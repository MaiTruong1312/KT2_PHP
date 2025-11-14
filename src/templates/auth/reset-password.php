<?php
// templates/auth/reset-password.php
$pageTitle = 'Đặt lại mật khẩu';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row d-flex justify-content-center align-items-center form-container">
    <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <h2 class="card-title text-center mb-4">Đặt lại mật khẩu mới</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($error) && empty($token) && $_SERVER['REQUEST_METHOD'] === 'GET'): ?>
                     <div class="alert alert-danger" role="alert">
                        Token không hợp lệ hoặc bị thiếu.
                    </div>
                <?php else: ?>
                    <form action="reset-password.php" method="POST">
                        <input type="hidden" name="csrf_token" 
                               value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="token" 
                               value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu mới (tối thiểu 8 ký tự):</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Lưu mật khẩu</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center py-3">
                <small><a href="login.php">Quay lại đăng nhập</a></small>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>