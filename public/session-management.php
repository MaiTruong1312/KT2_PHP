<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Core\Session;
use Core\DatabaseSessionHandler;

$userId = Session::get('user_id');
if (!$userId) {
    header('Location: login.php');
    exit;
}

$handler = new DatabaseSessionHandler();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!Session::validateCsrfToken($csrfToken)) {
        $error = "Lỗi bảo mật (CSRF).";
    } else {
        if ($action === 'revoke' && isset($_POST['session_id'])) {
            $sessionIdToRevoke = $_POST['session_id'];
            if ($handler->revokeSession($sessionIdToRevoke, $userId)) {
                Session::set('flash_success', 'Đã thu hồi phiên thành công.');
            }
        } elseif ($action === 'revoke_all') {
            $handler->revokeAllOtherSessions(session_id(), $userId);
            // Cũng nên xóa tất cả token "remember me"
            (new \Models\RememberToken())->deleteAllTokensForUser($userId);
            Session::set('flash_success', 'Đã thu hồi tất cả các phiên khác thành công.');
        }
        header('Location: session-management.php');
        exit;
    }
}

$sessions = $handler->getSessionsForUser($userId);
$currentSessionId = session_id();
$csrfToken = Session::getCsrfToken();

require __DIR__ . '/../src/templates/user/session-management.php';