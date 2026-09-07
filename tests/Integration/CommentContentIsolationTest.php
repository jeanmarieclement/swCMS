<?php

namespace Tests\Integration;

use App\Models\Comments;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/database/migrations/2026_09_06_000001_separate_comment_content_references.php';

class CommentContentIsolationTest extends TestCase
{
    private $pdo;
    private $admin;
    private $databaseName;
    private $model;

    public static function databases(): array
    {
        return [['sqlite', false], ['sqlite', true], ['mysql', false], ['mysql', true]];
    }

    private function setupDatabase(string $driver, bool $legacyForeignKeys): void
    {
        if ($driver === 'sqlite') {
            $this->pdo = new \PDO('sqlite::memory:');
            $this->pdo->exec('PRAGMA foreign_keys = ON');
            $id = 'INTEGER PRIMARY KEY AUTOINCREMENT';
        } else {
            // Explicit opt-in: create/drop only a randomly named, isolated test database.
            $dsn = getenv('COMMENTS_MYSQL_ADMIN_DSN');
            if (!$dsn) {
                $this->markTestSkipped('Set COMMENTS_MYSQL_ADMIN_DSN to run isolated MySQL regression tests.');
            }
            $this->admin = new \PDO($dsn, getenv('COMMENTS_MYSQL_USER'), getenv('COMMENTS_MYSQL_PASSWORD'), [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
            $this->databaseName = 'swcms_comments_test_' . bin2hex(random_bytes(6));
            $this->admin->exec('CREATE DATABASE `' . $this->databaseName . '`');
            $this->pdo = $this->admin;
            $this->pdo->exec('USE `' . $this->databaseName . '`');
            $id = 'INT AUTO_INCREMENT PRIMARY KEY';
        }
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->pdo->exec("CREATE TABLE posts (id INT PRIMARY KEY, title VARCHAR(100), comments_enabled INT DEFAULT 1)");
        $this->pdo->exec("CREATE TABLE pages (id INT PRIMARY KEY, title VARCHAR(100), comments_enabled INT DEFAULT 1)");
        $this->pdo->exec("CREATE TABLE users (id INT PRIMARY KEY, username VARCHAR(50), display_name VARCHAR(50), email VARCHAR(100))");
        $fks = $legacyForeignKeys ? ', FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE SET NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL' : '';
        $this->pdo->exec("CREATE TABLE comments (
            id {$id}, post_id INT NOT NULL, author_name VARCHAR(50), author_email VARCHAR(100),
            author_url VARCHAR(100), author_ip VARCHAR(100), content TEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending', parent_id INT, user_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, extension_field VARCHAR(30) DEFAULT 'preserved'
            {$fks})");
        $this->pdo->exec('CREATE INDEX legacy_comment_index ON comments(author_name)');
        $this->pdo->exec("INSERT INTO posts (id, title) VALUES (5, 'Article 5'), (6, 'Article 6')");
        $this->pdo->exec("INSERT INTO pages (id, title) VALUES (5, 'Page 5'), (7, 'Page 7')");
        $this->model = new Comments($this->pdo);
    }

    protected function tearDown(): void
    {
        $this->model = null;
        if ($this->admin && $this->databaseName) {
            $this->admin->exec('DROP DATABASE `' . $this->databaseName . '`');
        }
        $this->pdo = $this->admin = null;
        parent::tearDown();
    }

    private function migrate(): void
    {
        (new \SeparateCommentContentReferences($this->pdo))->up();
    }

    private function comment(array $target, string $content = 'Comment', string $status = 'approved'): int
    {
        $id = $this->model->createComment($target + ['content' => $content, 'status' => $status]);
        $this->assertNotFalse($id);
        return (int) $id;
    }

    /** @dataProvider databases */
    public function testMigrationPreservesAmbiguousAndOrphanedData(string $driver, bool $fks): void
    {
        $this->setupDatabase($driver, $fks);
        $this->pdo->exec("INSERT INTO comments (id, post_id, content, status) VALUES
            (1, 5, 'ambiguous', 'approved'), (2, 6, 'article only', 'approved')");
        $this->pdo->exec("INSERT INTO comments (id, post_id, parent_id, content, status) VALUES
            (3, 5, 1, 'ambiguous reply', 'approved')");
        if (!$fks) {
            $this->pdo->exec("INSERT INTO comments (id, post_id, content, status) VALUES
                (4, 7, 'page only', 'approved'), (5, 99, 'orphaned', 'approved')");
        }
        $this->migrate();
        $ambiguous = $this->model->getById(1);
        $this->assertNull($ambiguous['post_id']);
        $this->assertNull($ambiguous['page_id']);
        $this->assertEquals(5, $ambiguous['legacy_post_id']);
        $this->assertSame('approved', $ambiguous['status']);
        $this->assertSame('preserved', $ambiguous['extension_field']);
        $this->assertEquals(6, $this->model->getById(2)['post_id']);
        $this->assertEquals(1, $this->model->getById(3)['parent_id']);
        if (!$fks) {
            $this->assertEquals(7, $this->model->getById(4)['page_id']);
            $this->assertEquals(99, $this->model->getById(5)['legacy_post_id']);
        }
        $this->assertSame([], $this->model->getApprovedForPost(5));
        $this->assertSame([], $this->model->getApprovedForPage(5));
        $this->assertCount($fks ? 3 : 5, $this->model->getAllHierarchical());
        $this->assertFalse($this->model->createReply(['parent_id' => 1, 'content' => 'ambiguous reply']));
        $this->model->updateStatus(1, 'spam');
        $this->model->updateStatus(1, 'approved');
        $this->assertSame('spam', $this->model->getById(1)['status']);
        // A rerun must not reinterpret unresolved data after one of the colliding targets disappears.
        $this->pdo->exec('DELETE FROM pages WHERE id = 5');
        $this->migrate();
        $this->assertEquals(5, $this->model->getById(1)['legacy_post_id']);
        $this->assertSame(0, $this->model->countApprovedForPost(5));
        $this->assertTrue($this->model->delete(1));
        $this->assertNull($this->model->getById(3)['parent_id']);
    }

    /** @dataProvider databases */
    public function testIdenticalIdsHaveSeparateListsCountsTreesAndModeration(string $driver, bool $fks): void
    {
        $this->setupDatabase($driver, $fks);
        $this->migrate();
        $post = $this->comment(['post_id' => 5], 'Article root');
        $page = $this->comment(['page_id' => 5], 'Page root');
        $postReply = $this->model->createReply(['parent_id' => $post, 'content' => 'Article reply', 'status' => 'approved']);
        $pageReply = $this->model->createReply(['parent_id' => $page, 'content' => 'Page reply', 'status' => 'approved']);
        $pending = $this->comment(['page_id' => 5], 'Awaiting moderation', 'pending');
        $this->assertSame(2, $this->model->countApprovedForPost(5));
        $this->assertSame(2, $this->model->countApprovedForPage(5));
        $this->assertEquals([$post, $postReply], array_column($this->model->getApprovedForPost(5), 'id'));
        $this->assertEquals([$page, $pageReply], array_column($this->model->getApprovedForPage(5), 'id'));
        $this->assertEquals([$pageReply], array_column($this->model->getApprovedForPage(5, 1, 1), 'id'));
        $postTree = $this->model->getApprovedHierarchicalForPost(5);
        $pageTree = $this->model->getApprovedHierarchicalForPage(5);
        $this->assertCount(1, $postTree);
        $this->assertCount(1, $pageTree);
        $this->assertEquals($postReply, $postTree[0]['replies'][0]['id']);
        $this->assertEquals($pageReply, $pageTree[0]['replies'][0]['id']);
        $this->assertEquals([$pageReply], array_column($this->model->getReplies($page), 'id'));
        $rows = array_column($this->model->getAll(), null, 'id');
        $this->assertSame('Article 5', $rows[$post]['post_title']);
        $this->assertSame('Page 5', $rows[$page]['post_title']);
        $this->assertSame('page', $rows[$page]['content_type']);
        $this->model->updateStatus($pending, 'approved');
        $this->model->updateStatus($postReply, 'spam');
        $this->assertSame(1, $this->model->countApprovedForPost(5));
        $this->assertSame(3, $this->model->countApprovedForPage(5));
        $this->assertTrue($this->model->delete($post));
        $this->assertNull($this->model->getById($postReply)['parent_id']);
        $this->assertSame(3, $this->model->countApprovedForPage(5));
    }

    /** @dataProvider databases */
    public function testCrossContentRepliesAndInvalidTargetsAreRejected(string $driver, bool $fks): void
    {
        $this->setupDatabase($driver, $fks);
        $this->migrate();
        $post = $this->comment(['post_id' => 5]);
        $page = $this->comment(['page_id' => 5]);
        foreach (
            [['post_id' => 5, 'parent_id' => $page], ['page_id' => 5, 'parent_id' => $post],
            ['post_id' => 6, 'parent_id' => $post]] as $target
        ) {
            $this->assertFalse($this->model->createReply($target + ['content' => 'wrong parent']));
            $this->assertFalse($this->model->createComment($target + ['content' => 'wrong parent']));
        }
        foreach (
            [[], ['post_id' => 5, 'page_id' => 5], ['post_id' => -1], ['page_id' => 99],
            ['post_id' => 5, 'legacy_post_id' => 5]] as $target
        ) {
            $this->assertFalse($this->model->createComment($target + ['content' => 'invalid']));
        }
        $this->assertFalse($this->model->createReply(['parent_id' => 999, 'content' => 'missing parent']));
        $this->model->updateStatus($post, 'pending');
        $this->assertFalse($this->model->createReply(['parent_id' => $post, 'content' => 'unapproved parent']));
    }

    /** @dataProvider databases */
    public function testTypedForeignKeysCascadeOnlyTheSelectedContent(string $driver, bool $fks): void
    {
        $this->setupDatabase($driver, $fks);
        $this->migrate();
        $this->comment(['post_id' => 5]);
        $page = $this->comment(['page_id' => 5]);
        $pageOnly = $this->comment(['page_id' => 7]);
        $this->pdo->exec('DELETE FROM posts WHERE id = 5');
        $this->assertSame(0, $this->model->countApprovedForPost(5));
        $this->assertEquals($page, $this->model->getById($page)['id']);
        $this->comment(['post_id' => 6]);
        $this->pdo->exec("INSERT INTO pages (id, title) VALUES (6, 'Page 6')");
        $this->comment(['page_id' => 6]);
        $this->pdo->exec('DELETE FROM pages WHERE id = 6');
        $this->assertSame(1, $this->model->countApprovedForPost(6));
        $this->assertSame(0, $this->model->countApprovedForPage(6));
        $this->assertEquals($pageOnly, $this->model->getById($pageOnly)['id']);
        if ($driver === 'sqlite') {
            $this->assertSame([], $this->pdo->query('PRAGMA foreign_key_check')->fetchAll());
        }
    }

    /** @dataProvider databases */
    public function testDatabaseRejectsInvalidAssociationsOnInsertAndUpdate(string $driver, bool $fks): void
    {
        $this->setupDatabase($driver, $fks);
        $this->migrate();
        $id = $this->comment(['post_id' => 5]);
        foreach (
            [
            "INSERT INTO comments (post_id, page_id, content) VALUES (5, 5, 'both')",
            "INSERT INTO comments (content) VALUES ('no target')",
            "INSERT INTO comments (page_id, content) VALUES (99, 'missing')",
            "UPDATE comments SET page_id = 5 WHERE id = {$id}",
            "UPDATE comments SET post_id = NULL WHERE id = {$id}",
            ] as $sql
        ) {
            try {
                $this->pdo->exec($sql);
                $this->fail('Invalid association was accepted: ' . $sql);
            } catch (\PDOException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }
    }

    /** @dataProvider databases */
    public function testAdminKeepsFilteredAndInvalidLegacyRepliesVisible(string $driver, bool $fks): void
    {
        $this->setupDatabase($driver, $fks);
        $this->migrate();
        $post = $this->comment(['post_id' => 5]);
        $page = $this->comment(['page_id' => 5]);
        $pending = $this->comment(['post_id' => 5, 'parent_id' => $post], 'pending', 'pending');
        $this->assertEquals([$pending], array_column($this->model->getAllHierarchical('pending'), 'id'));
        // A historical bad parent link must never join unrelated trees or hide a comment in admin.
        $this->pdo->exec("UPDATE comments SET parent_id = {$post} WHERE id = {$page}");
        $this->assertSame([], $this->model->getReplies($post));
        $roots = array_column($this->model->getAllHierarchical(), null, 'id');
        $this->assertArrayHasKey($page, $roots);
        $this->assertNull($roots[$page]['parent_author_name']);
        $this->pdo->exec("UPDATE comments SET parent_id = {$pending} WHERE id = {$post}");
        $this->assertCount(3, $this->model->getAllHierarchical());
    }
}
