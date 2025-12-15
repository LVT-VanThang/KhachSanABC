<?php
// Cấu hình lấy thông tin tự động từ tab Variables của Railway
define('DB_HOST', getenv('MYSQLHOST'));     // Sẽ lấy giá trị mysql.railway.internal
define('DB_USER', getenv('MYSQLUSER'));     // Sẽ lấy giá trị root
define('DB_PASS', getenv('MYSQLPASSWORD')); // Sẽ lấy giá trị EnDGToMs...
define('DB_NAME', getenv('MYSQLDATABASE')); // Sẽ lấy giá trị railway
define('DB_PORT', getenv('MYSQLPORT'));     // Sẽ lấy giá trị 3306
?>
