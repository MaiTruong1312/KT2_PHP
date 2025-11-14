<?php
namespace Controllers;
use Core\Session;
use Models\User;
use Services\TwoFactorAuth;
use Controllers\AuthController;
use Services\TwoFactorAttemptLimiter;
use Services\AuditLogger;
use Services\Mailer;

class TwoFactorController {
    private User $userModel;
    private TwoFactorAuth $tfaService;
    private AuthController $authController;
    private AuditLogger $logger;

    public function __construct() {
        $this->userModel = new User();
        $this->tfaService = new TwoFactorAuth();
        $this->authController = new AuthController();
        $this->logger = new AuditLogger();
    }
    public function handleVerify(string $code, string $csrfToken): string {
        if (!Session::validateCsrfToken($csrfToken)) {
            return "Lỗi bảo mật (CSRF).";
        }
        $userId = Session::get('2fa_pending_user_id');
        if (!$userId) {
            header('Location: login.php');
            exit;
        }

        // Khởi tạo và kiểm tra Rate Limiter cho 2FA
        $limiter = new TwoFactorAttemptLimiter($userId);
        if ($limiter->isBlocked()) {
            // Nếu bị khóa, hủy session và bắt đăng nhập lại từ đầu
            Session::remove('2fa_pending_user_id');
            Session::set('flash_error', 'Bạn đã nhập sai mã xác thực quá nhiều lần. Vui lòng đăng nhập lại và thử lại sau ' . TwoFactorAttemptLimiter::TIME_FRAME_MINUTES . ' phút.');
            header('Location: login.php');
            exit;
        }

        $user = $this->userModel->findById($userId);
        if (!$user || !$user['is_2fa_enabled']) {
            // Lỗi lạ, hủy session tạm và bắt đăng nhập lại
            Session::remove('2fa_pending_user_id');
            header('Location: login.php');
            exit;
        }
        $secret = $user['totp_secret'];
        $isValid = $this->tfaService->verifyCode($secret, $code);

        // 5. Nếu mã TOTP sai, thử kiểm tra mã dự phòng
        if (!$isValid) {
            $isValid = $this->userModel->useBackupCode($userId, $code);
        }
        if ($isValid) {
            $this->authController->completeLogin($user['id'], $user['email']);
            Session::remove('2fa_pending_user_id');

            header("Location: dashboard.php");
            exit;
        } else {
            // Kiểm tra xem lần thử này có gây ra khóa không để gửi email
            if ($limiter->isThresholdReached()) {
                // Gửi email cảnh báo cho người dùng
                $mailer = new Mailer();
                $mailer->sendTwoFactorLockoutWarning($user['email']);
            }

            // Ghi nhận lần thử thất bại
            $limiter->logFailure();
            return "Mã xác thực không chính xác.";
        }
    }
    public function handleEnable(int $userId, string $code, string $secret, string $csrfToken): array {
        // 1. Kiểm tra CSRF
        if (!Session::validateCsrfToken($csrfToken)) {
            return ['success' => false, 'message' => 'Lỗi bảo mật (CSRF).'];
        }
        if (!$this->tfaService->verifyCode($secret, $code)) {
            return ['success' => false, 'message' => 'Mã xác thực không chính xác. Vui lòng quét lại.'];
        }
        try {
            $this->userModel->enableTwoFactor($userId, $secret);
            $backupCodes = $this->tfaService->generateBackupCodes();
            $hashedCodes = array_map(fn($c) => hash('sha256', $c), $backupCodes);
            $this->userModel->storeBackupCodes($userId, $hashedCodes);
            return ['success' => true, 'backup_codes' => $backupCodes];

        } catch (\PDOException $e) {

            return ['success' => false, 'message' => 'Lỗi cơ sở dữ liệu.'];
        }
    }

    public function handleDisable(int $userId, string $password, string $csrfToken): array {
        if (!Session::validateCsrfToken($csrfToken)) {
            return ['success' => false, 'message' => 'Lỗi bảo mật (CSRF).'];
        }

        $user = $this->userModel->findById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Không tìm thấy người dùng.'];
        }

        // Xác thực mật khẩu của người dùng
        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            $this->logger->logEvent($userId, '2fa_disable_failed_password');
            return ['success' => false, 'message' => 'Mật khẩu không chính xác.'];
        }

        // Vô hiệu hóa 2FA
        $result = $this->userModel->disable2FA($userId);
        if ($result['success']) {
            $this->logger->logEvent($userId, '2fa_disabled');
            Session::set('flash_success', 'Xác thực hai yếu tố đã được tắt thành công.');
            return ['success' => true];
        } else {
            $this->logger->logEvent($userId, '2fa_disable_failed_db', $result['message']);
            return ['success' => false, 'message' => 'Lỗi khi cập nhật cơ sở dữ liệu. Vui lòng liên hệ quản trị viên.'];
        }
    }
}
