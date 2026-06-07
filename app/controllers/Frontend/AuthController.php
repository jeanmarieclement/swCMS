<?php

namespace App\Controllers\Frontend;

use App\Helpers\RedirectHelper;
use App\Helpers\SessionHelper;
use App\Controllers\Frontend\BaseController;
use App\Helpers\LogHelper;
use App\Models\User;
use App\Helpers\HookHelper;
use App\Helpers\RequestHelper;
use App\Helpers\CSRFHelper;
use App\Helpers\ValidationHelper;
use App\Exceptions\SecurityException;

/**
 * Auth Controller
 * Handles user authentication (login, logout, registration)
 */
class AuthController extends BaseController

{
    private $userModel;


    /**
     * AuthController constructor.
     * Initializes the user model.
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->userModel = new User;
    }


    /**
     * Redirects to the login page.
     *
     * @return void
     */
    public function indexAction()
    {
        RedirectHelper::redirect('/auth/login');
    }

    /**
     * Handles user login (GET and POST).
     *
     * @return void
     */
    public function loginAction()
    {
        // If user is already logged in, redirect to admin dashboard
        if (SessionHelper::hasValue('user_id')) {
            RedirectHelper::redirect('/admin');
        }

        $error = '';

        // Handle login form submission
        if (RequestHelper::isPost()) {
            // Validate CSRF token
            if (!CSRFHelper::validateRequest()) {
                $error = 'Invalid CSRF token. Please try again.';
                LogHelper::warning('CSRF validation failed for login attempt from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown'));
            } else {
                $email = RequestHelper::post('email', null, 'email');
                $password = RequestHelper::post('password', null, 'raw');

                // Fire login attempt hook
                HookHelper::doAction('login_attempt', $email, RequestHelper::server('REMOTE_ADDR', 'unknown'));

                // Validate input
                $validationErrors = ValidationHelper::validate([
                    'email' => $email,
                    'password' => $password
                ], [
                    'email' => ['required', 'email'],
                    'password' => ['required']
                ]);

                if (!$validationErrors['valid']) {
                    $error = 'Please enter both email and password';
                    // Fire login failed hook
                    HookHelper::doAction('login_failed', $email, 'missing_credentials', RequestHelper::server('REMOTE_ADDR', 'unknown'));
                } else {
                    // Allow plugins to modify credentials before authentication
                    $email = HookHelper::applyFilters('pre_authenticate_email', $email);
                    $password = HookHelper::applyFilters('pre_authenticate_password', $password, $email);

                    // Attempt to authenticate user
                    $user = $this->userModel->authenticate($email, $password);

                    if ($user) {
                        // Fire before login hook (allows plugins to modify user data or prevent login)
                        $user = HookHelper::applyFilters('before_user_login', $user);
                        $allowLogin = HookHelper::applyFilters('allow_user_login', true, $user);

                        if (!$allowLogin) {
                            $error = 'Login not allowed';
                            HookHelper::doAction('login_failed', $email, 'not_allowed', RequestHelper::server('REMOTE_ADDR', 'unknown'));
                        } else {
                            // Set session variables
                            SessionHelper::setValue('user_id', $user['id']);
                            SessionHelper::setValue('user_email', $user['email']);
                            SessionHelper::setValue('user_username', $user['username']);
                            SessionHelper::setValue('user_display_name', $user['display_name'] ?? $user['username']);
                            SessionHelper::setValue('user_role', $user['role']);
                            SessionHelper::setValue('last_activity', time());

                            // Debug: Log successful login
                            LogHelper::debug('User authenticated successfully: ' . $user['email'] . ' with role: ' . $user['role']);

                            // Regenerate session ID for security
                            session_regenerate_id(true);

                            // Update last login time
                            $this->userModel->updateUser($user['id'], ['updated_at' => date('Y-m-d H:i:s')]);

                            // Fire user login hook
                            HookHelper::doAction('user_login', $user, RequestHelper::server('REMOTE_ADDR', 'unknown'));

                            // Allow plugins to modify redirect URL
                            $defaultRedirect = '/admin';
                            if (SessionHelper::hasValue('redirect_after_login')) {
                                $defaultRedirect = SessionHelper::getValue('redirect_after_login');
                                SessionHelper::removeValue('redirect_after_login');
                            }

                            $redirectUrl = HookHelper::applyFilters('login_redirect_url', $defaultRedirect, $user);

                            LogHelper::info('Redirecting after login to: ' . $redirectUrl);
                            RedirectHelper::redirect($redirectUrl);
                        }
                    } else {
                        $error = 'Invalid email or password';
                        // Fire login failed hook
                        HookHelper::doAction('login_failed', $email, 'invalid_credentials', RequestHelper::server('REMOTE_ADDR', 'unknown'));
                    }
                }
            }
        }

        // Render login form
        $this->render('auth/login', [
            'title' => 'Login',
            'error' => $error,
            'allow_registration' => \App\Helpers\SystemSettingsHelper::get('ALLOW_REGISTRATION')
        ]);
    }

    
    /**
     * Logs out the current user and destroys the session.
     *
     * @return void
     */
    public function logoutAction()
    {
        // Get user info before logout for hooks
        $userId = SessionHelper::getValue('user_id');
        $userEmail = SessionHelper::getValue('user_email');
        $userData = [];

        if ($userId) {
            $userData = $this->userModel->getById($userId);
        }

        // Fire before logout hook
        HookHelper::doAction('before_user_logout', $userData, RequestHelper::server('REMOTE_ADDR', 'unknown'));

        // Allow plugins to modify logout behavior
        $allowLogout = HookHelper::applyFilters('allow_user_logout', true, $userData);

        if (!$allowLogout) {
            // If logout is not allowed, redirect back
            RedirectHelper::redirect('/admin');
            return;
        }

        // Unset all session variables
        session_unset(); // Clear all session variables

        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy the session
        session_destroy();

        // Fire user logout hook
        HookHelper::doAction('user_logout', $userData, RequestHelper::server('REMOTE_ADDR', 'unknown'));

        // Allow plugins to modify logout redirect URL
        $logoutRedirectUrl = HookHelper::applyFilters('logout_redirect_url', '/auth/login', $userData);

        // Redirect to login page or custom URL
        RedirectHelper::redirect($logoutRedirectUrl);
    }

    
    /**
     * Handles user registration (GET and POST).
     *
     * @return void
     */
    public function registerAction()
    {
        // Check if registration is allowed
        if (!\App\Helpers\SystemSettingsHelper::get('ALLOW_REGISTRATION')) {
            RedirectHelper::redirect('/auth/login');
        }

        $error = '';
        $success = false;

        // Handle registration form submission
        if (RequestHelper::isPost()) {
            // Validate CSRF token
            if (!CSRFHelper::validateRequest()) {
                $error = 'Invalid CSRF token. Please try again.';
                LogHelper::warning('CSRF validation failed for registration attempt from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown'));
            } else {
                // Get sanitized inputs
                $username = RequestHelper::post('username');
                $email = RequestHelper::post('email', null, 'email');
                $displayName = RequestHelper::post('display_name');
                $password = RequestHelper::post('password', null, 'raw');
                $confirmPassword = RequestHelper::post('confirm_password', null, 'raw');

                // Validate input using ValidationHelper
                $validationErrors = ValidationHelper::validate([
                    'username' => $username,
                    'email' => $email,
                    'password' => $password
                ], [
                    'username' => ['required', 'username'],
                    'email' => ['required', 'email'],
                    'password' => ['required', 'min:8']
                ]);

                if (!$validationErrors['valid']) {
                    $error = 'Please fill in all required fields correctly';
                    if (!empty($validationErrors['errors'])) {
                        $error = implode(', ', array_map(function($errors) {
                            return implode(', ', $errors);
                        }, $validationErrors['errors']));
                    }
                } elseif ($password !== $confirmPassword) {
                    $error = 'Passwords do not match';
                } elseif ($this->userModel->getUserByEmail($email)) {
                    $error = 'Email is already registered';
                } else {
                    // Create new user
                    $userData = [
                        'username' => $username,
                        'email' => $email,
                        'display_name' => $displayName ?: $username,
                        'password' => $password,
                        'role' => 'subscriber' // Default role
                    ];

                    try {
                        $userId = $this->userModel->createUser($userData);

                        if ($userId) {
                            $success = true;
                            LogHelper::info('New user registered: ' . $email);
                        } else {
                            $error = 'Registration failed. Please try again.';
                        }
                    } catch (\Exception $e) {
                        // Handle password policy violations from User model
                        $error = $e->getMessage();
                        LogHelper::error('Registration failed: ' . $e->getMessage());
                    }
                }
            }
        }

        // Render registration form
        $this->render('auth/register', [
            'title' => 'Register',
            'error' => $error,
            'success' => $success
        ]);
    }

    /**
     * Unauthorized action - displays access denied page
     * @return void
     */
    public function unauthorizedAction()
    {
        // Render unauthorized page
        $this->render('errors/unauthorized', [
            'title' => 'Access Denied',
            'error_code' => 403
        ]);
    }

    /**
     * Display password reset request form
     * @return void
     */
    public function forgotPasswordAction()
    {
        // If user is already logged in, redirect to admin dashboard
        if (SessionHelper::hasValue('user_id')) {
            RedirectHelper::redirect('/admin');
        }

        $this->render('auth/forgot_password', [
            'title' => 'Forgot Password'
        ]);
    }

    /**
     * Handle password reset request with rate limiting
     * @return void
     */
    public function requestPasswordResetAction()
    {
        // If user is already logged in, redirect to admin dashboard
        if (SessionHelper::hasValue('user_id')) {
            RedirectHelper::redirect('/admin');
        }

        if (RequestHelper::isPost()) {
            // Validate CSRF token
            if (!CSRFHelper::validateRequest()) {
                SessionHelper::setFlashMessage('Invalid CSRF token. Please try again.', 'error');
                LogHelper::warning('CSRF validation failed for password reset from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown'));
                RedirectHelper::redirect('/auth/forgot-password');
                return;
            }

            $email = RequestHelper::post('email', null, 'email');

            // Basic email validation
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Show generic message even for invalid email format
                SessionHelper::setFlashMessage('If that email exists, a reset link has been sent.', 'success');
                RedirectHelper::redirect('/auth/login');
                return;
            }

            // Rate limiting - max 3 requests per hour per email
            // Normalize email to lowercase to prevent case-sensitive bypass
            $normalizedEmail = strtolower(trim($email));
            $rateLimitKey = 'password_reset_' . md5($normalizedEmail);
            $timeKey = $rateLimitKey . '_time';
            $attempts = SessionHelper::getValue($rateLimitKey, 0);
            $lastAttempt = SessionHelper::getValue($timeKey, 0);

            // Reset counter if more than 1 hour has passed
            if (time() - $lastAttempt > 3600) {
                $attempts = 0;
            }

            // Check if rate limit exceeded
            if ($attempts >= 3) {
                // Still show generic message to prevent enumeration
                SessionHelper::setFlashMessage('If that email exists, a reset link has been sent.', 'success');

                // Log the rate limit event for security monitoring
                LogHelper::warning('Password reset rate limit exceeded for email: ' . $email . ' from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown'));

                RedirectHelper::redirect('/auth/login');
                return;
            }

            // Increment attempts
            SessionHelper::setValue($rateLimitKey, $attempts + 1);
            SessionHelper::setValue($timeKey, time());

            // Check if user exists (but don't reveal this information)
            $user = $this->userModel->getUserByEmail($email);

            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $tokenHash = password_hash($token, PASSWORD_BCRYPT);
                $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiration

                // Store reset token in database or session
                // For this implementation, we'll use session storage
                SessionHelper::setValue('password_reset_token_' . $user['id'], $tokenHash);
                SessionHelper::setValue('password_reset_expires_' . $user['id'], $expiresAt);
                SessionHelper::setValue('password_reset_email_' . $user['id'], $email);

                // Fire password reset requested hook
                HookHelper::doAction('password_reset_requested', $user, $token, RequestHelper::server('REMOTE_ADDR', 'unknown'));

                // In a production environment, send email with reset link
                // For now, we'll log it (without the token for security)
                LogHelper::info('Password reset requested for: ' . $email);

                // NOTE: Email delivery not implemented. To enable, configure a mailer (PHPMailer, SMTP)
                // and send a message containing: /auth/reset-password?token={$token}&email={$email}
            }

            // Always show the same generic success message to prevent email enumeration
            SessionHelper::setFlashMessage('If that email exists, a reset link has been sent.', 'success');
            RedirectHelper::redirect('/auth/login');
        } else {
            // GET request - redirect to forgot password form
            RedirectHelper::redirect('/auth/forgot-password');
        }
    }

    /**
     * Display password reset form
     * @return void
     */
    public function resetPasswordAction()
    {
        // If user is already logged in, redirect to admin dashboard
        if (SessionHelper::hasValue('user_id')) {
            RedirectHelper::redirect('/admin');
        }

        $token = RequestHelper::get('token');
        $email = RequestHelper::get('email', null, 'email');

        // Validate parameters
        if (empty($token) || empty($email)) {
            SessionHelper::setFlashMessage('Invalid or expired reset link.', 'error');
            RedirectHelper::redirect('/auth/forgot-password');
            return;
        }

        $this->render('auth/reset_password', [
            'title' => 'Reset Password',
            'token' => $token,
            'email' => $email
        ]);
    }

    /**
     * Process password reset
     * @return void
     */
    public function processPasswordResetAction()
    {
        // If user is already logged in, redirect to admin dashboard
        if (SessionHelper::hasValue('user_id')) {
            RedirectHelper::redirect('/admin');
        }

        if (RequestHelper::isPost()) {
            // Validate CSRF token
            if (!CSRFHelper::validateRequest()) {
                SessionHelper::setFlashMessage('Invalid CSRF token. Please try again.', 'error');
                LogHelper::warning('CSRF validation failed for password reset process from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown'));
                RedirectHelper::redirect('/auth/forgot-password');
                return;
            }

            $token = RequestHelper::post('token');
            $email = RequestHelper::post('email', null, 'email');
            $password = RequestHelper::post('password', null, 'raw');
            $confirmPassword = RequestHelper::post('confirm_password', null, 'raw');

            // Validate inputs
            if (empty($token) || empty($email) || empty($password)) {
                SessionHelper::setFlashMessage('Invalid reset request.', 'error');
                RedirectHelper::redirect('/auth/forgot-password');
                return;
            }

            if ($password !== $confirmPassword) {
                SessionHelper::setFlashMessage('Passwords do not match.', 'error');
                $this->render('auth/reset_password', [
                    'title' => 'Reset Password',
                    'token' => $token,
                    'email' => $email,
                    'error' => 'Passwords do not match'
                ]);
                return;
            }

            // Get user by email
            $user = $this->userModel->getUserByEmail($email);

            if (!$user) {
                // Don't reveal that user doesn't exist
                SessionHelper::setFlashMessage('Invalid or expired reset link.', 'error');
                RedirectHelper::redirect('/auth/forgot-password');
                return;
            }

            // Verify token
            $storedTokenHash = SessionHelper::getValue('password_reset_token_' . $user['id']);
            $expiresAt = SessionHelper::getValue('password_reset_expires_' . $user['id']);
            $storedEmail = SessionHelper::getValue('password_reset_email_' . $user['id']);

            if (!$storedTokenHash || !$expiresAt || $storedEmail !== $email) {
                SessionHelper::setFlashMessage('Invalid or expired reset link.', 'error');
                RedirectHelper::redirect('/auth/forgot-password');
                return;
            }

            // Check if token expired
            if (strtotime($expiresAt) < time()) {
                // Clean up expired token
                SessionHelper::removeValue('password_reset_token_' . $user['id']);
                SessionHelper::removeValue('password_reset_expires_' . $user['id']);
                SessionHelper::removeValue('password_reset_email_' . $user['id']);

                SessionHelper::setFlashMessage('Reset link has expired. Please request a new one.', 'error');
                RedirectHelper::redirect('/auth/forgot-password');
                return;
            }

            // Verify token hash
            if (!password_verify($token, $storedTokenHash)) {
                SessionHelper::setFlashMessage('Invalid reset link.', 'error');
                RedirectHelper::redirect('/auth/forgot-password');
                return;
            }

            // Update password
            try {
                $updateResult = $this->userModel->updateUser($user['id'], [
                    'password' => $password
                ]);

                if ($updateResult) {
                    // Clean up token data
                    SessionHelper::removeValue('password_reset_token_' . $user['id']);
                    SessionHelper::removeValue('password_reset_expires_' . $user['id']);
                    SessionHelper::removeValue('password_reset_email_' . $user['id']);

                    // Fire password reset successful hook
                    HookHelper::doAction('password_reset_successful', $user, RequestHelper::server('REMOTE_ADDR', 'unknown'));

                    LogHelper::info('Password reset successful for: ' . $email);

                    SessionHelper::setFlashMessage('Password reset successful. You can now login with your new password.', 'success');
                    RedirectHelper::redirect('/auth/login');
                } else {
                    SessionHelper::setFlashMessage('Failed to reset password. Please try again.', 'error');
                    RedirectHelper::redirect('/auth/forgot-password');
                }
            } catch (\Exception $e) {
                // Handle password policy violations
                SessionHelper::setFlashMessage($e->getMessage(), 'error');
                $this->render('auth/reset_password', [
                    'title' => 'Reset Password',
                    'token' => $token,
                    'email' => $email,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            RedirectHelper::redirect('/auth/forgot-password');
        }
    }

    /**
     * Helper method to redirect
     */
    private function redirectTo($path)
    {
        RedirectHelper::redirect($this->settings['SITE_URL'] . $path);
        exit;
    }
}
