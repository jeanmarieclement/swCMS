<?php

namespace App\Models;

use App\Core\Model;

/**
 * User role management
 */
class Role extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'roles';
    }

    public function getAllRoles(): array
    {
        $stmt = $this->query("SELECT * FROM {$this->table} ORDER BY level DESC");
        return $stmt->fetchAll();
    }

    public function getRoleLevel(string $roleName): ?int
    {
        $stmt = $this->query("SELECT level FROM {$this->table} WHERE name = :name", [':name' => $roleName]);
        $result = $stmt->fetch();
        return $result ? (int)$result['level'] : null;
    }

    public function getRoleNames(): array
    {
        $stmt = $this->query("SELECT name FROM {$this->table} ORDER BY level DESC");
        $roles = $stmt->fetchAll();
        return array_column($roles, 'name');
    }

    public function getTemplatePermissions(string $roleName): array
    {
        $stmt = $this->query("SELECT template_permissions FROM {$this->table} WHERE name = :name", [':name' => $roleName]);
        $result = $stmt->fetch();
        return $result ? json_decode($result['template_permissions'], true) : [];
    }

    public function updateTemplatePermissions(string $roleName, array $permissions): bool
    {
        try {
            $stmt = $this->query("UPDATE {$this->table} SET template_permissions = :permissions WHERE name = :name", [':name' => $roleName, ':permissions' => json_encode($permissions)]);
            return $stmt->rowCount() >= 0; // Return true if query executed (even if no rows changed)
        } catch (\Exception $e) {
            error_log("Role model update failed: " . $e->getMessage());
            return false; // Return false on any database error
        }
    }

    /**
     * Get allowed columns for ORDER BY clause
     *
     * @return array List of column names allowed in ORDER BY
     */
    protected function getAllowedOrderByColumns()
    {
        return ['name', 'level', 'description', 'created_at', 'updated_at'];
    }
}
