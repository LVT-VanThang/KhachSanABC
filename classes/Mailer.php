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
        $this->mail->isSMTP();
        $this->mail->CharSet    = 'UTF-8';
        $this->mail->SMTPDebug  = 0; // Tắt debug cho gọn
        
        // --- CẤU HÌNH BREVO (CHUẨN KHÔNG CẦN CHỈNH) ---
        $this->mail->Host       = 'smtp-relay.brevo.com'; 
        $this->mail->SMTPAuth   = true;
        $this->mail->Port       = 587;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // 👇 1. ĐIỀN EMAIL ĐĂNG NHẬP BREVO CỦA BẠN VÀO ĐÂY (VD: abc@gmail.com)
        $this->mail->Username   = 'luongvanthang1301@gmail.com'; 
        
        // 👇 2. DÁN CÁI MÃ KHÓA DÀI NGOẰNG VỪA COPY VÀO ĐÂY
        $this->mail->Password   = 'xsmtpsib-4cc221885652138ab53319344d265d21716c23904d7ac3cab02b4f36448a6dcf-HoS7vAvzBmN44lF5'; 


        
        // 👇 3. QUAN TRỌNG: Email người gửi PHẢI TRÙNG với email đăng nhập Brevo
        $emailGui = 'luongvanthang1301@gmail.com'; 
        $this->mail->setFrom($emailGui, 'Khách sạn ABC Luxury');
        
        // Fix lỗi SSL trên Railway
        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
    }

   public function guiEmailThanhToan($emailKhach, $tenKhach, $data) {
        set_time_limit(120); 
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($emailKhach, $tenKhach);
            $this->mail->isHTML(true);
            $this->mail->Subject = "Thanh toán thành công - Mã đơn #" . $data['ma_don'];

            // 1. Tính toán số liệu
            $tong = number_format($data['tong_tien']);
            $coc = number_format($data['tien_coc']);
            
            // Tính số tiền còn lại cần thanh toán
            $soTienConLai = $data['tong_tien'] - $data['tien_coc'];
            $conLai = number_format($soTienConLai);

            // Format ngày tháng (Giả sử dữ liệu vào là Y-m-d)
            $ngayNhan = date('d/m/Y', strtotime($data['ngay_nhan']));
            $ngayTra = date('d/m/Y', strtotime($data['ngay_tra']));

            // 2. Nội dung Email chi tiết
            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
                    <div style='background-color: #27ae60; color: #fff; padding: 20px; text-align: center;'>
                        <h2 style='margin: 0;'>THANH TOÁN THÀNH CÔNG</h2>
                    </div>
                    
                    <div style='padding: 20px;'>
                        <p>Xin chào <strong>$tenKhach</strong>,</p>
                        <p>Cảm ơn bạn đã đặt phòng tại ABC Luxury. Chúng tôi xác nhận đã nhận được khoản đặt cọc của bạn.</p>
                        
                        <table style='width: 100%; border-collapse: collapse; margin-top: 15px;'>
                            <tr style='background-color: #f9f9f9;'>
                                <td style='padding: 10px; border: 1px solid #ddd;'><strong>Mã đơn hàng:</strong></td>
                                <td style='padding: 10px; border: 1px solid #ddd; color: #333;'>#{$data['ma_don']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 10px; border: 1px solid #ddd;'><strong>Loại phòng:</strong></td>
                                <td style='padding: 10px; border: 1px solid #ddd;'>{$data['loai_phong']}</td>
                            </tr>
                            <tr style='background-color: #f9f9f9;'>
                                <td style='padding: 10px; border: 1px solid #ddd;'><strong>Phòng số:</strong></td>
                                <td style='padding: 10px; border: 1px solid #ddd; color: #0056b3; font-weight: bold;'>{$data['so_phong']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 10px; border: 1px solid #ddd;'><strong>Thời gian:</strong></td>
                                <td style='padding: 10px; border: 1px solid #ddd;'>$ngayNhan - $ngayTra</td>
                            </tr>
                        </table>

                        <br>
                        <div style='background-color: #fff8e1; padding: 15px; border: 1px solid #ffecb3; border-radius: 5px;'>
                            <p style='margin: 5px 0;'>💰 <strong>Tổng tiền:</strong> $tong VNĐ</p>
                            <p style='margin: 5px 0; color: #27ae60;'>✅ <strong>Đã đặt cọc:</strong> $coc VNĐ</p>
                            <p style='margin: 5px 0; color: #c0392b; font-size: 16px;'>❗ <strong>Cần thanh toán tại quầy: $conLai VNĐ</strong></p>
                        </div>

                        <p style='margin-top: 20px; font-size: 13px; color: #777;'>Nếu có thắc mắc, vui lòng liên hệ hotline: 0123.456.789</p>
                    </div>
                </div>
            ";
            
            $this->mail->Body = $body;
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("MAIL ERROR: " . $this->mail->ErrorInfo);
            return "Lỗi gửi mail: " . $this->mail->ErrorInfo;
        }
    }
}
?>
