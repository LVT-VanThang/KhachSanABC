<?php
require_once 'Db.php';

class LoaiPhong extends Db {

    // Hàm tìm kiếm đầy đủ các tiêu chí
    public function timKiemPhong($filter = []) {
        $sql = "SELECT * FROM loai_phong WHERE 1=1";

        // Lọc theo sức chứa
        if (!empty($filter['suc_chua'])) {
            $sql .= " AND suc_chua >= " . (int)$filter['suc_chua'];
        }

        // Lọc theo số giường (Mới thêm)
        if (!empty($filter['so_giuong'])) {
            $sql .= " AND so_giuong >= " . (int)$filter['so_giuong'];
        }

        // Lọc theo view (Mới thêm)
        if (!empty($filter['view'])) {
            $view = $this->lamSachChuoi($filter['view']);
            $sql .= " AND huong_nhin LIKE '%$view%'";
        }
        
        // Lọc theo giá
        if (!empty($filter['muc_gia']) && $filter['muc_gia'] != 'all') {
            if ($filter['muc_gia'] == 'duoi-2tr') $sql .= " AND gia_tien < 2000000";
            elseif ($filter['muc_gia'] == '2tr-3tr') $sql .= " AND gia_tien BETWEEN 2000000 AND 3000000";
            elseif ($filter['muc_gia'] == 'tren-3tr') $sql .= " AND gia_tien > 3000000";
        }

        return $this->layDanhSach($sql);
    }

    public function layChiTiet($id) {
        $id = (int)$id;
        $sql = "SELECT * FROM loai_phong WHERE id = $id";
        return $this->layMotDong($sql);
    }

    public function demPhongTrong($loaiPhongId, $ngayNhan, $ngayTra) {
        if (empty($ngayNhan) || empty($ngayTra)) return 0; // Trả về 0 nếu chưa chọn ngày (để logic hiển thị xử lý)

        $dkBaoTri = (strtotime($ngayNhan) <= time()) ? "AND trang_thai != 'Bảo trì'" : "";
        
        $sqlTong = "SELECT COUNT(*) as total FROM phong WHERE loai_phong_id = $loaiPhongId $dkBaoTri";
        $tongPhong = $this->layMotDong($sqlTong)['total'];

        $sqlDaDat = "SELECT COUNT(DISTINCT ct.phong_id) as booked 
                     FROM chi_tiet_dat_phong ct
                     JOIN dat_phong dp ON ct.dat_phong_id = dp.id
                     WHERE ct.phong_id IN (SELECT id FROM phong WHERE loai_phong_id = $loaiPhongId)
                     AND dp.trang_thai IN ('Đã duyệt', 'Đang ở', 'Đã đặt')
                     AND (dp.ngay_nhan < '$ngayTra' AND dp.ngay_tra > '$ngayNhan')";
        
        $phongDaDat = $this->layMotDong($sqlDaDat)['booked'];

        return $tongPhong - $phongDaDat;
    }
}
?>