<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Controllers\AuthController;
use Core\Session;
if (Session::has('user_id')) {
    header('Location: dashboard.php');
    exit;
}

$controller = new AuthController();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    $error = $controller->handleRegister($email, $password, $csrfToken);
}
$csrfToken = Session::getCsrfToken();
require __DIR__ . '/../src/templates/auth/register.php';