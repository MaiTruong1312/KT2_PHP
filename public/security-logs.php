<?php
require_once __DIR__ . '/../src/bootstrap.php';
use Core\Session;
use Models\AuditLog;
$userId = Session::get('user_id');
if (!$userId) {
    header('Location: login.php');
    exit;
}

$logModel = new AuditLog();
$logs = $logModel->getLogsForUser($userId, 20); // Lấy 20 log gần nhất
require __DIR__ . '/../templates/user/security-logs.php';