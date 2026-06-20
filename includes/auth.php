<?php
declare(strict_types=1);

/**
 * Minimal auth helpers. Hardcoded 1 admin user for UAS demo.
 * Production: replace with users table + password_hash + password_verify.
 */
const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD_HASH = '$2y$10$db.VNSG0A8soTYiwfOKwS.WZQjIJBpglirock5yHByAPTHhceZxfG';

/**
 * Returns true if current session has a valid user.
 */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * Returns current username or null.
 */
function current_user(): ?string
{
    return $_SESSION['username'] ?? null;
}

/**
 * Verify credentials. On success, set session and regenerate ID. Returns true.
 */
function login(string $username, string $password): bool
{
    if ($username !== ADMIN_USERNAME) {
        return false;
    }
    if (!password_verify($password, ADMIN_PASSWORD_HASH)) {
        return false;
    }
    // Successful login: prevent session fixation
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = $username;
    return true;
}

/**
 * Destroy session completely.
 */
function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/**
 * Generate or fetch the current session's CSRF token.
 * One token per session, persisted in $_SESSION.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Returns a hidden input HTML for CSRF token. Use in all POST forms.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify CSRF token in $_POST matches session token. Returns true if valid.
 * Use this at the top of every POST handler.
 */
function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
