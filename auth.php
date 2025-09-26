<?php
session_start();

function getFingerprint() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    // Lấy IP /24 để giảm false-positive khi user đổi IP cùng mạng
    if (strpos($ip, '.') !== false) {
        $ip = preg_replace('/(\d+\.\d+\.\d+)\.\d+/', '$1', $ip);
    }
    return hash('sha256', $ua . '|' . $ip);
}

// Nếu chưa có id trong session => chưa login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Nếu có login_token (remember me), check ở đây
if (!empty($_COOKIE['login_token'])) {
    $redis = new Redis();
    $redis->connect('redis', 6379);

    $token = $_COOKIE['login_token'];
    $stored = $redis->hGetAll("login_token:$token");

    if ($stored && !empty($stored['id']) && !empty($stored['fp'])) {
        // So fingerprint
        if (hash_equals($stored['fp'], getFingerprint())) {
            $_SESSION['id'] = $stored['id'];
        } else {
            // fingerprint không khớp => huỷ session và xoá cookie
            setcookie('login_token', '', time()-3600, "/");
            session_destroy();
            header("Location: login.php?reason=fingerprint_mismatch");
            exit();
        }
    } else {
        header("Location: login.php");
        exit();
    }
}

// Kiểm tra fingerprint ngay cả với session thường
if (!empty($_SESSION['fp']) && !hash_equals($_SESSION['fp'], getFingerprint())) {
    setcookie('login_token', '', time()-3600, "/");
    session_destroy();
    header("Location: login.php?reason=fingerprint_mismatch");
    exit();
}
