<?php
require_once __DIR__ . '/../src/bootstrap.php';
use Controllers\AuthController;
$controller = new AuthController();
$controller->handleLogout();