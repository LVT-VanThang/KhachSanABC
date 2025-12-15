<?php
require_once '../classes/LienHe.php';

$tieuDeTrang = "Liên hệ - Khách sạn ABC";
include '../includes/headerkhachhang.php';

$thongBao = "";

// XỬ LÝ KHI GỬI FORM
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hoTen = $_POST['ho_ten'];
    $email = $_POST['email'];
    $noiDung = $_POST['noi_dung'];
    
    // Khởi tạo đối tượng và gọi hàm
    $lienHeObj = new LienHe();
    $ketQua = $lienHeObj->themLienHe($hoTen, $email, $noiDung);
    
    if ($ketQua) {
        $thongBao = "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất.</div>";
    } else {
        $thongBao = "<div class='alert alert-danger'>Lỗi hệ thống. Vui lòng thử lại sau.</div>";
    }
}
?>


<main>
    <div class="page-banner contact-bg">
        <div class="container">
            <h1 style="font-size: 2.5rem;">Liên Hệ</h1>
            <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn 24/7</p>
        </div>
    </div>

    <section class="container page-padding">
        <?php echo $thongBao; ?>

        <div class="dat-phong-wrapper">
            <div class="cot-form">
                <div class="form-box">
                    <h3 class="form-header">Gửi tin nhắn cho chúng tôi</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Họ và tên:</label>
                            <input type="text" name="ho_ten" class="form-control" required placeholder="Nhập tên của bạn">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email:</label>
                            <input type="email" name="email" class="form-control" required placeholder="Nhập email của bạn">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nội dung:</label>
                            <textarea name="noi_dung" class="form-control" rows="5" required placeholder="Bạn cần hỗ trợ gì?"></textarea>
                        </div>
                        <button type="submit" class="btn-submit">Gửi Tin Nhắn</button>
                    </form>
                </div>
            </div>

            <div class="cot-tom-tat">
                <div class="summary-box" style="background:#fcfcfc;">
                    <h3 class="summary-title">Thông tin liên lạc</h3>
                    
                    <div style="margin-bottom: 20px;">
                        <p style="margin-bottom: 10px;"><i class="fas fa-map-marker-alt" style="color: #d4af37; width: 20px;"></i> <strong>Địa chỉ:</strong></p>
                        <p style="color: #666;">123 Đường Biển, Phường A, Thành phố B</p>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <p style="margin-bottom: 10px;"><i class="fas fa-phone" style="color: #d4af37; width: 20px;"></i> <strong>Hotline:</strong></p>
                        <p style="color: #666; font-size: 1.1rem; font-weight: bold;">090.123.4567</p>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <p style="margin-bottom: 10px;"><i class="fas fa-envelope" style="color: #d4af37; width: 20px;"></i> <strong>Email:</strong></p>
                        <p style="color: #666;">contact@abchotel.com</p>
                    </div>

                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.424364070008!2d106.69763731474896!3d10.77926949232047!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f385570472f%3A0x1787491df0ed8d6a!2sDinh%20Doc%20Lap!5e0!3m2!1sen!2s!4v1634567890123!5m2!1sen!2s" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>

<?php include '../includes/footerkhachhang.php'; ?>