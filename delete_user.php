<?php
require_once 'models/UserModel.php';
require 'auth.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// kiểm token
$sent = $_POST['csrf_token'] ?? '';
$stored = $_SESSION['csrf'] ?? '';
if (!hash_equals($stored, $sent)) {
    http_response_code(403);
    exit('Invalid CSRF token');
}

// validate id
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    header('Location: list_users.php');
    exit;
}


if (empty($_SESSION['id']) /*|| $_SESSION['role'] !== 'admin'*/) {
    http_response_code(403);
    exit('No permission');
}

$userModel = new UserModel();
$userModel->deleteUserById($id);

header('Location: list_users.php');
exit;
