<?php

namespace App\Core;

use App\Core\Database\Database;
use App\Core\HookSystem;
/**
 * Base Model Class
 * All models will extend this class
 */
abstract class Model {
    protected $db;
    protected $table;
    protected $hookSystem;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->hookSystem = HookSystem::getInstance();
    }
    
    /**
     * Get all records from the table (basic implementation)
     * Child classes can override this method with their own parameters
     * 
     * @return array
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }
    
    /**
     * Get records with filtering, ordering, and pagination
     * This is the enhanced version that doesn't conflict with existing overrides
     *
     * @param array $conditions WHERE conditions
     * @param string $orderBy Order by field
     * @param string $order Order direction (ASC|DESC)
     * @param int $limit Limit number of records
     * @param int $offset Offset for pagination
     * @return array
     */
    public function findAll($conditions = [], $orderBy = 'id', $order = 'ASC', $limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "`{$field}` = :{$field}";
                $params[":{$field}"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }

        // Validate orderBy against whitelist
        $allowedColumns = $this->getAllowedOrderByColumns();
        if (!in_array($orderBy, $allowedColumns)) {
            $orderBy = 'id'; // Fallback to safe default
        }

        // Validate order direction
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $sql .= " ORDER BY `{$orderBy}` {$order}";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset > 0) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get allowed columns for ORDER BY clause
     * Child classes should override to define their specific allowed columns
     *
     * @return array List of column names allowed in ORDER BY
     */
    protected function getAllowedOrderByColumns() {
        // Default safe columns that exist in most tables
        return ['id', 'created_at', 'updated_at'];
    }
    
    /**
     * Get a record by ID
     * 
     * @param int $id The record ID
     * @return array|false The record or false if not found
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', $id, $this->db::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Insert a new record with validation
     * 
     * @param array $data The data to insert
     * @return int|false The last insert ID or false on failure
     * @throws \Exception On validation or insertion errors
     */
    public function insert($data) {
        if (empty($data)) {
            throw new \Exception('Cannot insert empty data');
        }
        // Fire before_save hook
        $this->fireModelHook('before_save', $data, null);
        $this->fireModelHook('before_insert', $data, null);
        
        // Allow plugins to modify data before insert
        $data = $this->hookSystem->applyFilters('model_insert_data', $data, $this->table);
        $data = $this->hookSystem->applyFilters($this->table . '_insert_data', $data);
        
        $keys = array_keys($data);
        // Remove ':' from field names for SQL field list
        $fields = '`' . implode('`, `', array_map(function($k){ return ltrim($k, ':'); }, $keys)) . '`';
        // Add ':' prefix to field names for placeholders
        $placeholders = ':' . implode(', :', array_map(function($k){ return ltrim($k, ':'); }, $keys));

        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            $paramName = ':' . ltrim($key, ':');
            $this->bindValueByType($stmt, $paramName, $value);
        }

        $result = $stmt->execute();
        if ($result) {
            $id = $this->db->lastInsertId();
            
            // Get the inserted record for hooks
            $insertedRecord = $this->getById($id);
            
            // Fire after_save hooks
            $this->fireModelHook('after_save', $insertedRecord, $id);
            $this->fireModelHook('after_insert', $insertedRecord, $id);
            
            return $id;
        }
        
        return false;
    }
    
    /**
     * Update a record
     * 
     * @param int $id The record ID
     * @param array $data The data to update
     * @return bool True on success, false on failure
     */
    public function update($id, $data) {
        // Get original record for hooks
        $originalRecord = $this->getById($id);
        
        // Fire before_save hooks
        $this->fireModelHook('before_save', $data, $id);
        $this->fireModelHook('before_update', $data, $id, $originalRecord);
        
        // Allow plugins to modify data before update
        $data = $this->hookSystem->applyFilters('model_update_data', $data, $this->table, $id);
        $data = $this->hookSystem->applyFilters($this->table . '_update_data', $data, $id, $originalRecord);
        
        $fields = [];
        foreach (array_keys($data) as $key) {
            $fields[] = "`$key` = :$key";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, $this->db::PARAM_INT);
        
        foreach ($data as $key => $value) {
            $this->bindValueByType($stmt, ':' . $key, $value);
        }
        
        $result = $stmt->execute();
        if ($result) {
            // Get updated record for hooks
            $updatedRecord = $this->getById($id);
            
            // Fire after_save hooks
            $this->fireModelHook('after_save', $updatedRecord, $id);
            $this->fireModelHook('after_update', $updatedRecord, $id, $originalRecord);
        }
        
        return $result;
    }
    
    /**
     * Delete a record
     * 
     * @param int $id The record ID
     * @return bool True on success, false on failure
     */
    public function delete($id) {
        // Get record for hooks before deletion
        $record = $this->getById($id);
        
        // Fire before_delete hooks
        $this->fireModelHook('before_delete', $record, $id);
        
        // Allow plugins to prevent deletion
        $allowDelete = $this->hookSystem->applyFilters('model_allow_delete', true, $this->table, $id, $record);
        $allowDelete = $this->hookSystem->applyFilters($this->table . '_allow_delete', $allowDelete, $id, $record);
        
        if (!$allowDelete) {
            return false;
        }
        
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', $id, $this->db::PARAM_INT);
        $result = $stmt->execute();
        
        if ($result) {
            // Fire after_delete hooks
            $this->fireModelHook('after_delete', $record, $id);
        }
        
        return $result;
    }
    
    /**
     * Execute a custom query with error handling
     * 
     * @param string $sql The SQL query
     * @param array $params The query parameters
     * @return \PDOStatement The statement object
     * @throws \Exception On query execution error
     */
    protected function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new \Exception('Failed to prepare SQL query: ' . implode(', ', $this->db->errorInfo()));
            }
            
            $result = $stmt->execute($params);
            if (!$result) {
                throw new \Exception('Failed to execute SQL query: ' . implode(', ', $stmt->errorInfo()));
            }
            
            return $stmt;
        } catch (\PDOException $e) {
            throw new \Exception('Database query error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get the database connection
     * 
     * @return Database The database instance
     */
    public function getDb() {
        return $this->db;
    }
    
    /**
     * Fire model-specific hooks
     * 
     * @param string $action The action (before_save, after_save, etc.)
     * @param mixed $data The data associated with the action
     * @param int|null $id The record ID (if applicable)
     * @param mixed $extraData Additional data for the hook
     */
    protected function fireModelHook($action, $data, $id = null, $extraData = null) {
        // Fire generic model hooks
        $this->hookSystem->doAction('model_' . $action, $data, $this->table, $id, $extraData);
        
        // Fire table-specific hooks
        $this->hookSystem->doAction($this->table . '_' . $action, $data, $id, $extraData);
        
        // Fire model class specific hooks if available
        $modelClass = get_called_class();
        $className = strtolower(basename(str_replace('\\', '/', $modelClass)));
        if ($className !== 'model') {
            $this->hookSystem->doAction($className . '_' . $action, $data, $id, $extraData);
        }
    }
    
    /**
     * Apply model-specific filters
     * 
     * @param string $filter The filter name
     * @param mixed $value The value to filter
     * @param mixed ...$args Additional arguments
     * @return mixed The filtered value
     */
    protected function applyModelFilter($filter, $value, ...$args) {
        // Apply generic model filter
        $value = $this->hookSystem->applyFilters('model_' . $filter, $value, $this->table, ...$args);
        
        // Apply table-specific filter
        $value = $this->hookSystem->applyFilters($this->table . '_' . $filter, $value, ...$args);
        
        // Apply model class specific filter if available
        $modelClass = get_called_class();
        $className = strtolower(basename(str_replace('\\', '/', $modelClass)));
        if ($className !== 'model') {
            $value = $this->hookSystem->applyFilters($className . '_' . $filter, $value, ...$args);
        }
        
        return $value;
    }
    
    /**
     * Get model metadata for hooks
     * 
     * @return array Model metadata
     */
    protected function getModelMetadata() {
        return [
            'table' => $this->table,
            'class' => get_called_class(),
            'database' => $this->db->getDatabaseName() ?? 'default'
        ];
    }
    
    /**
     * Bulk insert with hooks
     * 
     * @param array $records Array of records to insert
     * @return array Array of inserted IDs
     */
    public function bulkInsert($records) {
        $ids = [];
        
        // Fire bulk operation hook
        $this->hookSystem->doAction('before_bulk_insert', $records, $this->table);
        $this->hookSystem->doAction($this->table . '_before_bulk_insert', $records);
        
        foreach ($records as $record) {
            $id = $this->insert($record);
            if ($id) {
                $ids[] = $id;
            }
        }
        
        // Fire after bulk operation hook
        $this->hookSystem->doAction('after_bulk_insert', $ids, $this->table);
        $this->hookSystem->doAction($this->table . '_after_bulk_insert', $ids);
        
        return $ids;
    }
    
    /**
     * Bulk delete with hooks
     * 
     * @param array $ids Array of IDs to delete
     * @return int Number of deleted records
     */
    public function bulkDelete($ids) {
        $deletedCount = 0;
        
        // Get records for hooks
        $records = [];
        foreach ($ids as $id) {
            $record = $this->getById($id);
            if ($record) {
                $records[$id] = $record;
            }
        }
        
        // Fire bulk operation hook
        $this->hookSystem->doAction('before_bulk_delete', $records, $this->table);
        $this->hookSystem->doAction($this->table . '_before_bulk_delete', $records);
        
        foreach ($ids as $id) {
            if ($this->delete($id)) {
                $deletedCount++;
            }
        }
        
        // Fire after bulk operation hook
        $this->hookSystem->doAction('after_bulk_delete', $ids, $deletedCount, $this->table);
        $this->hookSystem->doAction($this->table . '_after_bulk_delete', $ids, $deletedCount);
        
        return $deletedCount;
    }
    
    /**
     * Bind value to prepared statement with appropriate type
     * 
     * @param \PDOStatement $stmt The prepared statement
     * @param string $param Parameter name
     * @param mixed $value Value to bind
     */
    private function bindValueByType(\PDOStatement $stmt, $param, $value) {
        if (is_int($value)) {
            $stmt->bindValue($param, $value, \PDO::PARAM_INT);
        } elseif (is_bool($value)) {
            $stmt->bindValue($param, $value, \PDO::PARAM_BOOL);
        } elseif (is_null($value)) {
            $stmt->bindValue($param, $value, \PDO::PARAM_NULL);
        } else {
            $stmt->bindValue($param, $value, \PDO::PARAM_STR);
        }
    }
    
    /**
     * Count records with optional conditions
     * 
     * @param array $conditions WHERE conditions
     * @return int Number of records
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "`{$field}` = :{$field}";
                $params[":{$field}"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $stmt = $this->query($sql, $params);
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Check if record exists
     * 
     * @param array $conditions WHERE conditions
     * @return bool True if record exists
     */
    public function exists($conditions) {
        return $this->count($conditions) > 0;
    }

    protected function generateSlug(string $title, ?int $excludeId = null): string {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));

        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = :slug";
        $params = [':slug' => $slug];

        if ($excludeId) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }

        $count = (int)$this->query($sql, $params)->fetchColumn();

        if ($count > 0) {
            $i = 1;
            do {
                $newSlug = $slug . '-' . $i;
                $checkSql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = :slug";
                $checkParams = [':slug' => $newSlug];
                if ($excludeId) {
                    $checkSql .= " AND id != :id";
                    $checkParams[':id'] = $excludeId;
                }
                $count = (int)$this->query($checkSql, $checkParams)->fetchColumn();
                $i++;
            } while ($count > 0);
            $slug = $newSlug;
        }

        return $slug;
    }
}
