<?php

namespace App\Core;

/**
 * InstallationState
 *
 * Answers "is this site already installed?" from the state of the system
 * itself, rather than from the presence of data/.installed alone.
 *
 * That flag is a dotfile inside a directory that is routinely lost: an FTP
 * client that skips hidden files, a backup restored without dotfiles, a data/
 * that was never writable so the flag was never written. Losing it must not
 * turn a live, populated site back into an open installation wizard, because
 * the wizard can repoint the database and create an administrator.
 *
 * @package App\Core
 */
class InstallationState
{
    /**
     * Tables the migrations create and that a finished install always has
     *
     * @var string[]
     */
    public const REQUIRED_TABLES = ['users', 'settings', 'migrations'];

    /**
     * Does the flag file exist?
     *
     * @param string $rootPath Application root
     * @return bool
     */
    public static function flagExists($rootPath): bool
    {
        return file_exists(rtrim($rootPath, '/') . '/data/.installed');
    }

    /**
     * Does this connection hold a usable installation?
     *
     * True only when every required table is present and at least one
     * administrator exists — a database where the migrations ran but the wizard
     * never created the account is genuinely unfinished, and the wizard has to
     * stay reachable for it.
     *
     * @param \PDO $pdo
     * @return bool
     */
    public static function looksInstalled(\PDO $pdo): bool
    {
        $tables = self::listTables($pdo);

        foreach (self::REQUIRED_TABLES as $required) {
            if (!in_array($required, $tables, true)) {
                return false;
            }
        }

        return self::hasAdministrator($pdo);
    }

    /**
     * Read the database settings out of a .env file
     *
     * The installer runs before the application config is bootstrapped, so the
     * DB_* constants do not exist yet and the file has to be read directly.
     *
     * @param string $envPath Path to the .env file
     * @return array Empty when the file is unreadable or carries no DB settings
     */
    public static function databaseConfigFromEnvFile($envPath): array
    {
        if (!is_file($envPath) || !is_readable($envPath)) {
            return [];
        }

        $values = @parse_ini_file($envPath);

        if ($values === false || empty($values['DB_NAME']) && empty($values['DB_SQLITE_PATH'])) {
            return [];
        }

        return [
            'driver' => $values['DB_DRIVER'] ?? 'mysql',
            'host' => $values['DB_HOST'] ?? 'localhost',
            'port' => $values['DB_PORT'] ?? '3306',
            'name' => $values['DB_NAME'] ?? '',
            'user' => $values['DB_USER'] ?? '',
            'pass' => $values['DB_PASS'] ?? '',
            'sqlite_path' => $values['DB_SQLITE_PATH'] ?? '',
        ];
    }

    /**
     * Is the database described by this .env file already installed?
     *
     * A connection that cannot be made returns false on purpose: a broken or
     * half-finished install has to leave the wizard reachable, or its owner
     * would be locked out of the only tool that can repair it.
     *
     * @param string $envPath Path to the .env file
     * @return bool
     */
    public static function environmentLooksInstalled($envPath): bool
    {
        $config = self::databaseConfigFromEnvFile($envPath);

        if (empty($config)) {
            return false;
        }

        try {
            if ($config['driver'] === 'sqlite') {
                if ($config['sqlite_path'] === '' || !is_file($config['sqlite_path'])) {
                    return false;
                }

                $pdo = new \PDO('sqlite:' . $config['sqlite_path']);
            } else {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    $config['host'],
                    $config['port'],
                    $config['name']
                );
                // Short timeout: this runs on a public request, and an
                // unreachable host must not hang the page.
                $pdo = new \PDO($dsn, $config['user'], $config['pass'], [
                    \PDO::ATTR_TIMEOUT => 3,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ]);
            }
        } catch (\PDOException $e) {
            return false;
        }

        return self::looksInstalled($pdo);
    }

    /**
     * List the table names in the connected database
     *
     * Driver-aware, and read from the connection rather than a DB_DRIVER
     * constant: during installation that constant is not defined at all.
     *
     * @param \PDO $pdo
     * @return string[]
     */
    private static function listTables(\PDO $pdo): array
    {
        try {
            $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

            $sql = $driver === 'sqlite'
                ? "SELECT name FROM sqlite_master WHERE type = 'table'"
                : "SHOW TABLES";

            $statement = $pdo->query($sql);

            // PDO returns false instead of throwing in ERRMODE_SILENT, which is
            // the default the installer's own connections are built with.
            if ($statement === false) {
                return [];
            }

            return array_map('strval', $statement->fetchAll(\PDO::FETCH_COLUMN));
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Is there at least one administrator account?
     *
     * @param \PDO $pdo
     * @return bool
     */
    private static function hasAdministrator(\PDO $pdo): bool
    {
        try {
            $statement = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");

            if ($statement === false) {
                return false;
            }

            return (int) $statement->fetchColumn() > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }
}
