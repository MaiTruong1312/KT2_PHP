<?php
// src/Core/Session.php
namespace Core;

class Session {
    const SESSION_TIMEOUT_MINUTES = 30;
    
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.cookie_secure', 0); // đổi thành 1 nếu dùng HTTPS
            
            session_start();
        }

        // ⚙️ Kiểm tra session timeout (đặt bên trong hàm start)
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity'] > (self::SESSION_TIMEOUT_MINUTES * 60))) 
        {
            // Nếu hết hạn, hủy session và báo lỗi
            $userId = self::get('user_id');
            
            self::destroy(); // Hủy session
            session_start(); // Bắt đầu session mới để lưu flash message
            
            self::set('flash_error', 'Phiên của bạn đã hết hạn do không hoạt động. Vui lòng đăng nhập lại.');
            
            // Ghi log (chỉ khi biết user là ai)
            if ($userId) {
                $loggerPath = __DIR__ . '/../Services/AuditLogger.php';
                if (file_exists($loggerPath)) {
                    require_once $loggerPath;
                    $logger = new \Services\AuditLogger();
                    $logger->logEvent($userId, 'session_timeout');
                }
            }
            
            header('Location: login.php');
            exit;
        }
        
        // Cập nhật thời gian hoạt động cuối
        $_SESSION['last_activity'] = time();
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key): bool {
        return isset($_SESSION[$key]);
    }

    public static function remove($key) {
        unset($_SESSION[$key]);
    }

    public static function destroy() {
        $_SESSION = []; 
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Tái tạo Session ID để chống Session Fixation
     */
    public static function regenerate() {
        session_regenerate_id(true);
    }

    // --- Chức năng CSRF ---
    public static function getCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrfToken($token): bool {
        if (empty($token) || !isset($_SESSION['csrf_token'])) {
            return false;
        }
        // Dùng hash_equals để chống tấn công Timing Attack
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
