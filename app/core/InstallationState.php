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
     * Roles this application treats as administrative
     *
     * Must stay in step with what the rest of the application considers an
     * administrator, otherwise a site whose only privileged account is not in
     * this list would be reported as unfinished and get the wizard back:
     * `AuthMiddleware::requireAdmin()` accepts both, `RoleService` short-circuits
     * on both, and `RoleController` describes super_admin as full system access.
     *
     * @var string[]
     */
    public const ADMIN_ROLES = ['admin', 'super_admin'];

    /** No evidence of a previous installation: the wizard may run */
    public const STATE_NOT_INSTALLED = 'not_installed';

    /** A working installation was found: the wizard must not run */
    public const STATE_INSTALLED = 'installed';

    /**
     * A previous installation is configured but could not be inspected — the
     * database it names is unreachable. The wizard must not run on a guess:
     * a transient outage would otherwise reopen it on a live site.
     */
    public const STATE_UNVERIFIABLE = 'unverifiable';

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

        // INI_SCANNER_RAW, because a .env is not really an INI file. Under the
        // default scanner a password containing & | ( ) makes the whole parse
        // fail, and the bare words yes/no/on/off are converted to '1'/'' — both
        // of which would report an installed site as unfinished.
        $values = @parse_ini_file($envPath, false, INI_SCANNER_RAW);

        if ($values === false || empty($values['DB_NAME']) && empty($values['DB_SQLITE_PATH'])) {
            return [];
        }

        $sqlitePath = $values['DB_SQLITE_PATH'] ?? '';

        return [
            'driver' => strtolower($values['DB_DRIVER'] ?? 'mysql'),
            'host' => $values['DB_HOST'] ?? 'localhost',
            'port' => $values['DB_PORT'] ?? '3306',
            'name' => $values['DB_NAME'] ?? '',
            'user' => $values['DB_USER'] ?? '',
            'pass' => $values['DB_PASS'] ?? '',
            'sqlite_path' => self::resolvePath($sqlitePath, dirname($envPath)),
        ];
    }

    /**
     * Resolve a possibly relative path against the project root
     *
     * .env.example suggests `DB_SQLITE_PATH=./data/database.sqlite`, and the web
     * entry point runs with the working directory set to public/, so a relative
     * path left as-is would never be found.
     *
     * @param string $path
     * @param string $basePath Directory the .env lives in
     * @return string
     */
    private static function resolvePath($path, $basePath): string
    {
        if ($path === '') {
            return '';
        }

        // Absolute POSIX path, or a Windows drive letter
        if ($path[0] === '/' || preg_match('#^[A-Za-z]:[\\\\/]#', $path) === 1) {
            return $path;
        }

        return rtrim($basePath, '/') . '/' . ltrim($path, './');
    }

    /**
     * What does the configured environment say about installation?
     *
     * @param string $envPath Path to the .env file
     * @return string One of the STATE_* constants
     */
    public static function inspectEnvironment($envPath): string
    {
        $config = self::databaseConfigFromEnvFile($envPath);

        if (empty($config)) {
            return self::STATE_NOT_INSTALLED;
        }

        $pdo = self::connect($config);

        if ($pdo === null) {
            // Something was configured here before; we simply cannot check it
            // right now. Saying "not installed" would hand the wizard to
            // whoever asks during a database outage.
            return self::STATE_UNVERIFIABLE;
        }

        return self::looksInstalled($pdo) ? self::STATE_INSTALLED : self::STATE_NOT_INSTALLED;
    }

    /**
     * Is the database described by this .env file already installed?
     *
     * @param string $envPath Path to the .env file
     * @return bool True only for a confirmed installation
     */
    public static function environmentLooksInstalled($envPath): bool
    {
        return self::inspectEnvironment($envPath) === self::STATE_INSTALLED;
    }

    /**
     * Open a connection for a parsed configuration
     *
     * @param array $config
     * @return \PDO|null Null when the database cannot be reached
     */
    private static function connect(array $config)
    {
        try {
            if ($config['driver'] === 'sqlite') {
                if ($config['sqlite_path'] === '' || !is_file($config['sqlite_path'])) {
                    return null;
                }

                return new \PDO('sqlite:' . $config['sqlite_path']);
            }

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['port'] !== '' ? $config['port'] : '3306',
                $config['name']
            );

            // Short timeout: this runs on a public request, and an unreachable
            // host must not hang the page.
            return new \PDO($dsn, $config['user'], $config['pass'], [
                \PDO::ATTR_TIMEOUT => 3,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (\PDOException $e) {
            return null;
        }
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
        $tables = array_map('strtolower', self::listTables($pdo));

        foreach (self::REQUIRED_TABLES as $required) {
            if (!in_array(strtolower($required), $tables, true)) {
                return false;
            }
        }

        return self::hasAdministrator($pdo);
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
        $placeholders = implode(',', array_fill(0, count(self::ADMIN_ROLES), '?'));

        try {
            $statement = $pdo->prepare(
                "SELECT COUNT(*) FROM users WHERE role IN ({$placeholders})"
            );

            if ($statement === false || $statement->execute(self::ADMIN_ROLES) === false) {
                return false;
            }

            return (int) $statement->fetchColumn() > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }
}
