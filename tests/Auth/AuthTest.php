<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

final class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset session for each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }

    public function testIsLoggedInReturnsFalseWhenNotAuthenticated(): void
    {
        $this->assertFalse(is_logged_in());
        $this->assertNull(current_user());
    }

    public function testIsLoggedInReturnsTrueAfterLogin(): void
    {
        $this->assertTrue(login('admin', 'admin123'));
        $this->assertTrue(is_logged_in());
        $this->assertSame('admin', current_user());
    }

    public function testLoginFailsWithWrongUsername(): void
    {
        $this->assertFalse(login('wrong', 'admin123'));
        $this->assertFalse(is_logged_in());
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $this->assertFalse(login('admin', 'wrong'));
        $this->assertFalse(is_logged_in());
    }

    public function testLogoutClearsSession(): void
    {
        login('admin', 'admin123');
        $this->assertTrue(is_logged_in());
        logout();
        $this->assertFalse(is_logged_in());
        $this->assertNull(current_user());
    }

    public function testCsrfTokenIsGenerated(): void
    {
        $token = csrf_token();
        $this->assertNotEmpty($token);
        $this->assertSame(64, strlen($token)); // 32 bytes hex = 64 chars
    }

    public function testCsrfTokenIsStableAcrossCalls(): void
    {
        $token1 = csrf_token();
        $token2 = csrf_token();
        $this->assertSame($token1, $token2);
    }

    public function testCsrfVerifyReturnsTrueWithValidToken(): void
    {
        $token = csrf_token();
        $_POST['csrf_token'] = $token;
        $this->assertTrue(csrf_verify());
    }

    public function testCsrfVerifyReturnsFalseWithInvalidToken(): void
    {
        csrf_token(); // ensure token is in session
        $_POST['csrf_token'] = 'wrong-token';
        $this->assertFalse(csrf_verify());
    }

    public function testCsrfVerifyReturnsFalseWithMissingToken(): void
    {
        csrf_token(); // ensure token is in session
        $_POST = [];
        $this->assertFalse(csrf_verify());
    }
}
