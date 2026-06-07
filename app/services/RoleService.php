<?php

namespace App\Services;

use App\Models\Role;
use \PDO;

class RoleService
{
    private $roleModel;

    public function __construct()
    {
        $this->roleModel = new Role();
    }

    public function getRoles(): array
    {
        return $this->roleModel->getAllRoles();
    }

    public function hasPermission(string $userRole, string $requiredRole): bool
    {

        $userRoleLevel = $this->roleModel->getRoleLevel($userRole);
        $requiredRoleLevel = $this->roleModel->getRoleLevel($requiredRole);
        
        if ($userRoleLevel === null || $requiredRoleLevel === null) {
            return false;
        }

        return $userRoleLevel >= $requiredRoleLevel;
    }

    public function getRoleHierarchy(): array
    {
        return $this->roleModel->getRoleNames();
    }

    public function canAccessTemplate(string $roleName, string $templatePath): bool
    {
        // Super admin always has access to all templates
        if ($roleName === 'super_admin' || $roleName === 'admin') {
            return true;
        }
        
        $permissions = $this->roleModel->getTemplatePermissions($roleName);
        
        // Convert template path to consistent format
        $template = str_replace(['admin/', '.tpl'], '', $templatePath);
        
        // Allow access to dashboard for all authenticated users
        if ($template === 'dashboard') {
            return true;
        }
        
        // Allow access if no specific permissions set (backward compatibility)
        if (empty($permissions['allowed_templates'])) {
            return true;
        }
        
        // Check for wildcard access
        if (in_array('*', $permissions['allowed_templates'])) {
            return true;
        }
        
        $hasAccess = in_array($template, $permissions['allowed_templates']);
        
        return $hasAccess;
    }

    public function getAccessibleTemplates(string $roleName): array
    {
        $permissions = $this->roleModel->getTemplatePermissions($roleName);
        return $permissions['allowed_templates'] ?? [];
    }
    
    /**
     * Update role permissions
     * 
     * @param int $roleId The ID of the role to update
     * @param array $templatePermissions Array of template names the role has access to
     * @return bool True if update was successful, false otherwise
     */
    public function updatePermissions(int $roleId, array $templatePermissions): bool
    {
        // Get the role by ID using the inherited method
        $role = $this->roleModel->getById($roleId);
        
        if (!$role) {
            return false;
        }
        
        $roleName = $role['name'];
        
        // Prepare permissions array
        $permissions = [
            'allowed_templates' => $templatePermissions
        ];
        
        // Update permissions in the database
        return $this->roleModel->updateTemplatePermissions($roleName, $permissions);
    }
    
    /**
     * Get role by ID
     * 
     * @param int $roleId The ID of the role to retrieve
     * @return array|false Role data or false if not found
     */
    public function getRoleById(int $roleId)
    {
        return $this->roleModel->getById($roleId);
    }
}
