<?php
require_once __DIR__ . '/../src/bootstrap.php';
use Controllers\AuthController;
use Core\Session;
use Services\RateLimiter;

if (Session::has('user_id')) {
    header('Location: dashboard.php');
    exit;
}

$controller = new AuthController();
$error = '';
$csrfToken = Session::getCsrfToken();

// Khởi tạo RateLimiter để kiểm tra xem có cần hiển thị reCAPTCHA không
$rateLimiter = new RateLimiter();
$recaptchaRequired = $rateLimiter->isRecaptchaRequired();
$recaptchaSiteKey = '';
if ($recaptchaRequired) {
    $recaptchaConfig = require __DIR__ . '/../config/recaptcha.php';
    $recaptchaSiteKey = $recaptchaConfig['site_key'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nếu là POST, gọi Controller xử lý
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    $rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] == '1';
    // AuthController sẽ tự redirect nếu thành công
    $error = $controller->handleLogin($email, $password, $csrfToken,$rememberMe);

}
require __DIR__ . '/../src/templates/auth/login.php';