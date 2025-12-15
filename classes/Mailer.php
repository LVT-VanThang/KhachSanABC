<?php
// classes/Mailer.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/SMTP.php';

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // Cấu hình Server
        $this->mail->isSMTP();
        // Mẹo: Dùng hàm gethostbyname để ép về IPv4, tránh lỗi IPv6 timeout
        $this->mail->Host       = gethostbyname('smtp.gmail.com'); 
        $this->mail->SMTPAuth   = true;
        
        // Thông tin đăng nhập
        $this->mail->Username   = 'thangkkt112@gmail.com'; 
        $this->mail->Password   = 'biwj mgak rwch ecmp'; // Nhớ thay mật khẩu mới
        
        // --- CẤU HÌNH QUAN TRỌNG ĐỂ SỬA LỖI 110 ---
        // 1. Dùng TLS cổng 587 (Thay vì SSL 465)
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $this->mail->Port       = 587; 
        
        // 2. Bỏ qua kiểm tra chứng chỉ SSL (Giúp server kết nối nhanh hơn và không bị chặn)
        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // 3. Tăng thời gian chờ (Timeout) lên 30 giây
        $this->mail->Timeout = 30;
        
        $this->mail->CharSet    = 'UTF-8';
        $this->mail->setFrom('thangkkt112@gmail.com', 'Khách sạn ABC Luxury');
    }

    public function guiEmailThanhToan($emailKhach, $tenKhach, $data) {
        // Tăng thời gian thực thi của PHP cho tiến trình gửi mail này
        // Vì gửi mail tốn thời gian, tránh lỗi "Maximum execution time"
        set_time_limit(120); 

        try {
            $this->mail->clearAddresses(); // Xóa địa chỉ cũ nếu dùng lại object
            $this->mail->addAddress($emailKhach, $tenKhach);
            $this->mail->isHTML(true);
            $this->mail->Subject = "Thanh toán thành công - Mã đơn #" . $data['ma_don'];

            // Format tiền tệ và ngày tháng
            $tong = number_format($data['tong_tien']);
            $coc = number_format($data['tien_coc']);
            $conLai = number_format($data['tong_tien'] - $data['tien_coc']);
            $in = date('d/m/Y', strtotime($data['ngay_nhan']));
            $out = date('d/m/Y', strtotime($data['ngay_tra']));

            $body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                    <h2 style='color: #27ae60;'>Thanh toán thành công!</h2>
                    <p>Xin chào <strong>$tenKhach</strong>,</p>
                    <p>Chúng tôi xác nhận đã nhận được khoản thanh toán cọc của bạn.</p>
                    <p>Phòng của bạn đã được chuyển sang trạng thái <strong>ĐÃ ĐẶT</strong>.</p>
                    
                    <ul style='background: #f9f9f9; padding: 15px; border-radius: 5px; list-style: none;'>
                        <li><strong>Mã đơn:</strong> #{$data['ma_don']}</li>
                        <li><strong>Loại phòng:</strong> {$data['loai_phong']}</li>
                        <li><strong>Phòng được xếp:</strong> <span style='color: blue; font-weight: bold;'>{$data['so_phong']}</span></li>
                        <li><strong>Ngày nhận:</strong> $in</li>
                        <li><strong>Ngày trả:</strong> $out</li>
                        <li><strong>Tổng tiền:</strong> $tong VNĐ</li>
                        <li><strong>Đã đặt cọc:</strong> <span style='color: green;'>$coc VNĐ</span></li>
                    </ul>
                    
                    <p>Số tiền còn lại cần thanh toán tại quầy: <strong style='color: red;'>$conLai VNĐ</strong></p>
                    <p>Hẹn gặp lại quý khách!</p>
                </div>
            ";

            $this->mail->Body = $body;
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            // Log lỗi ra file log của Railway để kiểm tra
            error_log("MAILER ERROR: " . $this->mail->ErrorInfo);
            return "Lỗi Mailer: " . $this->mail->ErrorInfo;
        }
    }
}
?>
