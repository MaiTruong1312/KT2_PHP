<?php
// templates/auth/login.php
$pageTitle = 'Đăng nhập';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row d-flex justify-content-center align-items-center form-container">
    <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <h2 class="card-title text-center mb-4">Đăng nhập</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php
                // Hiển thị thông báo thành công (Thành viên D)
                if (Core\Session::has('flash_success')) {
                    echo '<div class="alert alert-success" role="alert">' . 
                         htmlspecialchars(Core\Session::get('flash_success')) . 
                         '</div>';
                    Core\Session::remove('flash_success');
                }
                ?>

                <?php
                // [E] Hiển thị thông báo lỗi (ví dụ: session timeout)
                if (Core\Session::has('flash_error')) {
                    echo '<div class="alert alert-danger" role="alert">' . 
                         htmlspecialchars(Core\Session::get('flash_error')) . 
                         '</div>';
                    Core\Session::remove('flash_error');
                }
                ?>
                
                <form action="login.php" method="POST">
                    <input type="hidden" name="csrf_token" 
                           value="<?php echo htmlspecialchars($csrfToken); ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" 
                               id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu:</label>
                        <input type="password" class="form-control" 
                               id="password" name="password" required>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" 
                               value="1" id="remember_me" name="remember_me">
                        <label class="form-check-label" for="remember_me">
                            Ghi nhớ tôi
                        </label>
                    </div>

                    <?php if (isset($recaptchaRequired) && $recaptchaRequired): ?>
                        <div class="mb-3 d-flex justify-content-center">
                            <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptchaSiteKey); ?>"></div>
                        </div>
                        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                    <?php endif; ?>


                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Đăng nhập</button>
                    </div>

                    <hr class="my-4">
                    
                    <div class="text-center">
                        <a href="forgot-password.php">Quên mật khẩu?</a>
                    </div>
                    
                </form>
            </div>
            <div class="card-footer text-center py-3">
                <small>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></small>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
