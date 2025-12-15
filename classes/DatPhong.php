<?php
require_once 'Db.php';

class DatPhong extends Db {

    public function taoDonDatPhong($data) {
        // Làm sạch dữ liệu đầu vào
        $loaiId = (int)$data['loai_id'];
        $soLuong = (int)$data['so_luong'];
        $ten = $this->lamSachChuoi($data['ten']);
        $email = $this->lamSachChuoi($data['email']);
        $sdt = $this->lamSachChuoi($data['sdt']);
        $ngayNhan = $data['ngay_nhan'];
        $ngayTra = $data['ngay_tra'];
        $tongTien = $data['tong_tien'];
        $tienCoc = $data['tien_coc'];

        $sql = "INSERT INTO dat_phong (loai_phong_id, so_luong, ten_khach, email_khach, sdt_khach, ngay_nhan, ngay_tra, tong_tien, tien_coc, trang_thai) 
                VALUES ('$loaiId', '$soLuong', '$ten', '$email', '$sdt', '$ngayNhan', '$ngayTra', '$tongTien', '$tienCoc', 'Chờ xác nhận')";
        
        if ($this->thucThi($sql)) {
            return $this->layIdVuaTao(); // Trả về ID đơn hàng
        }
        return false;
    }

    public function kiemTraTrangThaiDon($id) {
        $id = (int)$id;
        $sql = "SELECT trang_thai, ngay_nhan, ngay_tra FROM dat_phong WHERE id = $id";
        return $this->layMotDong($sql);
    }
    
    // Hàm lấy danh sách tên phòng đã xếp cho đơn hàng (Dùng cho check_status)
    public function layTenPhongDaXep($datPhongId) {
        $datPhongId = (int)$datPhongId;
        $sql = "SELECT p.so_phong 
                FROM chi_tiet_dat_phong ct 
                JOIN phong p ON ct.phong_id = p.id 
                WHERE ct.dat_phong_id = $datPhongId
                ORDER BY p.so_phong ASC";
        
        $danhSach = $this->layDanhSach($sql);
        $ketQua = [];
        foreach($danhSach as $item) {
            $ketQua[] = "P." . $item['so_phong'];
        }
        return implode(', ', $ketQua);
    }
    public function layThongTinDonFull($id) {
        $id = (int)$id;
        $sql = "SELECT d.*, l.ten_loai 
                FROM dat_phong d 
                JOIN loai_phong l ON d.loai_phong_id = l.id
                WHERE d.id = $id";
        return $this->layMotDong($sql);
    }

    /**
     * Tự động tìm phòng trống và xếp cho đơn hàng
     * Trả về: Mảng danh sách tên phòng đã xếp (hoặc false nếu thất bại)
     */
    public function xuLyThanhToanTuDong($idDon, $orderData) {
        $idDon = (int)$idDon;
        $idLoai = (int)$orderData['loai_phong_id'];
        $slCan = (int)$orderData['so_luong'];
        $checkIn = $orderData['ngay_nhan'];
        $checkOut = $orderData['ngay_tra'];

        // 1. Tìm phòng trống (Logic SQL phức tạp từ file cũ)
        $sqlTim = "SELECT p.id, p.so_phong 
                   FROM phong p
                   WHERE p.loai_phong_id = $idLoai
                   AND p.trang_thai != 'Bảo trì' 
                   AND p.id NOT IN (
                        SELECT ct.phong_id 
                        FROM chi_tiet_dat_phong ct
                        JOIN dat_phong dp ON ct.dat_phong_id = dp.id
                        WHERE dp.trang_thai IN ('Đã đặt', 'Đã duyệt', 'Đang ở') 
                        AND dp.id != $idDon 
                        AND (dp.ngay_nhan < '$checkOut' AND dp.ngay_tra > '$checkIn')
                   )
                   LIMIT $slCan";

        $phongTrong = $this->layDanhSach($sqlTim);

        // 2. Kiểm tra đủ phòng không
        if (count($phongTrong) >= $slCan) {
            $dsTenPhong = [];
            foreach ($phongTrong as $p) {
                // Insert chi tiết
                $this->thucThi("INSERT INTO chi_tiet_dat_phong (dat_phong_id, phong_id) VALUES ($idDon, {$p['id']})");
                // Update trạng thái phòng (nếu cần thiết, thường thì dựa vào date range là đủ, nhưng update cho chắc)
                // Lưu ý: Logic cũ có update trạng thái phòng, ta giữ nguyên
                $this->thucThi("UPDATE phong SET trang_thai = 'Đã đặt' WHERE id = {$p['id']} AND trang_thai = 'Sẵn sàng'");
                
                $dsTenPhong[] = "P." . $p['so_phong'];
            }

            // 3. Update trạng thái đơn hàng
            $this->thucThi("UPDATE dat_phong SET trang_thai = 'Đã đặt' WHERE id = $idDon");
            
            return implode(', ', $dsTenPhong);
        } else {
            // Trường hợp: Đã nhận tiền nhưng hết phòng (Overbooking)
            // Vẫn update trạng thái để Admin xử lý thủ công
            $this->thucThi("UPDATE dat_phong SET trang_thai = 'Đã đặt' WHERE id = $idDon");
            return false; // Trả về false để báo hiệu hết phòng
        }
    }
}
?>