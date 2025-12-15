<?php
require_once __DIR__ . '/../includes/config.php';

class Db {
    protected $ketNoi;

    public function __construct() {
       $this->ketNoi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($this->ketNoi->connect_error) {
            die("Kết nối thất bại: " . $this->ketNoi->connect_error);
        }
        $this->ketNoi->set_charset("utf8");
    }

    // Hàm thực thi câu lệnh (INSERT, UPDATE, DELETE) trả về true/false
    public function thucThi($sql) {
        return $this->ketNoi->query($sql);
    }

    // Hàm lấy danh sách nhiều dòng (SELECT trả về mảng)
    public function layDanhSach($sql) {
        $result = $this->ketNoi->query($sql);
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    // Hàm lấy 1 dòng duy nhất (SELECT 1 dòng)
    public function layMotDong($sql) {
        $result = $this->ketNoi->query($sql);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    // Hàm làm sạch chuỗi để chống SQL Injection
    public function lamSachChuoi($str) {
        return $this->ketNoi->real_escape_string($str);
    }
    
    // Lấy ID vừa insert
    public function layIdVuaTao() {
        return $this->ketNoi->insert_id;
    }
}
?>
