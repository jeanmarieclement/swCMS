<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Core\InstallationState;

/**
 * InstallationState on MySQL Test
 *
 * looksInstalled() lists tables with a driver-specific statement: SHOW TABLES
 * on MySQL, sqlite_master on SQLite. #22 showed how long a driver branch can
 * stay broken when only the other one is ever exercised, and here a false
 * negative would leave the installer open on a live site.
 *
 * A note on fixtures, because it is a trap: on MySQL, SHOW TABLES does **not**
 * list TEMPORARY tables, even though queries against them resolve to the
 * temporary copy. So a temporary `users` table cannot be used to fake a schema
 * for this check — listTables() would not see it. What a temporary `users`
 * table *can* do is shadow the row data, which is how the administrator half of
 * the check is isolated below. The schema half is exercised against the real
 * migrated database, so these tests require a migrated one and say so when they
 * skip. The pure logic (partial schema, no users, non-admin users) is covered
 * driver-independently in tests/Unit/Core/InstallationStateTest.php.
 *
 * @package Tests\Integration
 */
class InstallationStateMysqlTest extends TestCase
{
    /** @var \PDO */
    private $pdo;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->requireMigratedDatabase();
    }

    /**
     * These tests read the real schema, so an unmigrated database has nothing
     * to say about the MySQL branch and must skip loudly rather than pass.
     */
    private function requireMigratedDatabase(): void
    {
        $tables = $this->pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

        foreach (InstallationState::REQUIRED_TABLES as $required) {
            if (!in_array($required, $tables, true)) {
                $this->markTestSkipped(
                    "Table '{$required}' is missing: run database/migrate.php up before this suite."
                );
            }
        }
    }

    public function testShowTablesSeesTheMigratedSchema()
    {
        // The driver branch itself: if listTables() ever queried sqlite_master
        // unconditionally, MySQL would report nothing and every installed site
        // would look uninstalled — reopening the wizard on all of them.
        $this->assertEquals('mysql', $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));

        $reflection = new \ReflectionMethod(InstallationState::class, 'listTables');
        $reflection->setAccessible(true);
        $tables = $reflection->invoke(null, $this->pdo);

        foreach (InstallationState::REQUIRED_TABLES as $required) {
            $this->assertContains($required, $tables, "listTables() did not report '{$required}' on MySQL");
        }
    }

    public function testAMigratedDatabaseWithAnAdministratorIsInstalled()
    {
        $adminRoles = implode("','", InstallationState::ADMIN_ROLES);
        $admins = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM users WHERE role IN ('{$adminRoles}')")
            ->fetchColumn();

        if ($admins === 0) {
            $this->markTestSkipped('No administrator in this database: nothing to assert installed.');
        }

        $this->assertTrue(InstallationState::looksInstalled($this->pdo));
    }

    public function testTheAdministratorCheckIsWhatDecidesOnAFullSchema()
    {
        // A TEMPORARY users table shadows the row data while SHOW TABLES keeps
        // reporting the real migrated schema, which isolates the administrator
        // half of the check: same tables, no admin, must come back false.
        $this->pdo->exec("CREATE TEMPORARY TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            role VARCHAR(50) NOT NULL DEFAULT 'subscriber'
        ) ENGINE=InnoDB");

        $this->assertFalse(InstallationState::looksInstalled($this->pdo));

        $this->pdo->exec("INSERT INTO users (username, email, role) VALUES ('admin', 'a@b.co', 'admin')");

        $this->assertTrue(InstallationState::looksInstalled($this->pdo));
    }

    public function testSuperAdminAloneIsEnoughOnMysql()
    {
        $this->pdo->exec("CREATE TEMPORARY TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            role VARCHAR(50) NOT NULL DEFAULT 'subscriber'
        ) ENGINE=InnoDB");
        $this->pdo->exec("INSERT INTO users (username, email, role) VALUES ('root', 'r@b.co', 'super_admin')");

        $this->assertTrue(InstallationState::looksInstalled($this->pdo));
    }
}
