<?php
require_once __DIR__ . '/../src/bootstrap.php';
use Core\Session;
use Models\AuditLog;

if (!Session::has('user_id')) {
    header('Location: login.php');
    exit;
}

$userId = Session::get('user_id');
$email = Session::get('user_email');

// Lấy 10 nhật ký hoạt động gần nhất
$logModel = new AuditLog();
$logs = $logModel->getLogsForUser($userId, 10);

// Mảng dịch các loại sự kiện để hiển thị thân thiện hơn
$eventTranslations = [
    'password_change_success'=>'Thay doi mat khau thanh cong',
    'login_success_pass1' => 'Đăng nhập thành công truoc tuong lua',
    'login_success_final' => 'Đăng nhập thành công',
    'login_failed_password' => 'Đăng nhập thất bại (sai mật khẩu)',
    'login_failed_csrf' => 'Đăng nhập thất bại (lỗi CSRF)',
    'logout' => 'Đăng xuất',
    'register_success' => 'Đăng ký tài khoản',
    '2fa_enabled' => 'Bật xác thực hai yếu tố',
    '2fa_enable_failed' => 'Bật 2FA thất bại',
    '2fa_disabled' => 'Tắt xác thực hai yếu tố',
    '2fa_disable_failed_password' => 'Tắt 2FA thất bại (sai mật khẩu)',
    '2fa_disable_failed_db' => 'Tắt 2FA thất bại (lỗi hệ thống)',
    '2fa_verify_failed' => 'Xác thực 2FA thất bại',
    '2fa_lockout_warning_sent' => 'Gửi cảnh báo khóa 2FA',
    'password_reset_request' => 'Yêu cầu đặt lại mật khẩu',
    'password_reset_success' => 'Đặt lại mật khẩu thành công',
    'session_timeout' => 'Phiên hết hạn',
    'session_revoked' => 'Thu hồi phiên đăng nhập',
    'session_revoked_all' => 'Thu hồi tất cả các phiên khác',
];


$pageTitle = 'Dashboard';
require __DIR__ . '/../src/templates/layouts/header.php';
?>

<div class="row my-5">
    <div class="col-12">
        <div class="jumbotron bg-white p-5 rounded shadow-sm border">
            <h1 class="display-5">Chào mừng bạn,</h1>
            <p class="lead"><strong><?php echo htmlspecialchars($email); ?>!</strong></p>
            <hr class="my-4">
            <p>Bạn đã đăng nhập thành công vào hệ thống bảo mật.</p>
            
            <a class="btn btn-primary" href="security-settings.php" role="button">
                Quản lý bảo mật (2FA)
            </a>
            <a class="btn btn-secondary ms-2" href="change-password.php" role="button">
                Thay đổi mật khẩu
            </a>
            
            </div>
    </div>

    <div class="col-12 mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h2 class="h5 mb-0"><i class="fas fa-history me-2"></i>Hoạt động gần đây</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Thời gian</th>
                                <th scope="col">Hành động</th>
                                <th scope="col">Địa chỉ IP</th>
                                <th scope="col">Thiết bị</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((new DateTime($log['created_at']))->format('H:i:s d/m/Y')); ?></td>
                                    <td><?php echo htmlspecialchars($eventTranslations[$log['event_type']] ?? $log['event_type']); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                    <td class="text-muted small" title="<?php echo htmlspecialchars($log['user_agent']); ?>">
                                        <?php echo htmlspecialchars(substr($log['user_agent'], 0, 50)) . (strlen($log['user_agent']) > 50 ? '...' : ''); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../src/templates/layouts/footer.php'; ?>