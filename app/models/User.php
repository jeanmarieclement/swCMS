<?php

namespace App\Models;

use App\Core\Model;
use App\Helpers\LogHelper;

/**
 * User Model
 * Handles user authentication and management
 */
class User extends Model
{
    /**
     * Pre-computed dummy hash for timing attack prevention
     * This is computed once and reused to ensure consistent timing
     * @var string|null
     */
    private static $dummyHash = null;

    /**
     * Constructor - get database connection
     */
    public function __construct()
    {
        parent::__construct();
        $this->table = 'users';
    }

    /**
     * Get user by ID
     *
     * @param int $id User ID
     * @return array|false User data or false if not found
     */
    public function getUserById($id)
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE id = :id AND status = 'active'", [':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get user by email
     *
     * @param string $email User email
     * @return array|false User data or false if not found
     */
    public function getUserByEmail($email)
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE email = :email", [':email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Get all users
     *
     * @param string $orderBy Field to order by
     * @param string $order Order direction (ASC or DESC)
     * @return array List of users
     */
    public function getAllUsers($orderBy = 'id', $order = 'ASC')
    {
        // Validate order by field to prevent SQL injection
        $validFields = ['id', 'username', 'email', 'role', 'created_at', 'updated_at'];
        if (!in_array($orderBy, $validFields)) {
            $orderBy = 'id';
        }

        // Validate order direction
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $stmt = $this->query("
            SELECT id, username, email, display_name, role, status, created_at, updated_at, last_login
            FROM {$this->table}
            ORDER BY {$orderBy} {$order}
        ");

        return $stmt->fetchAll();
    }

    /**
     * Authenticate user with security enhancements
     *
     * @param string $email User email
     * @param string $password User password (plain text)
     * @return array|false User data or false if authentication failed
     */
    public function authenticate($email, $password)
    {
        // Rate limiting check (basic implementation)
        if ($this->isRateLimited($email)) {
            // Perform dummy password verification to prevent timing attacks
            password_verify($password, $this->getDummyHash());
            LogHelper::warning("Authentication rate limited for email: $email");
            return false;
        }

        // Validate input
        if (empty($email) || empty($password)) {
            // Perform dummy password verification to prevent timing attacks
            password_verify($password ?: 'dummy', $this->getDummyHash());
            LogHelper::warning("Authentication failed: Empty credentials provided");
            return false;
        }

        if (strlen($password) > 255) {
            // Perform dummy password verification to prevent timing attacks
            password_verify(substr($password, 0, 72), $this->getDummyHash());
            LogHelper::warning("Authentication failed: Password too long for email: $email");
            return false;
        }

        $user = $this->getUserByEmail($email);

        // Log authentication attempt (without sensitive data)
        LogHelper::debug("Authentication attempt for email: $email");

        // Always perform password verification to prevent timing attacks
        // Use pre-computed dummy hash for consistent timing across all authentication attempts
        $actualPasswordHash = $user['password'] ?? $this->getDummyHash();
        $passwordValid = password_verify($password, $actualPasswordHash);

        if (!$user || $user['status'] !== 'active' || !$passwordValid) {
            $this->recordFailedAttempt($email);
            LogHelper::warning("Authentication failed for email: $email");
            return false;
        }

        // Authentication successful
        $this->clearFailedAttempts($email);
        LogHelper::info("Authentication successful for email: $email with role: {$user['role']}");

        // Update last login time
        $this->updateLastLogin($user['id']);

        // Remove password from user data before returning
        unset($user['password']);
        return $user;
    }

    /**
     * Get or compute the dummy hash used for timing attack prevention
     * This method caches the hash to ensure consistent performance
     *
     * @return string Pre-computed bcrypt hash
     */
    private function getDummyHash()
    {
        if (self::$dummyHash === null) {
            // Pre-compute dummy hash once and cache it
            // Using a constant value ensures the hash is always the same
            self::$dummyHash = password_hash('dummy_password_constant_value', PASSWORD_BCRYPT);
        }
        return self::$dummyHash;
    }

    /**
     * Create new user
     *
     * @param array $userData User data
     * @return int|false New user ID or false if failed
     * @throws \Exception if password does not meet security requirements
     */
    public function createUser($userData)
    {
        // Validate password strength
        $validationResult = $this->validatePasswordStrength($userData['password']);
        if (!$validationResult['valid']) {
            throw new \Exception('Password does not meet security requirements: ' . implode(', ', $validationResult['errors']));
        }

        // Hash the password
        $passwordHash = password_hash($userData['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $this->insert([
            ':username' => $userData['username'],
            ':email' => $userData['email'],
            ':password' => $passwordHash,
            ':display_name' => $userData['display_name'] ?? $userData['username'],
            ':role' => $userData['role'] ?? 'subscriber',
            ':status' => 'active',
            ':created_at' => date('Y-m-d H:i:s')
        ]);

        return $stmt;
    }

    /**
     * Update user
     *
     * @param int $id User ID
     * @param array $userData User data to update
     * @return bool Success or failure
     * @throws \Exception if password does not meet security requirements
     */
    public function updateUser($id, $userData)
    {
        $params = [];

        foreach ($userData as $key => $value) {
            // Special handling for password
            if ($key === 'password' && !empty($value)) {
                // Validate password strength before hashing
                $validationResult = $this->validatePasswordStrength($value);
                if (!$validationResult['valid']) {
                    throw new \Exception('Password does not meet security requirements: ' . implode(', ', $validationResult['errors']));
                }
                $value = password_hash($value, PASSWORD_BCRYPT, ['cost' => 12]);
            } elseif ($key === 'id') {
                continue;
            }
            $params[$key] = $value;
        }


        return $this->update($id, $params);
    }

    /**
     * Delete a user
     *
     * @param int $id User ID
     * @return bool Success or failure
     */
    public function deleteUser($id)
    {
        // Soft delete - set status to 'deleted'
        return $this->delete($id);
    }

    /**
     * Count total users
     *
     * @param string $status User status (active, deleted, all)
     * @return int Number of users
     */
    public function countUsers($status = 'active')
    {
        $query = "SELECT COUNT(*) FROM {$this->table}";

        if ($status !== 'all') {
            $query .= " WHERE status = :status";
        }

        $stmt = $this->query($query, [':status' => $status]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Get user count by role
     *
     * @param string $role Role name
     * @return int Number of users with this role
     */
    public function getUserCountByRole($role)
    {
        $query = "SELECT COUNT(*) FROM {$this->table} WHERE role = :role AND status = 'active'";
        $stmt = $this->query($query, [':role' => $role]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Check if authentication is rate limited for an email
     *
     * @param string $email User email
     * @return bool True if rate limited
     */
    private function isRateLimited($email)
    {
        try {
            // Check if the table exists first
            if (!$this->tableExists('user_login_attempts')) {
                return false; // If table doesn't exist, don't apply rate limiting
            }

            $query = "SELECT failed_attempts, last_attempt FROM user_login_attempts WHERE email = :email";
            $stmt = $this->query($query, [':email' => $email]);
            $attempt = $stmt->fetch();

            if (!$attempt) {
                return false;
            }

            $maxAttempts = 5;
            $lockoutTime = 900; // 15 minutes

            if ($attempt['failed_attempts'] >= $maxAttempts) {
                $lastAttempt = strtotime($attempt['last_attempt']);
                if (time() - $lastAttempt < $lockoutTime) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            LogHelper::error("Error checking rate limit: " . $e->getMessage());
            return false; // Fail open - don't block authentication if there's an error
        }
    }

    /**
     * Record failed authentication attempt
     *
     * @param string $email User email
     */
    private function recordFailedAttempt($email)
    {
        try {
            // Check if the table exists first
            if (!$this->tableExists('user_login_attempts')) {
                return; // If table doesn't exist, silently skip recording
            }

            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                // SQLite doesn't support ON DUPLICATE KEY UPDATE, so we use INSERT OR REPLACE
                $checkQuery = "SELECT id, failed_attempts FROM user_login_attempts WHERE email = :email";
                $checkStmt = $this->query($checkQuery, [':email' => $email]);
                $existing = $checkStmt->fetch();

                if ($existing) {
                    $updateQuery = "UPDATE user_login_attempts SET failed_attempts = failed_attempts + 1, last_attempt = datetime('now') WHERE email = :email";
                    $this->query($updateQuery, [':email' => $email]);
                } else {
                    $insertQuery = "INSERT INTO user_login_attempts (email, failed_attempts, last_attempt) VALUES (:email, 1, datetime('now'))";
                    $this->query($insertQuery, [':email' => $email]);
                }
            } else {
                // MySQL with ON DUPLICATE KEY UPDATE
                $query = "INSERT INTO user_login_attempts (email, failed_attempts, last_attempt) 
                          VALUES (:email, 1, NOW()) 
                          ON DUPLICATE KEY UPDATE 
                          failed_attempts = failed_attempts + 1, last_attempt = NOW()";
                $this->query($query, [':email' => $email]);
            }
        } catch (\Exception $e) {
            // Silently fail - don't break auth flow for logging issues
            LogHelper::error("Failed to record login attempt: " . $e->getMessage());
        }
    }

    /**
     * Clear failed authentication attempts
     *
     * @param string $email User email
     */
    private function clearFailedAttempts($email)
    {
        try {
            // Check if the table exists first
            if (!$this->tableExists('user_login_attempts')) {
                return; // If table doesn't exist, silently skip clearing
            }

            $query = "DELETE FROM user_login_attempts WHERE email = :email";
            $this->query($query, [':email' => $email]);
        } catch (\Exception $e) {
            LogHelper::error("Failed to clear login attempts: " . $e->getMessage());
        }
    }

    /**
     * Update last login time for user
     *
     * @param int $userId User ID
     */
    private function updateLastLogin($userId)
    {
        try {
            // Check if the last_login column exists first
            if (!$this->columnExists($this->table, 'last_login')) {
                return; // If column doesn't exist, silently skip updating
            }

            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                $query = "UPDATE {$this->table} SET last_login = datetime('now') WHERE id = :id";
            } else {
                $query = "UPDATE {$this->table} SET last_login = NOW() WHERE id = :id";
            }
            $this->query($query, [':id' => $userId]);
        } catch (\Exception $e) {
            LogHelper::error("Failed to update last login: " . $e->getMessage());
        }
    }

    /**
     * Validate password strength
     *
     * @param string $password Password to validate
     * @return array Validation result with 'valid' boolean and 'errors' array
     */
    public function validatePasswordStrength($password)
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Check if a table exists in the database
     *
     * @param string $tableName Table name to check
     * @return bool True if table exists
     */
    private function tableExists($tableName)
    {
        try {
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                $query = "SELECT name FROM sqlite_master WHERE type='table' AND name = :table_name";
            } else {
                $query = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name";
            }

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':table_name', $tableName, \PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            LogHelper::error("Error checking if table exists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a column exists in a table
     *
     * @param string $tableName Table name
     * @param string $columnName Column name
     * @return bool True if column exists
     */
    private function columnExists($tableName, $columnName)
    {
        try {
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                $stmt = $this->db->query("PRAGMA table_info($tableName)");
                $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($columns as $column) {
                    if ($column['name'] === $columnName) {
                        return true;
                    }
                }
                return false;
            } else {
                $query = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name";
                $stmt = $this->db->prepare($query);
                $stmt->bindValue(':table_name', $tableName, \PDO::PARAM_STR);
                $stmt->bindValue(':column_name', $columnName, \PDO::PARAM_STR);
                $stmt->execute();

                return $stmt->rowCount() > 0;
            }
        } catch (\Exception $e) {
            LogHelper::error("Error checking if column exists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get allowed columns for ORDER BY clause
     *
     * @return array List of column names allowed in ORDER BY
     */
    protected function getAllowedOrderByColumns()
    {
        return ['id', 'username', 'email', 'display_name', 'role', 'status', 'created_at', 'updated_at'];
    }
}
