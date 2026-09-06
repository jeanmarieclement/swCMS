<?php

require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Migration;

/**
 * Keep exactly one of post_id, page_id or the unresolved legacy_post_id.
 * Run with the application offline: MySQL DDL cannot be transactional.
 */
class SeparateCommentContentReferences extends Migration
{
    protected $db;

    public function up()
    {
        if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->upSqlite();
        } else {
            $this->upMysql();
        }
    }

    private function targetCheck(): string
    {
        return '(CASE WHEN post_id IS NULL THEN 0 ELSE 1 END'
            . ' + CASE WHEN page_id IS NULL THEN 0 ELSE 1 END'
            . ' + CASE WHEN legacy_post_id IS NULL THEN 0 ELSE 1 END) = 1';
    }

    private function upSqlite(): void
    {
        $columns = $this->db->query('PRAGMA table_info(comments)')->fetchAll(PDO::FETCH_ASSOC);
        if (in_array('page_id', array_column($columns, 'name'), true)) {
            return;
        }
        if ($this->db->inTransaction()) {
            throw new RuntimeException('Comment migration must run outside a transaction.');
        }
        $schema = $this->db->query("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'comments'")
            ->fetchColumn();
        $objects = $this->db->query("SELECT sql FROM sqlite_master
            WHERE tbl_name = 'comments' AND type IN ('index', 'trigger') AND sql IS NOT NULL")
            ->fetchAll(PDO::FETCH_COLUMN);
        $foreignKeys = $this->db->query('PRAGMA foreign_key_list(comments)')->fetchAll(PDO::FETCH_ASSOC);
        // Preserve custom columns, indexes, triggers and existing user/parent constraints.
        $schema = preg_replace(
            '/^(CREATE TABLE\s+)(?:IF NOT EXISTS\s+)?["`\[]?comments["`\]]?/i',
            '$1comments_typed',
            $schema,
            1
        );
        $schema = preg_replace('/(["`\[]?post_id["`\]]?\s+\w+(?:\(\d+\))?)\s+NOT NULL/i', '$1', $schema, 1);
        $schema = substr_replace(
            $schema,
            'page_id INTEGER DEFAULT NULL, legacy_post_id INTEGER DEFAULT NULL, ',
            strpos($schema, '(') + 1,
            0
        );
        $extra = ', CONSTRAINT chk_comments_target CHECK (' . $this->targetCheck() . ')'
            . ', FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE';
        if (!in_array('post_id', array_column($foreignKeys, 'from'), true)) {
            $extra .= ', FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE';
        }
        $end = strrpos($schema, ')');
        $schema = substr_replace($schema, $extra, $end, 0);
        $foreignKeysEnabled = (int) $this->db->query('PRAGMA foreign_keys')->fetchColumn();
        $this->db->exec('PRAGMA foreign_keys = OFF');
        $this->db->beginTransaction();
        try {
            $sequence = $this->db->query("SELECT seq FROM sqlite_sequence WHERE name = 'comments'")->fetchColumn();
            $this->db->exec($schema);
            $names = array_map(fn($c) => '"' . str_replace('"', '""', $c['name']) . '"', $columns);
            $select = array_map(function ($c) {
                if ($c['name'] === 'post_id') {
                    return 'CASE WHEN p.id IS NOT NULL AND pg.id IS NULL THEN c.post_id ELSE NULL END';
                }
                return 'c."' . str_replace('"', '""', $c['name']) . '"';
            }, $columns);
            $this->db->exec('INSERT INTO comments_typed (' . implode(', ', $names) . ', page_id, legacy_post_id)'
                . ' SELECT ' . implode(', ', $select)
                . ', CASE WHEN p.id IS NULL AND pg.id IS NOT NULL THEN c.post_id ELSE NULL END'
                . ', CASE WHEN (p.id IS NULL AND pg.id IS NULL) OR (p.id IS NOT NULL AND pg.id IS NOT NULL)'
                . ' THEN c.post_id ELSE NULL END'
                . ' FROM comments c LEFT JOIN posts p ON p.id = c.post_id LEFT JOIN pages pg ON pg.id = c.post_id');
            $this->db->exec('DROP TABLE comments');
            $this->db->exec('ALTER TABLE comments_typed RENAME TO comments');
            foreach ($objects as $sql) {
                $this->db->exec($sql);
            }
            $this->db->exec('CREATE INDEX idx_comments_post_status ON comments(post_id, status, parent_id)');
            $this->db->exec('CREATE INDEX idx_comments_page_status ON comments(page_id, status, parent_id)');
            if ($sequence !== false) {
                $stmt = $this->db->prepare("UPDATE sqlite_sequence SET seq = MAX(seq, :seq) WHERE name = 'comments'");
                $stmt->execute(['seq' => (int) $sequence]);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        } finally {
            $this->db->exec('PRAGMA foreign_keys = ' . $foreignKeysEnabled);
        }
    }

    private function upMysql(): void
    {
        $triggers = $this->db->query("SELECT TRIGGER_NAME FROM information_schema.TRIGGERS
            WHERE TRIGGER_SCHEMA = DATABASE() AND EVENT_OBJECT_TABLE = 'comments'")->fetchAll(PDO::FETCH_COLUMN);
        if (
            in_array('comments_target_insert', $triggers, true)
            && in_array('comments_target_update', $triggers, true)
        ) {
            return;
        }
        $columns = $this->db->query('SHOW COLUMNS FROM comments')->fetchAll(PDO::FETCH_ASSOC);
        if (!in_array('page_id', array_column($columns, 'Field'), true)) {
            // Preserve the exact ID type (including UNSIGNED) used by this installation.
            $postType = $this->db->query("SHOW COLUMNS FROM posts LIKE 'id'")->fetch(PDO::FETCH_ASSOC)['Type'];
            $pageType = $this->db->query("SHOW COLUMNS FROM pages LIKE 'id'")->fetch(PDO::FETCH_ASSOC)['Type'];
            $this->db->exec("ALTER TABLE comments MODIFY post_id {$postType} NULL,
                ADD page_id {$pageType} NULL, ADD legacy_post_id {$postType} NULL");
        }
        // Multi-table UPDATE expressions read the original source ID; target assignment order is irrelevant.
        $this->db->beginTransaction();
        try {
            $this->db->exec("UPDATE comments c
                LEFT JOIN posts p ON p.id = c.post_id
                LEFT JOIN pages pg ON pg.id = c.post_id
                SET c.legacy_post_id = CASE
                        WHEN (p.id IS NULL AND pg.id IS NULL) OR (p.id IS NOT NULL AND pg.id IS NOT NULL)
                        THEN c.post_id ELSE NULL END,
                    c.page_id = CASE WHEN p.id IS NULL AND pg.id IS NOT NULL THEN pg.id ELSE NULL END,
                    c.post_id = CASE WHEN p.id IS NOT NULL AND pg.id IS NULL THEN p.id ELSE NULL END
                WHERE c.page_id IS NULL AND c.legacy_post_id IS NULL");
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
        $foreignColumns = $this->db->query("SELECT COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'comments' AND REFERENCED_TABLE_NAME IS NOT NULL")
            ->fetchAll(PDO::FETCH_COLUMN);
        $alter = [
            'ADD INDEX idx_comments_post_status (post_id, status, parent_id)',
            'ADD INDEX idx_comments_page_status (page_id, status, parent_id)',
        ];
        if (!in_array('post_id', $foreignColumns, true)) {
            $alter[] = 'ADD CONSTRAINT fk_comments_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE';
        }
        if (!in_array('page_id', $foreignColumns, true)) {
            $alter[] = 'ADD CONSTRAINT fk_comments_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE';
        }
        $indexes = $this->db->query('SHOW INDEX FROM comments')->fetchAll(PDO::FETCH_ASSOC);
        $indexNames = array_column($indexes, 'Key_name');
        $alter = array_filter($alter, function ($sql) use ($indexNames) {
            return !preg_match('/ADD INDEX (\w+)/', $sql, $match) || !in_array($match[1], $indexNames, true);
        });
        if ($alter) {
            $this->db->exec('ALTER TABLE comments ' . implode(', ', $alter));
        }
        // MySQL forbids CHECKs on columns participating in FK cascade actions.
        // Equivalent BEFORE triggers enforce exclusivity without sacrificing typed cascades.
        // https://dev.mysql.com/doc/refman/8.0/en/create-table-check-constraints.html
        $check = preg_replace('/\b(post_id|page_id|legacy_post_id)\b/', 'NEW.$1', $this->targetCheck());
        foreach (['insert', 'update'] as $event) {
            if (!in_array('comments_target_' . $event, $triggers, true)) {
                $this->db->exec('CREATE TRIGGER comments_target_' . $event . ' BEFORE ' . strtoupper($event)
                    . ' ON comments FOR EACH ROW BEGIN IF NOT (' . $check . ") THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A comment must have exactly one content reference';
                      END IF; END");
            }
        }
    }

    public function down()
    {
        // Flattening page/post references reintroduces collisions and can lose data through the old FK.
        throw new RuntimeException('This migration cannot be reversed safely. Restore the pre-migration backup.');
    }
}
