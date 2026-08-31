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
 * negative would mean the installer stays open on a live site.
 *
 * Runs against TEMPORARY tables, which shadow the real ones for this
 * connection only, so the developer database is untouched.
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
    }

    private function shadowRequiredTables(): void
    {
        $this->pdo->exec("CREATE TEMPORARY TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            role VARCHAR(50) NOT NULL DEFAULT 'subscriber'
        ) ENGINE=InnoDB");
        $this->pdo->exec("CREATE TEMPORARY TABLE settings (id INT AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB");
        $this->pdo->exec("CREATE TEMPORARY TABLE migrations (id INT AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB");
    }

    public function testTheDeveloperDatabaseIsRecognisedAsInstalled()
    {
        // The real schema, as the migrations leave it, with the admin account
        // this environment has: the check must say "installed".
        $this->assertTrue(InstallationState::looksInstalled($this->pdo));
    }

    public function testSchemaWithoutAnAdministratorIsNotInstalled()
    {
        // TEMPORARY tables hide the real ones, so users is empty here
        $this->shadowRequiredTables();

        $this->assertFalse(InstallationState::looksInstalled($this->pdo));
    }

    public function testSchemaWithAnAdministratorIsInstalled()
    {
        $this->shadowRequiredTables();
        $this->pdo->exec("INSERT INTO users (username, email, role) VALUES ('admin', 'a@b.co', 'admin')");

        $this->assertTrue(InstallationState::looksInstalled($this->pdo));
    }

    public function testShowTablesIsActuallyUsedOnMysql()
    {
        // Guards the driver branch itself: if listTables() ever queried
        // sqlite_master unconditionally, MySQL would report nothing and every
        // installed site would look uninstalled.
        $this->assertEquals('mysql', $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));
        $this->assertTrue(InstallationState::looksInstalled($this->pdo));
    }
}
