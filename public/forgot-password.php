<?php
require_once __DIR__ . '/../src/bootstrap.php';
use Controllers\PasswordResetController;
use Core\Session;
if (Session::has('user_id')) {
    header('Location: dashboard.php');
    exit;
}

$controller = new PasswordResetController();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    $message = $controller->handleForgotPassword($email, $csrfToken);
}

$csrfToken = Session::getCsrfToken();
require __DIR__ . '/../src/templates/auth/forgot-password.php';