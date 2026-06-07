<?php

namespace App\Models;

use App\Core\Model;
/**
 * Model Settings - Site settings management
 */
class Settings extends Model
{
    protected $table = 'settings';

    public function __construct($params = [])
    {
        parent::__construct($params);
    }

    /**
     * Recupera il valore di una impostazione
     */
    public function get($key, $default = null)
    {
        $stmt = $this->query("SELECT value FROM " . $this->table . " WHERE `key` = :key LIMIT 1", ['key' => $key]);
        $row = $stmt->fetch($this->db::FETCH_ASSOC);
        return $row ? $row['value'] : $default;
    }

    /**
     * Set the value of a setting
     *
     * This method uses 'INSERT OR REPLACE' for SQLite and 'ON DUPLICATE KEY UPDATE' for MySQL.
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param string|null $description Description
     * @param int $autoload Autoload flag
     * @return bool Success
     */
    public function set($key, $value, $description = null, $autoload = 1)
    {
        if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
            $sql = "INSERT OR REPLACE INTO " . $this->table . " (`key`, `value`, `description`, `autoload`) VALUES (?, ?, ?, ?)";
            $stmt = $this->query($sql, [$key, $value, $description, $autoload]);
            return $stmt->execute([$key, $value, $description, $autoload]);
        } else {
            $sql = "INSERT INTO " . $this->table . " (`key`, `value`, `description`, `autoload`) VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `description` = VALUES(`description`), `autoload` = VALUES(`autoload`)";
            $stmt = $this->query($sql, [$key, $value, $description, $autoload]);
            return $stmt->execute([$key, $value, $description, $autoload]);
        }
    }

    /**
     * Recupera tutte le impostazioni
     */
    public function all()
    {
        $stmt = $this->query("SELECT * FROM " . $this->table . " ORDER BY `key`");
        return $stmt->fetchAll($this->db::FETCH_ASSOC);
    }

    /**
     * Get allowed columns for ORDER BY clause
     *
     * @return array List of column names allowed in ORDER BY
     */
    protected function getAllowedOrderByColumns() {
        return ['id', 'key', 'autoload'];
    }
}
