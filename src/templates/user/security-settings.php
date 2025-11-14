<?php
// templates/user/security-settings.php
$pageTitle = 'Cài đặt bảo mật';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center my-5">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h1 class="h4">Cài đặt bảo mật</h1>
                <p class="text-muted mb-0">Quản lý xác thực hai yếu tố (2FA) cho tài khoản của bạn.</p>
            </div>
            
            <div class="card-body p-4">
                <?php
                if (Core\Session::has('flash_success')) {
                    echo '<div class="alert alert-success">' . htmlspecialchars(Core\Session::get('flash_success')) . '</div>';
                    Core\Session::remove('flash_success'); 
                }
                ?>
                
                <?php if ($user['is_2fa_enabled'] && !isset($backupCodes)): ?>
                    <h5 class="card-title">Trạng thái Xác thực hai yếu tố (2FA)</h5>
                    <p class="text-success">
                        <i class="fas fa-check-circle"></i> Đã bật
                    </p>
                    <p class="text-muted small">
                        Tài khoản của bạn được bảo vệ thêm một lớp bằng ứng dụng xác thực.
                    </p>

                    <hr>

                    <h6 class="card-subtitle mb-2 text-muted">Tắt Xác thực hai yếu tố</h6>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form action="security-settings.php" method="POST" class="mt-3">
                        <input type="hidden" name="action" value="disable">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Để tắt 2FA, vui lòng xác nhận mật khẩu của bạn:
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-danger" 
                                onclick="return confirm('Bạn có chắc chắn muốn tắt xác thực hai yếu tố không?');">
                            Tắt 2FA
                        </button>
                    </form>

                    </div>
                <?php elseif (!empty($backupCodes)): ?>
                    <div class="alert alert-warning" role="alert">
                        <h4 class="alert-heading">Kích hoạt 2FA thành công!</h4>
                        <p><strong>QUAN TRỌNG:</strong> Hãy lưu lại các mã dự phòng này ở nơi an toàn. 
                        Bạn sẽ cần chúng nếu mất quyền truy cập vào ứng dụng xác thực.</p>
                        <hr>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($backupCodes as $bcode): ?>
                                <li class="list-group-item bg-transparent">
                                    <code><?php echo htmlspecialchars($bcode); ?></code>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <hr>
                        <a href="dashboard.php" class="btn btn-success">Đã hiểu, quay về Dashboard</a>
                    </div>

                <?php elseif (isset($qrCodeUrl)): ?>
                    <h3 class="h5">Kích hoạt 2FA (Bước 1/2: Quét mã)</h3>
                    <p>Quét mã QR này bằng ứng dụng Google Authenticator (hoặc Authy).</p>
                    
                    <div class="text-center p-3 border rounded bg-light my-3">
                        <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" class="img-fluid">
                    </div>
                    
                    <p class="text-center">Hoặc nhập thủ công mã này:</p>
                    <p class="text-center"><kbd class="fs-5"><?php echo htmlspecialchars($secret); ?></kbd></p>
                    
                    <hr>
                    
                    <h3 class="h5 mt-4">Bước 2/2: Xác thực mã</h3>
                    <p>Sau khi quét, nhập mã 6 số từ ứng dụng của bạn để xác nhận:</p>
                    
                    <form action="security-settings.php" method="POST">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                    
                        <input type="hidden" name="action" value="verify_enable">
                        <input type="hidden" name="csrf_token" 
                               value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="secret" 
                               value="<?php echo htmlspecialchars($secret); ?>">
                        
                        <div class="mb-3">
                            <label for="code" class="form-label">Mã 6 số:</label>
                            <input type="text" class="form-control" id="code" name="code" 
                                   required maxlength="6" pattern="[0-9]*" inputmode="numeric" 
                                   autocomplete="one-time-code">
                        </div>
                        <button type="submit" class="btn btn-primary">Kích hoạt 2FA</button>
                    </form>

                <?php else: ?>
                    <p>Bạn chưa kích hoạt 2FA. Hãy tăng cường bảo mật cho tài khoản.</p>
                    <form action="security-settings.php" method="POST">
                        <input type="hidden" name="action" value="start_enable">
                        <input type="hidden" name="csrf_token" 
                               value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <button type="submit" class="btn btn-primary">Bắt đầu kích hoạt 2FA</button>
                    </form>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>