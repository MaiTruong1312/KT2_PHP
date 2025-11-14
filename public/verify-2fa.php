<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php'; 
require_once __DIR__ . '/../src/Services/TwoFactorAuth.php';
require_once __DIR__ . '/../src/Controllers/TwoFactorController.php';

use Controllers\TwoFactorController;
use Core\Session;
if (!Session::has('2fa_pending_user_id')) {
    header('Location: login.php');
    exit;
}

$controller = new TwoFactorController();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    $error = $controller->handleVerify($code, $csrfToken);
}
$csrfToken = Session::getCsrfToken();
require __DIR__ . '/../src/templates/auth/verify-2fa.php';