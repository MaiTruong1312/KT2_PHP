<?php
namespace Controllers;
use Core\Session;
use Models\User;
use Models\PasswordReset;
use Models\RememberToken;
use Services\Mailer;
use Services\Token;

class PasswordResetController {
    private User $userModel;
    private PasswordReset $resetModel;
    private Mailer $mailer;

    public function __construct() {
        $this->userModel = new User();
        $this->resetModel = new PasswordReset();
        $this->mailer = new Mailer();
    }
    public function handleForgotPassword(string $email, string $csrfToken): string {
        if (!Session::validateCsrfToken($csrfToken)) {
            return "Lỗi bảo mật (CSRF).";
        }
        
        $user = $this->userModel->findByEmail($email);
        
        if ($user) {
            [$rawToken, $hashedToken] = Token::generateToken();
            $this->resetModel->createToken($user['id'], $hashedToken, 60);
            if (!$this->mailer->sendPasswordResetLink($email, $rawToken)) {
                // Trả về lỗi nếu không gửi được mail
                return "Không thể gửi email đặt lại mật khẩu. Vui lòng thử lại sau hoặc liên hệ quản trị viên.";
            }
        }
        return "Nếu email của bạn tồn tại, chúng tôi đã gửi link reset mật khẩu.";
    }
    public function handleResetPassword(string $rawToken, string $password, string $csrfToken): string {
        if (!Session::validateCsrfToken($csrfToken)) {
            return "Lỗi bảo mật (CSRF).";
        }
        
        if (strlen($password) < 8) {
            return "Mật khẩu phải có ít nhất 8 ký tự.";
        }
        $hashedToken = hash('sha256', $rawToken);
        $tokenData = $this->resetModel->findValidToken($hashedToken);
        
        if (!$tokenData) {
            return "Token không hợp lệ, đã hết hạn, hoặc đã được sử dụng.";
        }
        $userId = $tokenData['user_id'];
        if ($this->userModel->updatePassword($userId, $password)) {
            $this->resetModel->markTokenAsUsed($tokenData['id']);
            $rememberTokenModel = new RememberToken();
            $rememberTokenModel->deleteAllTokensForUser($userId);
            Session::set('flash_success', 'Mật khẩu đã được cập nhật. Vui lòng đăng nhập lại.');
            header('Location: login.php');
            exit;
        } else {
            return "Lỗi khi cập nhật mật khẩu.";
        }
    }
}