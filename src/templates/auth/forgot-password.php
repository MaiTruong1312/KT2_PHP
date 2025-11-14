<?php
// templates/auth/forgot-password.php
$pageTitle = 'Quên mật khẩu';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row d-flex justify-content-center align-items-center form-container">
    <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <h2 class="card-title text-center mb-4">Quên mật khẩu</h2>
                <p class="text-center text-muted">Nhập email của bạn, chúng tôi sẽ gửi link để reset mật khẩu.</p>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php
                if (Core\Session::has('debug_reset_link')) {
                    echo '<div class="alert alert-warning small p-2"><strong class="d-block">DEBUG MODE:</strong> <a href="' . 
                         htmlspecialchars(Core\Session::get('debug_reset_link')) . 
                         '" target="_blank">Test Reset Link</a></div>';
                    Core\Session::remove('debug_reset_link');
                }
                ?>

                <form action="forgot-password.php" method="POST">
                    <input type="hidden" name="csrf_token" 
                           value="<?php echo htmlspecialchars($csrfToken); ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Gửi link reset</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center py-3">
                <small><a href="login.php">Quay lại đăng nhập</a></small>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>