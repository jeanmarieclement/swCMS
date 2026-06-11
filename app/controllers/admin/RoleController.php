<?php

namespace App\Controllers\Admin;

use App\Helpers\SecurityHelper;
use App\Models\Role;
use App\Models\User;
use App\middlewares\AuthMiddleware;
use App\Helpers\RedirectHelper;
use App\Helpers\SessionHelper;
use App\Helpers\LogHelper;

class RoleController extends AdminController
{
    private $userModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->userModel = new User();
    }

    /**
     * Displays the list of roles and their permissions.
     *
     * @return void
     */
    public function indexAction()
    {
        // Fetch all roles and their permissions
        $roles = $this->roleService->getRoles();

        foreach ($roles as &$role) {
            // Get permissions for each role
            $role['permissions'] = $this->roleService->getAccessibleTemplates($role['name']);

            // Get user count for each role
            $role['user_count'] = $this->getUserCountByRole($role['name']);

            // Ensure description field exists
            if (!isset($role['description']) || empty($role['description'])) {
                $role['description'] = $this->getDefaultRoleDescription($role['name'], $role['level']);
            }
        }

        // Render the view with enhanced role data
        $this->render('admin/roles/index', [
            'title' => 'Roles & Permissions',
            'page_name' => 'roles',
            'roles' => $roles
        ]);
    }

    /**
     * Get user count by role
     */
    private function getUserCountByRole($roleName): int
    {
        try {
            return $this->userModel->getUserCountByRole($roleName);
        } catch (\Exception $e) {
            LogHelper::warning('Error getting user count for role', ['role' => $roleName, 'error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Get default description for a role
     */
    private function getDefaultRoleDescription($roleName, $level): string
    {
        $descriptions = [
            'super_admin' => 'Full system access with all administrative privileges',
            'admin' => 'Administrative access to manage users, content, and system settings',
            'editor' => 'Can edit all content, manage categories, and moderate comments',
            'author' => 'Can create and edit own content, upload media files',
            'subscriber' => 'Basic read-only access to the admin dashboard'
        ];

        if (isset($descriptions[$roleName])) {
            return $descriptions[$roleName];
        }

        // Generate default description based on level
        $levelDescriptions = [
            4 => 'Full administrative access with system-wide privileges',
            3 => 'Administrative access with content and user management',
            2 => 'Content management with editing capabilities',
            1 => 'Content creation with limited editing permissions',
            0 => 'Basic access with read-only permissions'
        ];

        return $levelDescriptions[$level] ?? 'Custom role with specific permissions';
    }


    /**
     * Displays the form to edit a role and handles updates.
     *
     * @return void
     */
    public function editAction()
    {
        // Get role ID from URL parameters
        $roleId = isset($this->params[0]) ? (int)$this->params[0] : 0;

        // If no role ID provided, redirect to roles list
        if (!$roleId) {
            $this->setFlashMessage('error', 'No role ID provided');
            RedirectHelper::redirect('/admin/roles');
            return;
        }

        // Get role details
        $role = $this->roleService->getRoleById($roleId);

        // If role not found, redirect to roles list
        if (!$role) {
            $this->setFlashMessage('error', 'Role not found');
            RedirectHelper::redirect('/admin/roles');
            return;
        }

        // Get current permissions for this role
        $role['permissions'] = $this->roleService->getAccessibleTemplates($role['name']);
        $role['user_count'] = $this->getUserCountByRole($role['name']);

        // Ensure description field exists
        if (!isset($role['description']) || empty($role['description'])) {
            $role['description'] = $this->getDefaultRoleDescription($role['name'], $role['level']);
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Get permissions from POST data
                $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];

                // Check if we should grant all permissions
                if (in_array('all', $permissions)) {
                    $permissions = ['*']; // Use wildcard to grant all permissions
                }

                // Update role permissions
                $success = $this->roleService->updatePermissions($roleId, $permissions);

                if ($success) {
                    LogHelper::info('Role permissions updated successfully', [
                        'role_id' => $roleId,
                        'role_name' => $role['name'],
                        'permissions' => $permissions,
                        'user_id' => $_SESSION['user_id'] ?? null
                    ]);

                    $this->setFlashMessage('success', "Permessi del ruolo '{$role['name']}' aggiornati con successo!");
                    RedirectHelper::redirect('/admin/roles');
                    return;
                } else {
                    $this->setFlashMessage('error', 'Impossibile aggiornare i permessi del ruolo. Controlla i permessi del database e riprova.');
                }
            } catch (\Exception $e) {
                LogHelper::error('Error updating role permissions', [
                    'role_id' => $roleId,
                    'error' => $e->getMessage()
                ]);

                // Provide user-friendly error messages based on error type
                if (strpos($e->getMessage(), 'readonly database') !== false) {
                    $this->setFlashMessage('error', 'Errore: Il database è in sola lettura. Controlla i permessi del file database.');
                } elseif (strpos($e->getMessage(), 'database is locked') !== false) {
                    $this->setFlashMessage('error', 'Errore: Database temporaneamente bloccato. Riprova tra qualche secondo.');
                } else {
                    $this->setFlashMessage('error', 'Errore durante l\'aggiornamento dei permessi: ' . $e->getMessage());
                }
            }

            // Refresh role data after attempting update (whether success or failure)
            $role['permissions'] = $this->roleService->getAccessibleTemplates($role['name']);
        }

        // Render the edit form
        $this->render('admin/roles/edit', [
            'title' => 'Edit Role - ' . $role['name'],
            'page_name' => 'roles_edit',
            'role' => $role
        ]);
    }

    /**
     * Create a new role (placeholder for future implementation)
     */
    public function createAction()
    {
        $this->setFlashMessage('info', 'Role creation feature will be available in a future version');
        RedirectHelper::redirect('/admin/roles');
    }

    /**
     * Delete a role (placeholder for future implementation)
     */
    public function deleteAction()
    {
        $roleId = isset($this->params[0]) ? (int)$this->params[0] : 0;

        if (!$roleId) {
            $this->setFlashMessage('error', 'No role ID provided');
            RedirectHelper::redirect('/admin/roles');
            return;
        }

        // Get role details for logging
        $role = $this->roleService->getRoleById($roleId);

        if (!$role) {
            $this->setFlashMessage('error', 'Role not found');
            RedirectHelper::redirect('/admin/roles');
            return;
        }

        // Protect system roles
        if (in_array($role['name'], ['super_admin', 'admin'])) {
            $this->setFlashMessage('error', 'System roles cannot be deleted');
            RedirectHelper::redirect('/admin/roles');
            return;
        }

        $this->setFlashMessage('info', 'Role deletion feature will be available in a future version');
        RedirectHelper::redirect('/admin/roles');
    }
}
