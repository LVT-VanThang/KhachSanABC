<?php
require_once 'Db.php';

class AdminDonHang extends Db {
    
    // Lấy thống kê dashboard
    public function layThongKe() {
        return [
            'tong_don' => $this->layMotDong("SELECT COUNT(*) as t FROM dat_phong")['t'],
            'cho_xac_nhan' => $this->layMotDong("SELECT COUNT(*) as t FROM dat_phong WHERE trang_thai = 'Chờ xác nhận'")['t'],
            'da_giu_cho' => $this->layMotDong("SELECT COUNT(*) as t FROM dat_phong WHERE trang_thai = 'Đã đặt'")['t']
        ];
    }

    // Lấy danh sách đơn (Có thể lọc trạng thái)
    public function layDanhSachDon($keyword = '', $trangThai = '') {
        $sql = "SELECT dp.*, lp.ten_loai FROM dat_phong dp 
                JOIN loai_phong lp ON dp.loai_phong_id = lp.id 
                WHERE 1=1"; // Dùng 1=1 để dễ nối chuỗi
        
        // Tìm theo Mã đơn (ID) hoặc Tên khách hàng
        if ($keyword != '') {
            $sql .= " AND (dp.id LIKE '%$keyword%' OR dp.ten_khach LIKE '%$keyword%')";
        }

        if ($trangThai != '') {
            $sql .= " AND dp.trang_thai = '$trangThai'";
        }

        $sql .= " ORDER BY dp.ngay_dat DESC";
        return $this->layDanhSach($sql);
    }
    
    // Lấy tên các phòng đã xếp cho đơn hàng
    public function layPhongCuaDon($idDon) {
        $sql = "SELECT p.so_phong FROM chi_tiet_dat_phong ct 
                JOIN phong p ON ct.phong_id = p.id WHERE ct.dat_phong_id = $idDon";
        $data = $this->layDanhSach($sql);
        return array_column($data, 'so_phong'); // Trả về mảng số phòng: ['101', '102']
    }

    // Logic duyệt đơn, hủy đơn, check-in
    public function xuLyTrangThai($id, $action) {
        $id = (int)$id;
        $order = $this->layMotDong("SELECT * FROM dat_phong WHERE id = $id");
        if (!$order) return false;

        $idLoai = $order['loai_phong_id'];
        $slCan = $order['so_luong'];
        $start = $order['ngay_nhan'];
        $end = $order['ngay_tra'];

        if ($action == 'duyet_giu_cho') {
            // ... (Giữ nguyên đoạn duyệt giữ chỗ cũ) ...
            // Code cũ của bạn: Tìm phòng trống, insert chi_tiet...
            // Copy lại đoạn logic cũ vào đây
            $phongTrong = [];
            $allRooms = $this->layDanhSach("SELECT id FROM phong WHERE loai_phong_id = $idLoai");
            foreach($allRooms as $r) {
                $pid = $r['id'];
                $cnt = $this->layMotDong("SELECT COUNT(*) as c FROM chi_tiet_dat_phong ct JOIN dat_phong dp ON ct.dat_phong_id = dp.id WHERE ct.phong_id = $pid AND dp.trang_thai IN ('Đã duyệt', 'Đang ở') AND dp.id != $id AND (dp.ngay_nhan < '$end' AND dp.ngay_tra > '$start')")['c'];
                if ($cnt == 0) $phongTrong[] = $pid;
            }

            if (count($phongTrong) < $slCan) return "Không đủ phòng trống (Còn " . count($phongTrong) . ")";

            for ($i = 0; $i < $slCan; $i++) {
                $pid = $phongTrong[$i];
                $this->thucThi("INSERT INTO chi_tiet_dat_phong (dat_phong_id, phong_id) VALUES ($id, $pid)");
                $this->thucThi("UPDATE phong SET trang_thai = 'Đã đặt' WHERE id = $pid AND trang_thai = 'Sẵn sàng'");
            }
            $this->thucThi("UPDATE dat_phong SET trang_thai = 'Đã đặt' WHERE id = $id");
            return true;

        } elseif ($action == 'check_in') {
            // ... (Giữ nguyên logic Check-in) ...
            $phongs = $this->layDanhSach("SELECT phong_id FROM chi_tiet_dat_phong WHERE dat_phong_id = $id");
            foreach($phongs as $p) $this->thucThi("UPDATE phong SET trang_thai = 'Đang ở' WHERE id = " . $p['phong_id']);
            $this->thucThi("UPDATE dat_phong SET trang_thai = 'Đang ở' WHERE id = $id");
            return true;

        } elseif ($action == 'huy') {
            // ... (Giữ nguyên logic Hủy) ...
            $phongs = $this->layDanhSach("SELECT phong_id FROM chi_tiet_dat_phong WHERE dat_phong_id = $id");
            foreach($phongs as $p) $this->thucThi("UPDATE phong SET trang_thai = 'Sẵn sàng' WHERE id = " . $p['phong_id']);
            $this->thucThi("DELETE FROM chi_tiet_dat_phong WHERE dat_phong_id = $id");
            $this->thucThi("UPDATE dat_phong SET trang_thai = 'Đã hủy' WHERE id = $id");
            return true;
        } 
        
        // ĐÃ XÓA PHẦN: elseif ($action == 'xoa') ...
        
        return false;
    }
    public function layDanhSachDangO() {
        $sql = "SELECT dp.*, lp.ten_loai FROM dat_phong dp
                JOIN loai_phong lp ON dp.loai_phong_id = lp.id
                WHERE dp.trang_thai = 'Đang ở' ORDER BY dp.ngay_nhan ASC";
        return $this->layDanhSach($sql);
    }

    // --- HÀM MỚI: Lấy chi tiết đơn để thanh toán ---
    public function layChiTietThanhToan($idDon) {
        $sql = "SELECT dp.*, lp.ten_loai, lp.gia_tien 
                FROM dat_phong dp 
                JOIN loai_phong lp ON dp.loai_phong_id = lp.id 
                WHERE dp.id = " . (int)$idDon;
        return $this->layMotDong($sql);
    }

    // --- HÀM MỚI: Xử lý trả phòng ---
    public function thucHienTraPhong($idDon, $tongTien, $ngayTra) {
        $idDon = (int)$idDon;
        
        // 1. Giải phóng phòng (Set về Sẵn sàng)
        $phongs = $this->layPhongCuaDon($idDon); // Hàm cũ đã viết trả về mảng số phòng, cần lấy ID
        // Query lấy ID phòng trực tiếp
        $res = $this->layDanhSach("SELECT phong_id FROM chi_tiet_dat_phong WHERE dat_phong_id = $idDon");
        foreach($res as $r) {
            $this->thucThi("UPDATE phong SET trang_thai = 'Sẵn sàng' WHERE id = " . $r['phong_id']);
        }

        // 2. Cập nhật đơn
        $sqlUpd = "UPDATE dat_phong SET tong_tien='$tongTien', ngay_tra_thuc_te='$ngayTra', trang_thai='Đã trả' WHERE id=$idDon";
        $this->thucThi($sqlUpd);

        // 3. Xóa chi tiết đặt phòng (để giải phóng lịch cho sơ đồ)
        $this->thucThi("DELETE FROM chi_tiet_dat_phong WHERE dat_phong_id = $idDon");
        
        return true;
    }
    public function layDuLieuSoDo($start, $end) {
    // Lấy các đơn hàng có dính líu đến khoảng ngày start -> end
    $sql = "SELECT p.id as pid, dp.ngay_nhan, dp.ngay_tra, dp.ten_khach, dp.trang_thai 
            FROM dat_phong dp
            JOIN chi_tiet_dat_phong ct ON dp.id = ct.dat_phong_id
            JOIN phong p ON ct.phong_id = p.id
            WHERE dp.trang_thai NOT IN ('Đã hủy', 'Đã trả', 'Hủy do vắng mặt') 
            AND (dp.ngay_nhan <= '$end' AND dp.ngay_tra >= '$start')";
            
    return $this->layDanhSach($sql);
}
}
?>
