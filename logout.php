<?php
session_start();

// Nếu có Remember Me token thì xóa cả trong Redis và cookie
if (!empty($_COOKIE['login_token'])) {
    $redis = new Redis();
    $redis->connect('redis', 6379);

    $token = $_COOKIE['login_token'];
    $redis->del("login_token:$token"); // xóa token trong Redis

    // Xóa cookie login_token
    setcookie('login_token', '', time() - 3600, "/");
}

// Xóa toàn bộ session
$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
