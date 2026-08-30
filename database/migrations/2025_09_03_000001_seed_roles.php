<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Migration;

class SeedRoles extends Migration
{
    /**
     * @var \PDO
     */
    protected $db;

    public function __construct(\PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    public function up()
    {
        $roles = [
            ['admin',      'Administrator with full access',           4],
            ['editor',     'Editor with content management access',    3],
            ['author',     'Author with limited content creation access', 2],
            ['subscriber', 'Subscriber with read-only access',         1],
        ];

        $this->removeDuplicateRoles();
        $this->ensureUniqueNameIndex();

        // Check-then-insert rather than a dialect-specific upsert: SQLite spells
        // it INSERT OR IGNORE and MySQL INSERT IGNORE, and IGNORE only suppresses
        // a constraint violation, so it would silently duplicate rows anywhere
        // the unique index below could not be created.
        $exists = $this->db->prepare("SELECT COUNT(*) FROM roles WHERE name = ?");
        $insert = $this->db->prepare(
            "INSERT INTO roles (name, description, level) VALUES (?, ?, ?)"
        );

        if ($exists === false || $insert === false) {
            $this->failed('prepare the role statements');
        }

        foreach ($roles as [$name, $desc, $level]) {
            $exists->execute([$name]);

            if ((int) $exists->fetchColumn() === 0) {
                $insert->execute([$name, $desc, $level]);
            }
        }
    }

    /**
     * Drop rows left behind by an earlier double-seeding, keeping the lowest id
     * per name.
     *
     * Read first and delete by explicit id: MySQL refuses a subquery that reads
     * the table being deleted from (error 1093, and 1137 on a temporary table),
     * so the single-statement form worked on SQLite only.
     */
    private function removeDuplicateRoles(): void
    {
        $statement = $this->db->query("SELECT MIN(id) FROM roles GROUP BY name");

        if ($statement === false) {
            $this->failed('read the existing roles');
        }

        $keep = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($keep)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($keep), '?'));
        $delete = $this->db->prepare("DELETE FROM roles WHERE id NOT IN ({$placeholders})");

        if ($delete === false) {
            $this->failed('remove duplicate roles');
        }

        $delete->execute($keep);
    }

    /**
     * Report a statement that returned false instead of throwing.
     *
     * PDO only raises PDOException in exception mode. PHP 7.4 — still allowed
     * by composer.json — defaults to ERRMODE_SILENT, and InstallController
     * builds its connection without options, so there a failing statement
     * returns false and the next call on it would raise an Error.
     * MigrationRunner catches \Exception, not \Error, so that would escape as a
     * fatal instead of being reported as a failed migration.
     *
     * @throws \RuntimeException
     */
    private function failed(string $what): void
    {
        $info = $this->db->errorInfo();

        throw new \RuntimeException(
            "SeedRoles could not {$what}: " . ($info[2] ?? 'unknown database error')
        );
    }

    /**
     * Enforce one row per role name.
     *
     * SQLite accepts CREATE UNIQUE INDEX IF NOT EXISTS; MySQL has no such form,
     * so there the duplicate-index error is what tells us it is already in
     * place. The driver is read from the connection rather than the DB_DRIVER
     * constant, which is not necessarily the connection this migration was
     * handed.
     */
    private function ensureUniqueNameIndex(): void
    {
        $driver = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            if ($this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_roles_name ON roles(name)") === false) {
                $this->failed('create the unique index on roles(name)');
            }
            return;
        }

        // Both error modes have to be handled: exception mode throws, silent
        // mode returns false and leaves the code on the handle.
        try {
            if ($this->db->exec("CREATE UNIQUE INDEX idx_roles_name ON roles(name)") !== false) {
                return;
            }

            $code = $this->db->errorInfo()[1] ?? null;
        } catch (\PDOException $e) {
            $code = $e->errorInfo[1] ?? null;
        }

        // 1061: duplicate key name — the index is already there
        if ((int) $code !== 1061) {
            $this->failed('create the unique index on roles(name)');
        }
    }

    public function down()
    {
        $this->db->exec("DELETE FROM roles WHERE name IN ('admin','editor','author','subscriber')");
    }
}
