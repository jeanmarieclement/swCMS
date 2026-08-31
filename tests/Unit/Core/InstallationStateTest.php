<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use App\Core\InstallationState;

/**
 * InstallationState Test
 *
 * The installer must not decide whether a site is installed from the presence
 * of data/.installed alone: that file is a dotfile and is routinely lost to an
 * FTP deploy, a backup restore, or a data/ directory that was never writable.
 * Losing it must not turn a live site back into an open installation wizard.
 *
 * @package Tests\Unit\Core
 */
class InstallationStateTest extends TestCase
{
    private function connection(int $errorMode = \PDO::ERRMODE_EXCEPTION): \PDO
    {
        return new \PDO('sqlite::memory:', null, null, [\PDO::ATTR_ERRMODE => $errorMode]);
    }

    private function createSchema(\PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            role VARCHAR(50) NOT NULL DEFAULT 'subscriber'
        )");
        $pdo->exec("CREATE TABLE settings (id INTEGER PRIMARY KEY AUTOINCREMENT, `key` VARCHAR(100))");
        $pdo->exec("CREATE TABLE migrations (id INTEGER PRIMARY KEY AUTOINCREMENT, migration VARCHAR(255))");
    }

    private function insertAdmin(\PDO $pdo): void
    {
        $pdo->exec("INSERT INTO users (username, email, role) VALUES ('admin', 'a@b.co', 'admin')");
    }

    public function testAnEmptyDatabaseIsNotInstalled()
    {
        $this->assertFalse(InstallationState::looksInstalled($this->connection()));
    }

    public function testSchemaWithAnAdminUserIsInstalled()
    {
        $pdo = $this->connection();
        $this->createSchema($pdo);
        $this->insertAdmin($pdo);

        $this->assertTrue(InstallationState::looksInstalled($pdo));
    }

    public function testSchemaWithNoUsersIsNotInstalled()
    {
        // Migrations ran but the wizard never created the account: the install
        // is genuinely unfinished and the wizard should still be reachable.
        $pdo = $this->connection();
        $this->createSchema($pdo);

        $this->assertFalse(InstallationState::looksInstalled($pdo));
    }

    public function testSchemaWhoseOnlyAdministratorIsASuperAdminIsInstalled()
    {
        // super_admin is a first-class administrative role in this application:
        // AuthMiddleware::requireAdmin() accepts ['admin', 'super_admin'],
        // RoleService::canAccessTemplate() short-circuits on both, and
        // RoleController offers it as "Full system access with all
        // administrative privileges". Recognising only 'admin' here would
        // report such a site as unfinished and expose the wizard on it.
        $pdo = $this->connection();
        $this->createSchema($pdo);
        $pdo->exec("INSERT INTO users (username, email, role) VALUES ('root', 'r@b.co', 'super_admin')");

        $this->assertTrue(InstallationState::looksInstalled($pdo));
    }

    public function testEveryAdminRoleTheAppRecognisesCountsAsInstalled()
    {
        foreach (InstallationState::ADMIN_ROLES as $role) {
            $pdo = $this->connection();
            $this->createSchema($pdo);
            $pdo->exec("INSERT INTO users (username, email, role) VALUES ('u', 'u@b.co', '{$role}')");

            $this->assertTrue(
                InstallationState::looksInstalled($pdo),
                "Role '{$role}' is listed as administrative but does not mark the site installed"
            );
        }
    }

    public function testSchemaWithOnlyNonAdminUsersIsNotInstalled()
    {
        $pdo = $this->connection();
        $this->createSchema($pdo);
        $pdo->exec("INSERT INTO users (username, email, role) VALUES ('bob', 'b@b.co', 'subscriber')");

        $this->assertFalse(InstallationState::looksInstalled($pdo));
    }

    public function testAPartialSchemaIsNotInstalled()
    {
        $pdo = $this->connection();
        $pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, role VARCHAR(50))");

        $this->assertFalse(InstallationState::looksInstalled($pdo));
    }

    public function testASilentConnectionDoesNotFatal()
    {
        // PDO returns false instead of throwing in ERRMODE_SILENT, and the
        // installer builds its connections without options.
        $pdo = $this->connection(\PDO::ERRMODE_SILENT);

        $this->assertFalse(InstallationState::looksInstalled($pdo));
    }

    public function testDatabaseConfigIsReadFromAnEnvFile()
    {
        $env = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($env, "DB_DRIVER=mysql\nDB_HOST=dbhost\nDB_NAME=sitedb\nDB_USER=siteuser\nDB_PASS=x\n");

        $config = InstallationState::databaseConfigFromEnvFile($env);

        unlink($env);

        $this->assertEquals('mysql', $config['driver']);
        $this->assertEquals('dbhost', $config['host']);
        $this->assertEquals('sitedb', $config['name']);
        $this->assertEquals('siteuser', $config['user']);
    }

    public function testDatabaseConfigIsEmptyWhenTheEnvFileIsAbsent()
    {
        $this->assertSame([], InstallationState::databaseConfigFromEnvFile('/nonexistent/.env'));
    }

    public function testDatabaseConfigIsEmptyWhenTheEnvFileHasNoDatabaseSettings()
    {
        $env = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($env, "SITE_NAME=example\n");

        $config = InstallationState::databaseConfigFromEnvFile($env);

        unlink($env);

        $this->assertSame([], $config);
    }

    public function testAnUnreachableDatabaseIsNotReportedAsInstalled()
    {
        // A broken or half-finished install must leave the wizard reachable,
        // otherwise a failed connection would lock the owner out of it.
        $env = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($env, "DB_DRIVER=mysql\nDB_HOST=203.0.113.1\nDB_NAME=x\nDB_USER=x\nDB_PASS=x\n");

        $installed = InstallationState::environmentLooksInstalled($env);

        unlink($env);

        $this->assertFalse($installed);
    }

    public function testASqliteEnvPointingAtAnInstalledDatabaseIsReportedAsInstalled()
    {
        $dbFile = tempnam(sys_get_temp_dir(), 'sqlite');
        $pdo = new \PDO('sqlite:' . $dbFile);
        $this->createSchema($pdo);
        $this->insertAdmin($pdo);
        unset($pdo);

        $env = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($env, "DB_DRIVER=sqlite\nDB_SQLITE_PATH={$dbFile}\n");

        $installed = InstallationState::environmentLooksInstalled($env);

        unlink($env);
        unlink($dbFile);

        $this->assertTrue($installed);
    }

    public function testTheRealSchemaIsRecognised()
    {
        // Guards against the required-table list drifting away from what the
        // migrations actually create.
        $pdo = $this->connection();
        $this->createSchema($pdo);
        $this->insertAdmin($pdo);

        foreach (InstallationState::REQUIRED_TABLES as $table) {
            $this->assertNotFalse(
                $pdo->query("SELECT 1 FROM `{$table}` LIMIT 1"),
                "Required table {$table} is not part of the test schema"
            );
        }
    }
}
