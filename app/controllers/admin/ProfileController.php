<?php

/**
 * AdminProfileController
 * Handles user profile display and update in the admin area
 */

namespace App\Controllers\Admin;

use App\Models\User;
use App\Helpers\AuthHelper;
use App\Helpers\SessionHelper;
use App\Helpers\SecurityHelper;
use App\Helpers\RedirectHelper;
use App\middlewares\AuthMiddleware;
use App\Helpers\RequestHelper;
use App\Helpers\CSRFHelper;
use App\Helpers\LogHelper;

class ProfileController extends AdminController
{
    protected $userModel;

    public function __construct($params = [])
    {
        parent::__construct($params);

        // Ensure user is authenticated
        AuthMiddleware::requireAuth();

        $this->userModel = new User();
    }

    /**
     * Display and update user profile
     */
    public function indexAction()
    {
        // Get current user ID from session
        $userId = AuthHelper::getCurrentUserId();
        if (!$userId) {
            SessionHelper::setFlashMessage('You must be logged in to access your profile.', 'error');
            RedirectHelper::redirect('/auth/login');
            return;
        }

        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            SessionHelper::setFlashMessage('User not found.', 'error');
            RedirectHelper::redirect('/admin/dashboard');
            return;
        }

        $error = '';
        $success = false;

        // Handle form submission
        if (RequestHelper::isPost()) {
            // Validate CSRF token
            if (!CSRFHelper::validateRequest()) {
                SessionHelper::setFlashMessage('Invalid CSRF token. Please try again.', 'error');
                LogHelper::warning('CSRF validation failed for profile update from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown'));
                RedirectHelper::redirect('/admin/profile');
                return;
            }

            // Sanitize inputs
            $username = trim(RequestHelper::post('username'));
            $email = RequestHelper::post('email', null, 'email');
            $displayName = trim(RequestHelper::post('display_name'));
            $password = RequestHelper::post('password', null, 'raw');
            $confirmPassword = RequestHelper::post('confirm_password', null, 'raw');

            // Validate input
            if (empty($username) || empty($email)) {
                $error = 'Username and email are required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address';
            } elseif (!empty($password) && $password !== $confirmPassword) {
                $error = 'Passwords do not match';
            } else {
                // Check if email is already taken by another user
                $existingUser = $this->userModel->getUserByEmail($email);
                if ($existingUser && $existingUser['id'] != $userId) {
                    $error = 'Email is already registered by another user';
                } else {
                    // Update user data
                    $userData = [
                        'username' => $username,
                        'email' => $email,
                        'display_name' => $displayName ?: $username
                    ];

                    // Only update password if provided
                    if (!empty($password)) {
                        $userData['password'] = $password;
                    }

                    try {
                        $success = $this->userModel->updateUser($userId, $userData);

                        if ($success) {
                            SessionHelper::setFlashMessage('Profile updated successfully', 'success');
                            // Update session display name if changed
                            if (isset($userData['display_name'])) {
                                SessionHelper::setValue('user_display_name', $userData['display_name']);
                            }
                            // Refresh user data
                            $user = $this->userModel->getUserById($userId);
                        } else {
                            $error = 'Failed to update profile. Please try again.';
                        }
                    } catch (\Exception $e) {
                        // Handle password policy violations from User model
                        $error = $e->getMessage();
                    }
                }
            }
        }

        $this->render('admin/profile', [
            'title' => 'Your Profile',
            'page_name' => 'profile',
            'user' => $user,
            'error' => $error,
            'success' => $success
        ]);
    }
}
