<?php
// templates/layouts/header.php

// Đảm bảo session đã được khởi động (từ bootstrap.php)
use Core\Session;

$isLoggedIn = Session::has('user_id');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
          crossorigin="anonymous">
          <link rel="icon" type="image/png" href="../public/assets/img/v.png">
    <title><?php echo $pageTitle ?? 'Hệ thống Đăng nhập'; ?></title>
    <style>
        body { padding-top: 56px; background-color: #f8f9fa; }
        .form-container { min-height: calc(100vh - 56px); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $isLoggedIn ? 'dashboard.php' : 'login.php'; ?>">
            PHP Security
        </a>
        <button class="navbar-toggler" type="button" 
                data-bs-toggle="collapse" data-bs-target="#mainNav" 
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="security-settings.php">Bảo mật (2FA)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="session-management.php">Quản lý Phiên</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Đăng xuất</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Đăng nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Đăng ký</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container"></div>