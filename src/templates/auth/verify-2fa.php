<?php
// templates/auth/verify-2fa.php
$pageTitle = 'Xác thực 2 yếu tố';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row d-flex justify-content-center align-items-center form-container">
    <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <h2 class="card-title text-center mb-4">Xác thực 2 yếu tố</h2>
                <p class="text-center text-muted">Mở ứng dụng xác thực của bạn và nhập mã 6 số.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="verify-2fa.php" method="POST">
                    <input type="hidden" name="csrf_token" 
                           value="<?php echo htmlspecialchars($csrfToken); ?>">
                    
                    <div class="mb-3">
                        <label for="code" class="form-label">Mã 6 số:</label>
                        <input type="text" class="form-control form-control-lg text-center" 
                               id="code" name="code" required maxlength="12" 
                               pattern="[0-9]*" inputmode="numeric" 
                               autocomplete="one-time-code">
                        <div class="form-text text-center">
                            Bạn cũng có thể sử dụng mã dự phòng.
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Xác thực</button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="logout.php" class="text-muted">Hủy (Đăng xuất)</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>