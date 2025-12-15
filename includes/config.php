<?php
// Cấu hình lấy thông tin tự động từ hệ thống Railway
define('DB_HOST', getenv('MYSQLHOST'));
define('DB_USER', getenv('MYSQLUSER'));
define('DB_PASS', getenv('MYSQLPASSWORD'));
define('DB_NAME', getenv('MYSQLDATABASE'));
define('DB_PORT', getenv('MYSQLPORT') ?: 3306); // Dùng cổng Railway cấp, mặc định là 3306
?>
