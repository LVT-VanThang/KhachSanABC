<?php
// classes/Mailer.php

class Mailer {
    private $apiKey;
    private $apiEndpoint = 'https://api.brevo.com/v3/smtp/email';

    public function __construct() {
        $this->apiKey = getenv('BREVO_API_KEY'); 

        // Kiểm tra nếu quên chưa cài đặt biến (để debug)
        if (!$this->apiKey) {
            error_log("LỖI: Chưa cấu hình biến môi trường BREVO_API_KEY trên Railway!");
        }
    }

    public function guiEmailThanhToan($emailKhach, $tenKhach, $data) {
        // Chuẩn bị nội dung Email
        $tong = number_format($data['tong_tien']);
        $coc = number_format($data['tien_coc']);
        $conLai = number_format($data['tong_tien'] - $data['tien_coc']);
        
        $htmlContent = "
            <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd;'>
                <h2 style='color: #27ae60;'>Thanh toán thành công!</h2>
                <p>Xin chào <strong>$tenKhach</strong>,</p>
                <p>Đơn hàng <strong>#{$data['ma_don']}</strong> đã được xác nhận.</p>
                <ul>
                    <li><strong>Loại phòng:</strong> {$data['loai_phong']}</li>
                    <li><strong>Phòng số:</strong> {$data['so_phong']}</li>
                    <li><strong>Tổng tiền:</strong> $tong VNĐ</li>
                    <li><strong>Đã cọc:</strong> $coc VNĐ</li>
                    <li><strong>Cần thanh toán thêm:</strong> <span style='color:red'>$conLai VNĐ</span></li>
                </ul>
                <p>Cảm ơn quý khách!</p>
            </div>
        ";

        // Cấu trúc dữ liệu gửi lên Brevo API
        $dataSend = [
            "sender" => [
                "name" => "Khách sạn ABC Luxury",
                "email" => "luongvanthang1301@gmail.com" // Email này phải trùng email đăng nhập Brevo
            ],
            "to" => [
                [
                    "email" => $emailKhach,
                    "name" => $tenKhach
                ]
            ],
            "subject" => "Thanh toán thành công - Mã đơn #" . $data['ma_don'],
            "htmlContent" => $htmlContent
        ];

        // Gửi qua CURL (Giống như truy cập web, không bị chặn port)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiEndpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $this->apiKey,
            'content-type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataSend));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Kiểm tra kết quả (201 là thành công)
        if ($httpCode == 201 || $httpCode == 200) {
            return true;
        } else {
            // Ghi log lỗi nếu có
            error_log("BREVO API ERROR: " . $response);
            return "Lỗi API: " . $response;
        }
    }
}
?>
