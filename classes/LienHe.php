<?php
require_once 'Db.php';

class LienHe extends Db {
    /**
     * Thêm mới liên hệ vào database
     */
    public function themLienHe($hoTen, $email, $noiDung) {
        // 1. Làm sạch dữ liệu để tránh lỗi SQL
        $hoTen = $this->lamSachChuoi($hoTen);
        $email = $this->lamSachChuoi($email);
        $noiDung = $this->lamSachChuoi($noiDung);

        // 2. Tạo câu SQL
        $sql = "INSERT INTO lien_he (ho_ten, email, noi_dung) 
                VALUES ('$hoTen', '$email', '$noiDung')";

        // 3. Thực thi
        return $this->thucThi($sql);
    }
}
?>