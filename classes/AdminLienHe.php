<?php
require_once 'Db.php';

class AdminLienHe extends Db {
    public function layThongKe() {
        return [
            'tong' => $this->layMotDong("SELECT COUNT(*) as t FROM lien_he")['t'],
            'chua_xem' => $this->layMotDong("SELECT COUNT(*) as t FROM lien_he WHERE trang_thai = 'Chua_xem'")['t']
        ];
    }

    public function layDanhSachLH() {
        return $this->layDanhSach("SELECT * FROM lien_he ORDER BY ngay_gui DESC");
    }

    public function danhDauDaXem($id) {
        return $this->thucThi("UPDATE lien_he SET trang_thai = 'Da_xem' WHERE id = " . (int)$id);
    }

    public function xoaLienHe($id) {
        return $this->thucThi("DELETE FROM lien_he WHERE id = " . (int)$id);
    }
}
?>