<?php
namespace Controllers;
use Core\Session;
use Models\User;
use Models\RememberToken; 
use Services\RateLimiter; 
use Services\Token;
use Services\AuditLogger;
use Services\RecaptchaVerifier;

class AuthController {
    private User $userModel;
    private RateLimiter $rateLimiter;
    private RememberToken $rememberTokenModel;
    private AuditLogger $logger;

    public function __construct() {
        $this->userModel = new User();
        $this->rateLimiter = new RateLimiter();
        $this->rememberTokenModel = new RememberToken();
        $this->logger = new AuditLogger();
    }

    public function handleRegister(string $email, string $password, string $csrfToken): string {
        if (!Session::validateCsrfToken($csrfToken)) {
            return "Lỗi bảo mật (CSRF token không hợp lệ).";
        }

        if (empty($email) || empty($password) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Email không hợp lệ hoặc mật khẩu bị trống.";
        }

        if (strlen($password) < 8) {
            return "Mật khẩu phải có ít nhất 8 ký tự.";
        }

        try {
            if ($this->userModel->create($email, $password)) {
                $user = $this->userModel->findByEmail($email);
                $this->completeLogin($user['id'], $user['email']);
                $this->logger->logEvent($user['id'], 'register_success');
                header("Location: dashboard.php");
                exit;
            }
            return "Email này đã được sử dụng.";
        } catch (\PDOException $e) {
            return "Lỗi cơ sở dữ liệu.";
        }
    }
    public function handleLogin(string $email, string $password, string $csrfToken, bool $rememberMe = false): string {
        if ($this->rateLimiter->isIpBlocked()) {
            return "Bạn đã thử quá nhiều lần. Vui lòng thử lại sau 15 phút.";
        }

        if (!Session::validateCsrfToken($csrfToken)) {
            $this->logger->logEvent(null, 'login_failed_csrf', $email);
            return "Lỗi bảo mật (CSRF token không hợp lệ).";
        }

        // Kiểm tra reCAPTCHA nếu cần
        if ($this->rateLimiter->isRecaptchaRequired()) {
            $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
            $recaptchaVerifier = new RecaptchaVerifier();
            if (!$recaptchaVerifier->verify($recaptchaResponse)) {
                $this->logger->logEvent(null, 'login_failed_recaptcha', $email);
                return "Vui lòng xác thực bạn không phải là robot.";
            }
        }

        if (empty($email) || empty($password)) {
            return "Vui lòng nhập email và mật khẩu.";
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
            $this->rateLimiter->logAttempt($email, false);
            $this->logger->logEvent(null, 'login_failed_password', $email);
            return "Email hoặc mật khẩu không chính xác.";
        }

        $this->rateLimiter->logAttempt($email, true);

        // Kiểm tra 2FA
        if ($user['is_2fa_enabled'] == 1) {
            Session::set('2fa_pending_user_id', $user['id']);
            if ($rememberMe) {
                Session::set('2fa_pending_remember_me', true);
            }
            $this->logger->logEvent($user['id'], 'login_success_pass1');
            header("Location: verify-2fa.php");
            exit;
        }
        $this->completeLogin($user['id'], $user['email']);

        if ($rememberMe) {
            $this->setRememberMeCookie($user['id']);
        }

        header("Location: dashboard.php");
        exit;
    }
    public function completeLogin(int $userId, string $email) {
        Session::regenerate(); 
        Session::set('user_id', $userId);
        Session::set('user_email', $email);
        $this->userModel->updateLastLogin($userId);
        $this->logger->logEvent($userId, 'login_success_final');

        if (Session::has('2fa_pending_remember_me')) {
            $this->setRememberMeCookie($userId);
            Session::remove('2fa_pending_remember_me');
        }
    }
    public function handleLogout() {
        $userId = Session::get('user_id');
        $this->clearRememberMeCookie();
        Session::destroy();

        if ($userId) {
            $this->logger->logEvent($userId, 'logout');
        }

        header("Location: login.php");
        exit;
    }
    private function setRememberMeCookie(int $userId) {
        [$selector, $rawValidator, $hashedValidator] = Token::generateRememberMeToken();
        
        if ($this->rememberTokenModel->storeToken($userId, $selector, $hashedValidator, 30)) {
            $cookieValue = $selector . ':' . $rawValidator;
            setcookie(
                'remember_me',
                $cookieValue,
                time() + (86400 * 30), 
                '/',
                '',
                false, 
                true  
            );
        }
    }

    private function clearRememberMeCookie() {
        if (isset($_COOKIE['remember_me'])) {
            $selector = explode(':', $_COOKIE['remember_me'])[0];
            $this->rememberTokenModel->deleteTokenBySelector($selector);
            setcookie('remember_me', '', time() - 3600, '/');
        }
    }
}
