<?php

namespace App\Controllers\Admin;

use App\Models\User;
use App\middlewares\AuthMiddleware;
use App\Helpers\SecurityHelper;
use App\Helpers\RedirectHelper;
use App\Helpers\SessionHelper;
use App\Helpers\RequestHelper;
use App\Helpers\CSRFHelper;
use App\Helpers\LogHelper;

/**
 * Admin User Controller
 * Handles user management in the admin dashboard
 */
class UserController extends AdminController
{
    protected $userModel;

    /**
     * UserController constructor.
     * Requires admin authentication and initializes the user model.
     *
     * @param array $params Optional parameters for the controller
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        AuthMiddleware::requireAdmin();
        $this->userModel = new User();
    }

    /**
     * Check rate limiting for sensitive operations
     *
     * @param string $operation Operation type (create, delete, password_change)
     * @param int $limit Maximum attempts allowed
     * @param int $window Time window in seconds (default: 1 hour)
     * @return bool True if operation is allowed, false if rate limit exceeded
     */
    private function checkRateLimit($operation, $limit = 5, $window = 3600)
    {
        $key = "rate_limit_{$operation}_" . SessionHelper::getValue('user_id');
        $timeKey = $key . '_time';

        $attempts = SessionHelper::getValue($key, 0);
        $lastAttempt = SessionHelper::getValue($timeKey, 0);

        // Reset counter if more than the time window has passed
        if (time() - $lastAttempt > $window) {
            $attempts = 0;
        }

        if ($attempts >= $limit) {
            SessionHelper::setFlashMessage('Too many attempts. Please try again later.', 'error');
            return false;
        }

        // Increment attempts
        SessionHelper::setValue($key, $attempts + 1);
        SessionHelper::setValue($timeKey, time());
        return true;
    }

    /**
     * Validate username format and length
     *
     * @param string $username Username to validate
     * @return array Array with 'valid' boolean and 'error' message
     */
    private function validateUsername($username)
    {
        if (empty($username)) {
            return ['valid' => false, 'error' => 'Username is required'];
        }

        if (strlen($username) < 3) {
            return ['valid' => false, 'error' => 'Username must be at least 3 characters'];
        }

        if (strlen($username) > 50) {
            return ['valid' => false, 'error' => 'Username must not exceed 50 characters'];
        }

        // Allow only alphanumeric, underscore, and hyphen
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            return ['valid' => false, 'error' => 'Username can only contain letters, numbers, underscore, and hyphen'];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * Validate email format
     *
     * @param string $email Email to validate
     * @return array Array with 'valid' boolean and 'error' message
     */
    private function validateEmail($email)
    {
        if (empty($email)) {
            return ['valid' => false, 'error' => 'Email is required'];
        }

        $sanitizedEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
        if ($sanitizedEmail !== $email) {
            return ['valid' => false, 'error' => 'Email contains invalid characters'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'Invalid email format'];
        }

        // Additional validation for email length
        if (strlen($email) > 255) {
            return ['valid' => false, 'error' => 'Email must not exceed 255 characters'];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * Validate password strength
     *
     * @param string $password Password to validate
     * @return array Array with 'valid' boolean and 'error' message
     */
    private function validatePassword($password)
    {
        if (empty($password)) {
            return ['valid' => false, 'error' => 'Password is required'];
        }

        if (strlen($password) < 8) {
            return ['valid' => false, 'error' => 'Password must be at least 8 characters'];
        }

        if (strlen($password) > 255) {
            return ['valid' => false, 'error' => 'Password must not exceed 255 characters'];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * Validate role
     *
     * @param string $role Role to validate
     * @return array Array with 'valid' boolean and 'error' message
     */
    private function validateRole($role)
    {
        $validRoles = ['admin', 'editor', 'author', 'subscriber'];

        if (empty($role)) {
            return ['valid' => false, 'error' => 'Role is required'];
        }

        if (!in_array($role, $validRoles)) {
            return ['valid' => false, 'error' => 'Invalid role selected'];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * Validate display name
     *
     * @param string $displayName Display name to validate
     * @return array Array with 'valid' boolean and 'error' message
     */
    private function validateDisplayName($displayName)
    {
        // Display name is optional - empty is valid
        if (empty($displayName)) {
            return ['valid' => true, 'error' => ''];
        }

        // Check maximum length
        if (strlen($displayName) > 100) {
            return ['valid' => false, 'error' => 'Display name must not exceed 100 characters'];
        }

        // Check for HTML tags (XSS prevention)
        if ($displayName !== strip_tags($displayName)) {
            return ['valid' => false, 'error' => 'Display name cannot contain HTML tags'];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * Displays the list of users in the admin dashboard.
     *
     * @return void
     */
    public function indexAction()
    {
        // Get all users from the database
        $users = $this->userModel->getAllUsers();

        $this->render('admin/users/list', [
            'title' => 'User Management',
            'page_name' => 'users',
            'users' => $users,
            'admin_url' => $this->settings['ADMIN_URL'],
            'current_user_id' => SessionHelper::getValue('user_id'),
            'canEdit' => $this->roleService->hasPermission(SessionHelper::getValue('user_role'), 'admin'),
            'canDelete' => $this->roleService->hasPermission(SessionHelper::getValue('user_role'), 'admin')
        ]);
    }

    /**
     * Create a new user
     *
     */
    public function createAction()
    {
        $error = '';
        $success = false;

        // Handle form submission
        if (RequestHelper::isPost()) {
            $this->requireCsrf('/admin/users', 'user creation');

            // Check rate limiting - max 5 user creation attempts per hour
            if (!$this->checkRateLimit('user_create', 5, 3600)) {
                RedirectHelper::redirect('/admin/users/create');
                return;
            }

            // Sanitize inputs
            $username = trim(RequestHelper::post('username'));
            $email = trim(RequestHelper::post('email', null, 'email'));
            $displayName = trim(RequestHelper::post('display_name'));
            $password = RequestHelper::post('password', null, 'raw');
            $confirmPassword = RequestHelper::post('password_confirm', null, 'raw');
            $role = trim(RequestHelper::post('role'));

            // Comprehensive input validation
            $usernameValidation = $this->validateUsername($username);
            if (!$usernameValidation['valid']) {
                $error = $usernameValidation['error'];
            } else {
                $emailValidation = $this->validateEmail($email);
                if (!$emailValidation['valid']) {
                    $error = $emailValidation['error'];
                } else {
                    $displayNameValidation = $this->validateDisplayName($displayName);
                    if (!$displayNameValidation['valid']) {
                        $error = $displayNameValidation['error'];
                    } else {
                        $passwordValidation = $this->validatePassword($password);
                        if (!$passwordValidation['valid']) {
                            $error = $passwordValidation['error'];
                        } elseif ($password !== $confirmPassword) {
                            $error = 'Passwords do not match';
                        } else {
                            $roleValidation = $this->validateRole($role);
                            if (!$roleValidation['valid']) {
                                $error = $roleValidation['error'];
                            } elseif ($this->userModel->getUserByEmail($email)) {
                                $error = 'Email is already registered';
                            } else {
                                // All validation passed, create user
                                try {
                                    // Strip tags from display name as additional safety measure
                                    $sanitizedDisplayName = strip_tags($displayName);

                                    $userData = [
                                        'username' => $username,
                                        'email' => $email,
                                        'display_name' => $sanitizedDisplayName ?: $username,
                                        'password' => $password,
                                        'role' => $role
                                    ];

                                    $userId = $this->userModel->createUser($userData);

                                    if ($userId) {
                                        SessionHelper::setFlashMessage('User created successfully', 'success');
                                        // Redirect to user list with success message
                                        RedirectHelper::redirect('/admin/users');
                                    } else {
                                        $error = 'User creation failed. Please try again.';
                                    }
                                } catch (\Exception $e) {
                                    // Handle password policy violations from User model
                                    $error = $e->getMessage();
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($error)) {
                SessionHelper::setFlashMessage($error, 'error');
            }
        }

        $roles = $this->roleService->getRoleHierarchy();

        $user = [];

        $this->render('admin/users/create', [
            'title' => 'Create User',
            'page_name' => 'users',
            'error' => $error,
            'admin_url' => $this->settings['ADMIN_URL'],
            'success' => $success,
            'roles' => $roles,
            'user' => $user,
            'canEditRole' => $this->roleService->hasPermission(SessionHelper::getValue('user_role'), 'admin')
        ]);
    }


    /**
     * Displays the form to edit an existing user and handles submission.
     *
     * @return void
     */
    public function editAction()
    {
        // Get the user ID from the URL
        $userId = isset($this->params[0]) ? (int)$this->params[0] : 0;

        if ($userId <= 0) {
            RedirectHelper::redirect('/admin/users');
        }

        // Get user data
        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            SessionHelper::setFlashMessage('User not found', 'error');
            RedirectHelper::redirect('/admin/users');
        }

        if (!$this->roleService->hasPermission(SessionHelper::getValue('user_role'), $user['role'])) {
            SessionHelper::setFlashMessage('You don\'t have permission to edit this user', 'error');
            RedirectHelper::redirect('/admin/users');
        }

        $error = '';
        $success = false;

        // Handle form submission
        if (RequestHelper::isPost()) {
            $this->requireCsrf('/admin/users', 'user edit');

            // Sanitize inputs
            $username = trim(RequestHelper::post('username'));
            $email = trim(RequestHelper::post('email', null, 'email'));
            $displayName = trim(RequestHelper::post('display_name'));
            $password = RequestHelper::post('password', null, 'raw');
            $confirmPassword = RequestHelper::post('password_confirm', null, 'raw');
            $role = trim(RequestHelper::post('role'));

            // If password is being changed, apply rate limiting
            $isPasswordChange = !empty($password);
            if ($isPasswordChange) {
                if (!$this->checkRateLimit('password_change', 5, 3600)) {
                    RedirectHelper::redirect("/admin/users/edit/$userId");
                    return;
                }
            }

            // Comprehensive input validation
            $usernameValidation = $this->validateUsername($username);
            if (!$usernameValidation['valid']) {
                $error = $usernameValidation['error'];
            } else {
                $emailValidation = $this->validateEmail($email);
                if (!$emailValidation['valid']) {
                    $error = $emailValidation['error'];
                } else {
                    $displayNameValidation = $this->validateDisplayName($displayName);
                    if (!$displayNameValidation['valid']) {
                        $error = $displayNameValidation['error'];
                    } else {
                        $roleValidation = $this->validateRole($role);
                        if (!$roleValidation['valid']) {
                            $error = $roleValidation['error'];
                        } else {
                            // Validate password if provided
                            if ($isPasswordChange) {
                                $passwordValidation = $this->validatePassword($password);
                                if (!$passwordValidation['valid']) {
                                    $error = $passwordValidation['error'];
                                } elseif ($password !== $confirmPassword) {
                                    $error = 'Passwords do not match';
                                }
                            }

                            if (empty($error)) {
                                // Check if email is already taken by another user
                                $existingUser = $this->userModel->getUserByEmail($email);
                                if ($existingUser && $existingUser['id'] != $userId) {
                                    $error = 'Email is already registered by another user';
                                } else {
                                    // Validate role hierarchy
                                    if (!$this->roleService->hasPermission(SessionHelper::getValue('user_role'), $role)) {
                                        SessionHelper::setFlashMessage('You can\'t assign this role', 'error');
                                        RedirectHelper::redirect("/admin/users/edit/$userId");
                                        return;
                                    }

                                    // Update user data
                                    try {
                                        // Strip tags from display name as additional safety measure
                                        $sanitizedDisplayName = strip_tags($displayName);

                                        $userData = [
                                            'username' => $username,
                                            'email' => $email,
                                            'display_name' => $sanitizedDisplayName ?: $username,
                                            'role' => $role
                                        ];

                                        // Only update password if provided
                                        if ($isPasswordChange) {
                                            $userData['password'] = $password;
                                        }

                                        $success = $this->userModel->updateUser($userId, $userData);

                                        if ($success) {
                                            // Redirect to user list with success message
                                            SessionHelper::setFlashMessage('User updated successfully', 'success');
                                            RedirectHelper::redirect('/admin/users');
                                        } else {
                                            $error = 'User update failed. Please try again.';
                                        }
                                    } catch (\Exception $e) {
                                        // Handle password policy violations from User model
                                        $error = $e->getMessage();
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($error)) {
                SessionHelper::setFlashMessage($error, 'error');
            }
        }

        $roles = $this->roleService->getRoleHierarchy();

        $this->render('admin/users/edit', [
            'title' => 'Edit User',
            'page_name' => 'users',
            'user' => $user,
            'error' => $error,
            'success' => $success,
            'roles' => $roles,
            'canEditRole' => $this->roleService->hasPermission(SessionHelper::getValue('user_role'), 'admin')
        ]);
    }


    /**
     * Deletes a user by ID.
     *
     * @return void
     */
    public function deleteAction()
    {
        // Get the user ID from the URL params
        $userId = isset($this->params[0]) ? (int)$this->params[0] : 0;

        if ($userId <= 0) {
            RedirectHelper::redirect('/admin/users');
        }

        // Prevent deleting the current user
        if ($userId == SessionHelper::getValue('user_id')) {
            SessionHelper::setFlashMessage('You cannot delete yourself', 'error');
            RedirectHelper::redirect('/admin/users');
        }

        // Get user data
        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            SessionHelper::setFlashMessage('User not found', 'error');
            RedirectHelper::redirect('/admin/users');
        }

        $error = '';
        $success = false;

        // Handle form submission
        if (RequestHelper::isPost() && RequestHelper::post('confirm_delete')) {
            $this->requireCsrf('/admin/users', 'user deletion');

            // Check rate limiting - max 5 delete attempts per hour
            if (!$this->checkRateLimit('user_delete', 5, 3600)) {
                RedirectHelper::redirect("/admin/users/delete/$userId");
                return;
            }

            $success = $this->userModel->deleteUser($userId);

            if ($success) {
                // Redirect to user list with success message
                SessionHelper::setFlashMessage('User deleted successfully', 'success');
                RedirectHelper::redirect('/admin/users');
            } else {
                SessionHelper::setFlashMessage('User deletion failed. Please try again.', 'error');
            }
        }

        $this->render('admin/users/delete', [
            'title' => 'Delete User',
            'page_name' => 'users',
            'user' => $user,
            'error' => $error,
            'canDelete' => $this->roleService->hasPermission(SessionHelper::getValue('user_role'), 'admin')
        ]);
    }
}
