<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/database/migrations/2025_09_03_000001_seed_roles.php';

/**
 * SeedRoles on MySQL Test
 *
 * The migration's statements have to be valid on MySQL, not only on SQLite.
 * A dialect mistake in a migration is invisible until someone migrates on the
 * other driver, which is exactly how it reached main.
 *
 * The migration runs against a TEMPORARY `roles` table: for the length of the
 * connection it shadows the real one, so the developer database is untouched.
 *
 * @package Tests\Integration
 */
class SeedRolesMysqlTest extends TestCase
{
    /** @var \PDO */
    private $pdo;

    protected function setUp(): void
    {
        parent::setUp();

        // Taken from the environment only — no connection details are spelled
        // out here. docker-compose exports these to the app container; without
        // them there is nothing to connect to and the test skips.
        $host = getenv('DB_HOST');
        $name = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');

        if ($host === false || $name === false || $user === false) {
            $this->markTestSkipped('DB_HOST/DB_NAME/DB_USER are not set: no MySQL to test against.');
        }

        try {
            $this->pdo = new \PDO(
                "mysql:host={$host};dbname={$name};charset=utf8mb4",
                $user,
                $pass,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 3]
            );
        } catch (\PDOException $e) {
            $this->markTestSkipped('MySQL not reachable: ' . $e->getMessage());
        }

        // Shadows the real table for this connection only
        $this->pdo->exec("CREATE TEMPORARY TABLE roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            description VARCHAR(255) DEFAULT NULL,
            level INT NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    private function roleNames(): array
    {
        $names = $this->pdo->query("SELECT name FROM roles ORDER BY name")->fetchAll(\PDO::FETCH_COLUMN);

        return $names;
    }

    public function testUpSeedsTheDefaultRolesOnMysql()
    {
        (new \SeedRoles($this->pdo))->up();

        $this->assertEquals(['admin', 'author', 'editor', 'subscriber'], $this->roleNames());
    }

    public function testUpIsIdempotentOnMysql()
    {
        $migration = new \SeedRoles($this->pdo);
        $migration->up();
        $migration->up();

        $this->assertCount(4, $this->roleNames(), 'Re-running the migration must not duplicate roles');
    }

    public function testUpRemovesPreExistingDuplicatesOnMysql()
    {
        // The state an earlier double-seeding left behind, which the migration
        // is meant to clean up before enforcing uniqueness.
        $this->pdo->exec("INSERT INTO roles (name, description, level) VALUES
            ('admin', 'first', 4), ('admin', 'duplicate', 4)");

        (new \SeedRoles($this->pdo))->up();

        $this->assertCount(4, $this->roleNames());
    }
}
