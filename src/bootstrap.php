<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Core/DatabaseSessionHandler.php';
require_once __DIR__ . '/Core/Session.php';
require_once __DIR__ . '/Models/User.php';
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Services/Token.php';
require_once __DIR__ . '/Services/RateLimiter.php';
require_once __DIR__ . '/Services/Mailer.php';
require_once __DIR__ . '/Services/RecaptchaVerifier.php';
require_once __DIR__ . '/Models/PasswordReset.php';
require_once __DIR__ . '/Models/RememberToken.php';
require_once __DIR__ . '/Controllers/PasswordResetController.php';
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Controllers/TwoFactorController.php';
require_once __DIR__ . '/Services/TwoFactorAttemptLimiter.php';
require_once __DIR__ . '/Services/AuditLogger.php';
require_once __DIR__ . '/Models/AuditLog.php';

// Sử dụng DatabaseSessionHandler để quản lý session
$handler = new \Core\DatabaseSessionHandler();
session_set_save_handler($handler, true);

Core\Session::start();

if (!Core\Session::has('user_id') && isset($_COOKIE['remember_me'])) {
    
    $cookie = $_COOKIE['remember_me'];
    list($selector, $rawValidator) = explode(':', $cookie);

    if ($selector && $rawValidator) {
        $tokenModel = new \Models\RememberToken();
        $tokenData = $tokenModel->findValidTokenBySelector($selector);

        if ($tokenData) {
            $hashedValidator = $tokenData['validator_hash'];
            $userHashedValidator = hash('sha256', $rawValidator);
            if (hash_equals($hashedValidator, $userHashedValidator)) {
                // Đăng nhập thành công!
                $userModel = new \Models\User();
                $user = $userModel->findById($tokenData['user_id']);

                if ($user) {
                    $authController = new \Controllers\AuthController();
                    $authController->completeLogin($user['id'], $user['email']);
                    $tokenModel->deleteTokenBySelector($selector);
                }
            } else {
                $tokenModel->deleteAllTokensForUser($tokenData['user_id']);
                setcookie('remember_me', '', time() - 3600, '/');
            }
        }
    }
}