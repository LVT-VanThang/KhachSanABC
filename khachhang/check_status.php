<?php
// File: khachhang/check_status.php
require_once '../classes/DatPhong.php';

error_reporting(0); 
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Khởi tạo Class
    $datPhongObj = new DatPhong();
    
    // 1. Lấy trạng thái đơn
    $donHang = $datPhongObj->kiemTraTrangThaiDon($id);
    
    if ($donHang) {
        $trangThai = trim($donHang['trang_thai']);
        
        // Kiểm tra trạng thái thành công
        if (in_array($trangThai, ['Đã duyệt', 'Đã đặt', 'Đang ở'])) {
            
            // 2. Lấy danh sách phòng đã xếp (Gọi hàm từ Class)
            $tenPhong = $datPhongObj->layTenPhongDaXep($id);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Thanh toán thành công!',
                'phong' => $tenPhong ? $tenPhong : 'Đang cập nhật...',
                'checkin' => date('d/m/Y', strtotime($donHang['ngay_nhan'])),
                'checkout' => date('d/m/Y', strtotime($donHang['ngay_tra']))
            ]);
        } else {
            echo json_encode(['status' => 'waiting']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy đơn hàng']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu ID']);
}
?>