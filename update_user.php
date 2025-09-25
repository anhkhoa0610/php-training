<?php

require 'auth.php';
require_once 'models/UserModel.php';
$userModel = new UserModel();

if (!empty($_POST['submit'])) {

    $sent = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf'] ?? '';

    // Kiểm tra token
    if (!hash_equals($stored, $sent)) {
        http_response_code(403);
        echo 'Invalid CSRF token';
        exit;
    }

    if (!empty($_POST['id'])) {
        $userModel->updateUser($_POST);
    } else {
        $userModel->insertUser($_POST);
    }
    header('location: list_users.php');
}