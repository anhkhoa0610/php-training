<?php
session_start();
require_once 'models/UserModel.php';
$userModel = new UserModel();

// Hàm tạo fingerprint
function getFingerprint() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    // Lấy /24 để tránh đổi IP nhỏ vẫn login được
    if (strpos($ip, '.') !== false) {
        $ip = preg_replace('/(\d+\.\d+\.\d+)\.\d+/', '$1', $ip);
    }
    return hash('sha256', $ua . '|' . $ip);
}

// Nếu đã có session rồi thì vào thẳng list
if (!empty($_SESSION['id']) && !empty($_SESSION['fp']) && hash_equals($_SESSION['fp'], getFingerprint())) {
    header("Location: list_users.php");
    exit;
}

// Nếu có remember token thì check
if (!empty($_COOKIE['login_token'])) {
    $redis = new Redis();
    $redis->connect('redis', 6379);

    $token = $_COOKIE['login_token'];
    $stored = $redis->hGetAll("login_token:$token");

    if ($stored && !empty($stored['id']) && !empty($stored['fp'])) {
        if (hash_equals($stored['fp'], getFingerprint())) {
            // Khôi phục session
            $_SESSION['id'] = $stored['id'];
            $_SESSION['fp'] = $stored['fp'];
            $_SESSION['message'] = 'Auto login by remember token';
            header("Location: list_users.php");
            exit;
        } else {
            // fingerprint sai => huỷ
            setcookie('login_token', '', time()-3600, "/");
            session_destroy();
        }
    }
}

// Xử lý login form
if (!empty($_POST['submit'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $user = $userModel->auth($username, $password);

    if ($user) {
        $uid = $user[0]['id'];
        $_SESSION['id'] = $uid;
        $_SESSION['fp'] = getFingerprint();
        $_SESSION['message'] = 'Login successful';

        // Nếu chọn Remember Me
        if (!empty($_POST['remember'])) {
            $token = bin2hex(random_bytes(32));

            $redis = new Redis();
            $redis->connect('redis', 6379);
            $redis->hMSet("login_token:$token", [
                'id' => $uid,
                'fp' => getFingerprint()
            ]);
            $redis->expire("login_token:$token", 60*60*24*30); // 30 ngày

            setcookie('login_token', $token, time() + 60*60*24*30, "/", "", true, true);
        }

        header("Location: list_users.php");
        exit;
    } else {
        $_SESSION['message'] = 'Login failed';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
<?php include 'views/header.php' ?>

<div class="container">
    <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title">Login</div>
            </div>
            <div style="padding-top:30px" class="panel-body">
                <form method="post" class="form-horizontal" role="form">
                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input type="text" class="form-control" name="username" placeholder="username or email">
                    </div>
                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input type="password" class="form-control" name="password" placeholder="password">
                    </div>
                    <div class="margin-bottom-25">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember"> Remember Me</label>
                    </div>
                    <div class="margin-bottom-25 input-group">
                        <div class="col-sm-12 controls">
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12 control">
                            Don't have an account? <a href="form_user.php">Sign Up Here</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
