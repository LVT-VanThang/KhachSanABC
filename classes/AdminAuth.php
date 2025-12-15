<?php
require_once 'Db.php';

class AdminAuth extends Db {
    public function dangNhap($username, $password) {
        $username = $this->lamSachChuoi($username);
        $password_md5 = md5($password);

        $sql = "SELECT * FROM admins WHERE username = '$username' AND password = '$password_md5'";
        $result = $this->layMotDong($sql);

        if ($result) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $username;
            return true;
        }
        return false;
    }

    public function kiemTraDangNhap() {
        if (!isset($_SESSION['admin_logged_in'])) {
            header("Location: login.php");
            exit;
        }
    }
}
?>