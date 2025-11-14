<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Services/TwoFactorAuth.php';
require_once __DIR__ . '/../src/Controllers/TwoFactorController.php';

use Controllers\TwoFactorController;
use Core\Session;
use Models\User;
use Services\AuditLogger;
use Services\TwoFactorAuth;
use Services\Mailer;
$userId = Session::get('user_id');
if (!$userId) {
    header('Location: login.php');
    exit;
}

$userModel = new User();
$user = $userModel->findById($userId);

$controller = new TwoFactorController();
$tfaService = new TwoFactorAuth();
$mailer = new Mailer();
$error = '';
$qrCodeUrl = null;
$secret = null;
$backupCodes = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!Session::validateCsrfToken($csrfToken)) {
        $error = "Lỗi bảo mật (CSRF).";
    
    } elseif ($action === 'start_enable' && !$user['is_2fa_enabled']) {
        $secret = $tfaService->generateSecretKey();
        $appName = "MyLoginApp";
        $qrCodeUrl = $tfaService->getQRCodeUrl($appName, $user['email'], $secret);
        Session::set('2fa_setup_secret', $secret); 
    
    } elseif ($action === 'verify_enable' && !$user['is_2fa_enabled']) {
        $code = $_POST['code'] ?? '';
        $postedSecret = $_POST['secret'] ?? ''; 
        
        $result = $controller->handleEnable($userId, $code, $postedSecret, $csrfToken);
        
        if ($result['success']) {
            $backupCodes = $result['backup_codes'];
            $user['is_2fa_enabled'] = 1; 
            $logger = new AuditLogger();
            $logger->logEvent($userId, '2fa_enabled');
            $mailer->send2faStatusChangedNotification($user['email'], true);
        } else {
            $error = $result['message'];
            $secret = $postedSecret; 
            $appName = "MyLoginApp";
            $qrCodeUrl = $tfaService->getQRCodeUrl($appName, $user['email'], $secret);
        }
    } elseif ($action === 'disable' && $user['is_2fa_enabled']) {
        $password = $_POST['password'] ?? '';
        $result = $controller->handleDisable($userId, $password, $csrfToken);

        if ($result['success']) {
            $mailer->send2faStatusChangedNotification($user['email'], false);
            // Tải lại trang để cập nhật trạng thái
            header('Location: security-settings.php');
            exit;
        }
        $error = $result['message'];
    }
}
$csrfToken = Session::getCsrfToken();

require __DIR__ . '/../src/templates/user/security-settings.php';