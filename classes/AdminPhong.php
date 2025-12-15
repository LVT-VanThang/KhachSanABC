<?php
require_once 'Db.php';

class AdminPhong extends Db {
    
    // --- QUẢN LÝ LOẠI PHÒNG ---
    public function layDanhSachLoaiPhong() {
        $sql = "SELECT lp.*, 
                (SELECT COUNT(*) FROM phong p WHERE p.loai_phong_id = lp.id) as tong, 
                (SELECT COUNT(*) FROM phong p WHERE p.loai_phong_id = lp.id AND p.trang_thai='Đang ở') as dang_o 
                FROM loai_phong lp";
        return $this->layDanhSach($sql);
    }

    public function layChiTietLoai($id) {
        return $this->layMotDong("SELECT * FROM loai_phong WHERE id = " . (int)$id);
    }

    public function xoaLoaiPhong($id) {
        $id = (int)$id;
        // Kiểm tra ràng buộc
        $busy = $this->layMotDong("SELECT COUNT(*) as c FROM phong WHERE loai_phong_id=$id AND trang_thai='Đang ở'")['c'];
        $hasRoom = $this->layMotDong("SELECT COUNT(*) as c FROM phong WHERE loai_phong_id=$id")['c'];

        if ($busy > 0) return "Loại phòng này đang có khách ở!";
        if ($hasRoom > 0) return "Vui lòng xóa hết các phòng thuộc loại này trước!";
        
        return $this->thucThi("DELETE FROM loai_phong WHERE id=$id") ? true : "Lỗi hệ thống";
    }

    public function luuLoaiPhong($data, $fileData = null) {
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $ten = $data['ten_loai']; $gia = $data['gia_tien']; $suc = $data['suc_chua'];
        $giuong = $data['so_giuong']; $view = $data['huong_nhin']; $desc = $data['mo_ta'];
        
        // Logic BLOB ảnh (giữ nguyên logic cũ của bạn)
        if ($id > 0) {
            $sql = "UPDATE loai_phong SET ten_loai=?, gia_tien=?, suc_chua=?, so_giuong=?, huong_nhin=?, mo_ta=?" . ($fileData ? ", anh_dai_dien=?" : "") . " WHERE id=?";
            $stmt = $this->ketNoi->prepare($sql);
            if ($fileData) {
                $null = null; // Placeholder for binding blob
                $stmt->bind_param("siiissbi", $ten, $gia, $suc, $giuong, $view, $desc, $null, $id);
                $stmt->send_long_data(6, $fileData);
            } else {
                $stmt->bind_param("siiissi", $ten, $gia, $suc, $giuong, $view, $desc, $id);
            }
        } else {
            $sql = "INSERT INTO loai_phong (ten_loai, gia_tien, suc_chua, so_giuong, huong_nhin, mo_ta" . ($fileData ? ", anh_dai_dien" : "") . ") VALUES (?,?,?,?,?,?" . ($fileData ? ",?" : "") . ")";
            $stmt = $this->ketNoi->prepare($sql);
            if ($fileData) {
                $null = null;
                $stmt->bind_param("siiissb", $ten, $gia, $suc, $giuong, $view, $desc, $null);
                $stmt->send_long_data(6, $fileData);
            } else {
                $stmt->bind_param("siiiss", $ten, $gia, $suc, $giuong, $view, $desc);
            }
        }
        return $stmt->execute();
    }

    // --- QUẢN LÝ SỐ PHÒNG (Room Number) ---
    public function layDanhSachSoPhong($keyword = '', $loaiId = 0, $tang = 0) {
        $keyword = $this->lamSachChuoi($keyword);
        $loaiId = (int)$loaiId;
        $tang = (int)$tang;

        // Câu lệnh gốc
        $sql = "SELECT p.id, p.so_phong, p.tang, p.trang_thai, lp.ten_loai 
                FROM phong p JOIN loai_phong lp ON p.loai_phong_id = lp.id 
                WHERE 1=1"; // Mẹo WHERE 1=1 để dễ nối chuỗi AND

        // Thêm điều kiện nếu có dữ liệu tìm kiếm
        if (!empty($keyword)) {
            $sql .= " AND p.so_phong LIKE '%$keyword%'";
        }

        if ($loaiId > 0) {
            $sql .= " AND p.loai_phong_id = $loaiId";
        }

        if ($tang > 0) {
            $sql .= " AND p.tang = $tang";
        }

        $sql .= " ORDER BY p.tang ASC, p.so_phong ASC";

        return $this->layDanhSach($sql);
    }
    public function layDanhSachTang() {
        // Lấy các tầng duy nhất, sắp xếp tăng dần
        return $this->layDanhSach("SELECT DISTINCT tang FROM phong ORDER BY tang ASC");
    }

    public function xoaSoPhong($id) {
        $id = (int)$id;
        $check = $this->layMotDong("SELECT trang_thai, so_phong FROM phong WHERE id = $id");
        if ($check && $check['trang_thai'] == 'Đang ở') return "Phòng đang có khách, không thể xóa!";
        
        try {
            return $this->thucThi("DELETE FROM phong WHERE id = $id") ? true : "Lỗi xóa";
        } catch (Exception $e) { return "Không thể xóa do ràng buộc dữ liệu cũ."; }
    }
    public function layChiTietSoPhong($id) {
        return $this->layMotDong("SELECT * FROM phong WHERE id = " . (int)$id);
    }

    // --- HÀM MỚI: Thêm hoặc Cập nhật Số phòng ---
    public function luuSoPhong($data) {
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $so = $this->lamSachChuoi($data['so_phong']);
        $loai = (int)$data['loai_phong_id'];
        $tang = (int)$data['tang'];
        $tt = $data['trang_thai'];

        // Kiểm tra trùng số phòng
        $sqlCheck = "SELECT COUNT(*) as c FROM phong WHERE so_phong='$so' AND id != $id";
        if ($this->layMotDong($sqlCheck)['c'] > 0) return "Số phòng đã tồn tại!";

        if ($id > 0) {
            $sql = "UPDATE phong SET so_phong='$so', loai_phong_id='$loai', tang='$tang', trang_thai='$tt' WHERE id=$id";
        } else {
            $sql = "INSERT INTO phong (so_phong, loai_phong_id, tang, trang_thai) VALUES ('$so', '$loai', '$tang', '$tt')";
        }

        return $this->thucThi($sql) ? true : "Lỗi SQL";
    }

    // --- HÀM MỚI: Hàm xử lý Loai Phong đầy đủ (cho them_sua_phong.php) ---
    public function luuLoaiPhongFull($data, $files) {
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $ten = $data['ten_loai']; $gia = $data['gia_tien']; $suc = $data['suc_chua'];
        $giuong = $data['so_giuong']; $view = $data['huong_nhin']; $desc = $data['mo_ta'];
        
        $fileData = null;
        if (!empty($files['anh_dai_dien']['tmp_name'])) {
            $fileData = file_get_contents($files['anh_dai_dien']['tmp_name']);
        }

        if ($id > 0) {
            $sql = "UPDATE loai_phong SET ten_loai=?, gia_tien=?, suc_chua=?, so_giuong=?, huong_nhin=?, mo_ta=?" . ($fileData ? ", anh_dai_dien=?" : "") . " WHERE id=?";
            $stmt = $this->ketNoi->prepare($sql);
            if ($fileData) {
                $null = null;
                $stmt->bind_param("siiissbi", $ten, $gia, $suc, $giuong, $view, $desc, $null, $id);
                $stmt->send_long_data(6, $fileData);
            } else {
                $stmt->bind_param("siiissi", $ten, $gia, $suc, $giuong, $view, $desc, $id);
            }
        } else {
            $sql = "INSERT INTO loai_phong (ten_loai, gia_tien, suc_chua, so_giuong, huong_nhin, mo_ta" . ($fileData ? ", anh_dai_dien" : "") . ") VALUES (?,?,?,?,?,?" . ($fileData ? ",?" : "") . ")";
            $stmt = $this->ketNoi->prepare($sql);
            if ($fileData) {
                $null = null;
                $stmt->bind_param("siiissb", $ten, $gia, $suc, $giuong, $view, $desc, $null);
                $stmt->send_long_data(6, $fileData);
            } else {
                $stmt->bind_param("siiiss", $ten, $gia, $suc, $giuong, $view, $desc);
            }
        }
        return $stmt->execute();
    }
    public function layDanhSachPhongTimeline($keyword = '', $loaiId = 0) {
    $keyword = $this->lamSachChuoi($keyword);
    $loaiId = (int)$loaiId;

    $sql = "SELECT p.id, p.so_phong, p.trang_thai, lp.ten_loai 
            FROM phong p 
            JOIN loai_phong lp ON p.loai_phong_id = lp.id 
            WHERE 1=1";
    
    if ($keyword) $sql .= " AND p.so_phong LIKE '%$keyword%'";
    if ($loaiId) $sql .= " AND p.loai_phong_id = $loaiId";
    
    $sql .= " ORDER BY lp.ten_loai ASC, p.so_phong ASC";
    
    return $this->layDanhSach($sql);
}
}
?>
