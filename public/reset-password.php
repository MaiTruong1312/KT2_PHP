<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Controllers\PasswordResetController;
use Core\Session;
if (Session::has('user_id')) {
    header('Location: dashboard.php');
    exit;
}

$controller = new PasswordResetController();
$error = '';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    $token = $_POST['token'] ?? '';
    $error = $controller->handleResetPassword($token, $password, $csrfToken);
}
if (empty($token)) {

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $error = "Token không hợp lệ hoặc bị thiếu.";
    }
}

$csrfToken = Session::getCsrfToken();
require __DIR__ . '/../src/templates/auth/reset-password.php';