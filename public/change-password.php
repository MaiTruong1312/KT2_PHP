<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Services/TwoFactorAuth.php';

use Core\Session;
use Models\User;
use Services\TwoFactorAuth;
use Services\AuditLogger;
use Services\Mailer;

$userId = Session::get('user_id');
if (!$userId) {
    header('Location: login.php');
    exit;
}

$userModel = new User();
$tfaService = new TwoFactorAuth();
$logger = new AuditLogger();
$mailer = new Mailer();
$user = $userModel->findById($userId);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $code = $_POST['code'] ?? '';

    if (!Session::validateCsrfToken($csrfToken)) {
        $error = "Lỗi bảo mật (CSRF).";
    } elseif (!$userModel->verifyPassword($oldPassword, $user['password_hash'])) {
        $error = "Mật khẩu hiện tại không chính xác.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Mật khẩu mới và xác nhận mật khẩu không khớp.";
    } elseif (strlen($newPassword) < 8) {
        $error = "Mật khẩu mới phải có ít nhất 8 ký tự.";
    } elseif ($user['is_2fa_enabled'] && !$tfaService->verifyCode($user['totp_secret'], $code)) {
        $error = "Mã xác thực hai yếu tố (2FA) không chính xác.";
    } else {
        if ($userModel->updatePassword($userId, $newPassword)) {
            $logger->logEvent($userId, 'password_change_success');
            $mailer->sendPasswordChangedNotification($user['email']);
            Session::set('flash_success', 'Đổi mật khẩu thành công!');
            header('Location: security-settings.php');
            exit;
        } else {
            $error = "Đã xảy ra lỗi khi cập nhật mật khẩu.";
        }
    }
}

$csrfToken = Session::getCsrfToken();
require __DIR__ . '/../src/templates/user/change-password.php';