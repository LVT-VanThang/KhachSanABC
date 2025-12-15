<?php
session_start();
// Gọi class
require_once '../classes/AdminAuth.php';

// Khởi tạo
$auth = new AdminAuth();

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: quan_ly_don.php");
    exit;
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Gọi hàm đăng nhập từ Class
    if ($auth->dangNhap($username, $password)) {
        header("Location: quan_ly_don.php");
        exit;
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập Admin</title>
    <link rel="stylesheet" href="../css/styleadmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-hotel"></i>
            <h2>Khách Sạn ABC</h2>
            <p>Hệ thống quản lý dành cho Admin</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="username" class="login-input" placeholder="Tên đăng nhập" required autocomplete="off">
            <input type="password" name="password" class="login-input" placeholder="Mật khẩu" required>
            <button type="submit" class="btn-login-submit"><i class="fas fa-sign-in-alt"></i> ĐĂNG NHẬP</button>
        </form>
        
        <div style="margin-top: 20px;">
            <a href="../khachhang/index.php" style="color: #eee; font-size: 0.9rem; text-decoration: underline;">&larr; Quay về trang chủ website</a>
        </div>
    </div>
</body>
</html>