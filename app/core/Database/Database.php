<?php

namespace App\Core\Database;

use PDO;
use PDOException;
use App\Helpers\LogHelper;

/**
 * Database configuration and connection handler
 */
class Database extends \PDO
{
    /**
     * @var self|null
     */
    private static $instance = null;


    /**
     * Costruttore privato: inizializza la connessione PDO
     */
    private function __construct()
    {
        try {
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];

            if (defined('DB_DRIVER') && \DB_DRIVER === 'sqlite') {
                $dsn = 'sqlite:' . \DB_SQLITE_PATH;
                parent::__construct($dsn, null, null, $options);
                // Enable foreign key constraints for SQLite
                $this->exec('PRAGMA foreign_keys = ON');
            } else {
                $dsn = "mysql:host=" . \DB_HOST . ";dbname=" . \DB_NAME . ";charset=" . \DB_CHARSET;
                parent::__construct($dsn, \DB_USER, \DB_PASS, $options);
            }
        } catch (\PDOException $e) {
            LogHelper::critical('Database Connection Error: ' . $e->getMessage());
            // Generic message: never expose DSN/credentials to callers
            throw new \RuntimeException('Database connection failed. Please check your configuration.');
        }
    }

    /**
     * Ottieni l'istanza singleton
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Impedisce la clonazione
     */
    private function __clone()
    {
    }

    /**
     * Impedisce l'unserialization
     */
    public function __wakeup()
    {
    }
}
