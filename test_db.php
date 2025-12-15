<?php
require_once 'includes/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    echo "❌ Kết nối THẤT BẠI: " . $conn->connect_error;
} else {
    echo "✅ Kết nối THÀNH CÔNG!<br>";
    echo "Host: " . DB_HOST . "<br>";
    
    // Thử lấy danh sách bảng để chắc chắn
    $result = $conn->query("SHOW TABLES");
    echo "Các bảng hiện có trong Database:<br>";
    while($row = $result->fetch_array()) {
        echo "- " . $row[0] . "<br>";
    }
}
?>
