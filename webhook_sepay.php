<?php
// webhook_sepay.php

// 1. NHÚNG CÁC CLASS
require_once 'classes/DatPhong.php';
require_once 'classes/Mailer.php';

// Hàm ghi log (Giữ lại để debug)
function writeLog($message) {
    file_put_contents('webhook_log.txt', "[" . date('Y-m-d H:i:s') . "] $message" . PHP_EOL, FILE_APPEND);
}

try {
    // 2. NHẬN DỮ LIỆU JSON
    $dataJson = file_get_contents('php://input');
    $data = json_decode($dataJson, true);

    if (!isset($data['id'])) die('Access Denied'); // Chặn truy cập trực tiếp

    $noiDungCk = $data['content']; 
    $soTienCk = $data['transferAmount'];
    
    writeLog("------------------------------------------------");
    writeLog("WEBHOOK IN: Content='$noiDungCk', Amount=$soTienCk");

    // 3. PHÂN TÍCH MÃ ĐƠN HÀNG
    if (preg_match('/(DH|DON)\s*(\d+)/i', $noiDungCk, $matches)) {
        $idDon = (int)$matches[2];
        writeLog("-> ID Đơn: $idDon");

        // 4. KHỞI TẠO ĐỐI TƯỢNG
        $datPhongObj = new DatPhong();
        $mailerObj = new Mailer();

        // 5. LẤY THÔNG TIN ĐƠN
        $order = $datPhongObj->layThongTinDonFull($idDon);

        if ($order && $order['trang_thai'] == 'Chờ xác nhận') {
            
            // Kiểm tra số tiền
            if ($soTienCk >= $order['tien_coc']) {
                writeLog("-> Tiền hợp lệ. Bắt đầu xếp phòng...");

                // 6. GỌI HÀM XỬ LÝ THANH TOÁN TỰ ĐỘNG
                // Hàm này sẽ tự tìm phòng, insert chi tiết và update trạng thái
                $resultXepPhong = $datPhongObj->xuLyThanhToanTuDong($idDon, $order);

                if ($resultXepPhong !== false) {
                    // --- THÀNH CÔNG: CÓ PHÒNG ---
                    $chuoiPhong = $resultXepPhong;
                    writeLog("-> SUCCESS: Đã xếp phòng: " . $chuoiPhong);

                    // 7. GỬI MAIL
                    $mailData = [
                        'ma_don' => $idDon,
                        'loai_phong' => $order['ten_loai'],
                        'so_phong' => $chuoiPhong,
                        'ngay_nhan' => $order['ngay_nhan'],
                        'ngay_tra' => $order['ngay_tra'],
                        'tong_tien' => $order['tong_tien'],
                        'tien_coc' => $order['tien_coc']
                    ];
                    
                    $mailStatus = $mailerObj->guiEmailThanhToan($order['email_khach'], $order['ten_khach'], $mailData);
                    
                    if ($mailStatus === true) writeLog("-> Mail sent.");
                    else writeLog("-> Mail failed: $mailStatus");

                    echo json_encode(["status" => "success", "message" => "Confirmed"]);

                } else {
                    // --- THẤT BẠI: HẾT PHÒNG (Overbooking) ---
                    writeLog("-> WARNING: Đã nhận tiền nhưng HẾT PHÒNG trống.");
                    // Vẫn trả về success để SePay không gửi lại webhook nữa (vì tiền đã vào rồi)
                    echo json_encode(["status" => "success", "message" => "Paid but no rooms"]);
                }

            } else {
                writeLog("-> Lỗi: Tiền chuyển ($soTienCk) nhỏ hơn cọc (" . $order['tien_coc'] . ")");
            }
        } else {
            writeLog("-> Bỏ qua: Đơn không tồn tại hoặc trạng thái không phải 'Chờ xác nhận'.");
        }
    } else {
        writeLog("-> Không tìm thấy mã đơn trong nội dung chuyển khoản.");
    }

} catch (Exception $e) {
    writeLog("Error System: " . $e->getMessage());
}
?>