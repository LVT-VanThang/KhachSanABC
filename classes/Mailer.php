<?php
// classes/Mailer.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; // Thêm dòng này để dùng hằng số SMTP

require_once __DIR__ . '/../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/SMTP.php';

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // --- BẬT CHẾ ĐỘ DEBUG ĐỂ XEM LỖI CHI TIẾT TRÊN RAILWAY LOGS ---
        // 0 = Tắt, 2 = Hiện Client/Server message
        $this->mail->SMTPDebug = 2; 
        $this->mail->Debugoutput = 'error_log'; // Ghi lỗi vào Logs thay vì hiện ra màn hình

        // Cấu hình Server
        $this->mail->isSMTP();
        // Dùng server dự phòng của Google để tránh timeout
        $this->mail->Host       = 'smtp.googlemail.com'; 
        $this->mail->SMTPAuth   = true;
        
        // Thông tin đăng nhập
        $this->mail->Username   = 'thangkkt112@gmail.com'; 
        $this->mail->Password   = 'biwj mgak rwch ecmp'; // Mật khẩu ứng dụng của bạn
        
        // Cấu hình mã hóa & Cổng
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $this->mail->Port       = 587; 
        
        // Bỏ qua kiểm tra chứng chỉ SSL (Fix lỗi kết nối trên Cloud)
        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Tăng thời gian chờ lên 60 giây cho chắc chắn
        $this->mail->Timeout = 60;
        
        $this->mail->CharSet    = 'UTF-8';
        $this->mail->setFrom('thangkkt112@gmail.com', 'Khách sạn ABC Luxury');
    }

    public function guiEmailThanhToan($emailKhach, $tenKhach, $data) {
        set_time_limit(120); 

        try {
            $this->mail->clearAddresses();
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
                    <ul style='background: #f9f9f9; padding: 15px; border-radius: 5px; list-style: none;'>
                        <li><strong>Mã đơn:</strong> #{$data['ma_don']}</li>
                        <li><strong>Loại phòng:</strong> {$data['loai_phong']}</li>
                        <li><strong>Phòng:</strong> <span style='color: blue; font-weight: bold;'>{$data['so_phong']}</span></li>
                        <li><strong>Ngày nhận:</strong> $in - <strong>Ngày trả:</strong> $out</li>
                        <li><strong>Tổng tiền:</strong> $tong VNĐ</li>
                        <li><strong>Đã cọc:</strong> <span style='color: green;'>$coc VNĐ</span></li>
                    </ul>
                    <p>Số tiền còn lại: <strong style='color: red;'>$conLai VNĐ</strong></p>
                </div>
            ";

            $this->mail->Body = $body;
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            // Lỗi chi tiết sẽ được ghi vào Log nhờ SMTPDebug = 2 ở trên
            error_log("MAILER ERROR FINAL: " . $this->mail->ErrorInfo);
            return "Lỗi: " . $this->mail->ErrorInfo;
        }
    }
}
?>
