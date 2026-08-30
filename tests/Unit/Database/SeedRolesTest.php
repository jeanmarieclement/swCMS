<?php

namespace Tests\Unit\Database;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/database/migrations/2025_09_03_000001_seed_roles.php';

/**
 * SeedRoles Test
 *
 * Covers the SQLite path of the migration, so its behaviour is guarded in any
 * environment — the MySQL counterpart needs a server and skips without one.
 *
 * @package Tests\Unit\Database
 */
class SeedRolesTest extends TestCase
{
    private function connection(int $errorMode = \PDO::ERRMODE_EXCEPTION): \PDO
    {
        $pdo = new \PDO('sqlite::memory:', null, null, [\PDO::ATTR_ERRMODE => $errorMode]);

        return $pdo;
    }

    private function createRolesTable(\PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE roles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(50) NOT NULL,
            description VARCHAR(255) DEFAULT NULL,
            level INTEGER NOT NULL DEFAULT 0
        )");
    }

    private function roleNames(\PDO $pdo): array
    {
        return $pdo->query("SELECT name FROM roles ORDER BY name")->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function testUpSeedsTheDefaultRoles()
    {
        $pdo = $this->connection();
        $this->createRolesTable($pdo);

        (new \SeedRoles($pdo))->up();

        $this->assertEquals(['admin', 'author', 'editor', 'subscriber'], $this->roleNames($pdo));
    }

    public function testUpIsIdempotent()
    {
        $pdo = $this->connection();
        $this->createRolesTable($pdo);

        $migration = new \SeedRoles($pdo);
        $migration->up();
        $migration->up();

        $this->assertCount(4, $this->roleNames($pdo));
    }

    public function testUpRemovesPreExistingDuplicates()
    {
        $pdo = $this->connection();
        $this->createRolesTable($pdo);
        $pdo->exec("INSERT INTO roles (name, description, level) VALUES
            ('admin', 'first', 4), ('admin', 'duplicate', 4)");

        (new \SeedRoles($pdo))->up();

        $this->assertCount(4, $this->roleNames($pdo));
    }

    public function testUpCreatesTheUniqueNameIndex()
    {
        $pdo = $this->connection();
        $this->createRolesTable($pdo);

        (new \SeedRoles($pdo))->up();

        $this->expectException(\PDOException::class);
        $pdo->exec("INSERT INTO roles (name, description, level) VALUES ('admin', 'second admin', 4)");
    }

    public function testAFailedStatementRaisesAnExceptionEvenOnASilentConnection()
    {
        // PHP 7.4 — still allowed by composer.json — defaults PDO to
        // ERRMODE_SILENT, and InstallController builds its connection without
        // options. A failing statement must surface as an Exception that
        // MigrationRunner's catch(\Exception) can report, not as an Error
        // raised by calling a method on false.
        $pdo = $this->connection(\PDO::ERRMODE_SILENT);
        // roles deliberately not created

        $this->expectException(\Exception::class);

        (new \SeedRoles($pdo))->up();
    }
}
