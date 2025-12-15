<?php
// 1. NHÚNG CÁC CLASS CẦN THIẾT
require_once '../classes/LoaiPhong.php';
require_once '../classes/DatPhong.php';

$tieuDeTrang = "Hoàn tất đặt phòng - Khách sạn ABC";
include '../includes/headerkhachhang.php';

// 2. CHECK DỮ LIỆU ĐẦU VÀO
if (!isset($_GET['id']) || empty($_GET['checkin']) || empty($_GET['checkout'])) {
    echo "<script>window.location.href='index.php';</script>"; exit;
}

$idLoai = (int)$_GET['id'];
$checkin = $_GET['checkin'];
$checkout = $_GET['checkout'];
$soLuongDefault = isset($_GET['soluong']) ? (int)$_GET['soluong'] : 1;

// 3. KHỞI TẠO ĐỐI TƯỢNG OOP
$loaiPhongObj = new LoaiPhong();
$datPhongObj = new DatPhong();

// 4. LẤY THÔNG TIN LOẠI PHÒNG (Dùng Class LoaiPhong)
$phongInfo = $loaiPhongObj->layChiTiet($idLoai);
if (!$phongInfo) die("Lỗi: Không tìm thấy loại phòng.");

$anh = !empty($phongInfo['anh_dai_dien']) ? 'data:image/jpeg;base64,' . base64_encode($phongInfo['anh_dai_dien']) : '../images/no-image.jpg';

// Tính toán ngày
$d1 = new DateTime($checkin);
$d2 = new DateTime($checkout);
$soDem = $d1->diff($d2)->days; 
if($soDem < 1) $soDem = 1;

// 5. KIỂM TRA PHÒNG TRỐNG (Dùng Class LoaiPhong)
$phongTrong = $loaiPhongObj->demPhongTrong($idLoai, $checkin, $checkout);

if ($phongTrong <= 0) {
    echo "<script>alert('Rất tiếc, loại phòng này vừa hết chỗ!'); window.location.href='index.php';</script>";
    exit;
}
$tongTienHienThi = $phongInfo['gia_tien'] * $soDem * $soLuongDefault;

// 6. XỬ LÝ POST (TẠO ĐƠN HÀNG)
$datThanhCong = false;
$maDonHang = 0;
$tienCocFinal = 0;
$thongBaoLoi = "";
// Biến tạm để giữ giá trị form nếu lỗi (hoặc thành công)
$ten = ""; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten = $_POST['ten_khach'];
    $sdt = $_POST['sdt_khach'];
    $email = $_POST['email_khach'];
    $slDat = (int)$_POST['so_luong_chot'];
    
    // Check lại phòng trống lần cuối
    $phongTrongHienTai = $loaiPhongObj->demPhongTrong($idLoai, $checkin, $checkout);

    if ($slDat > $phongTrongHienTai) {
        $thongBaoLoi = "<div class='alert alert-danger'>Lỗi: Chỉ còn $phongTrongHienTai phòng trống. Vui lòng chọn lại.</div>";
    } else {
        $tongTienFinal = $phongInfo['gia_tien'] * $soDem * $slDat;
        $tienCocFinal = $tongTienFinal * 0.3; 
        
        // Chuẩn bị dữ liệu cho hàm tạo đơn
        $duLieuDon = [
            'loai_id' => $idLoai,
            'so_luong' => $slDat,
            'ten' => $ten,
            'email' => $email,
            'sdt' => $sdt,
            'ngay_nhan' => $checkin,
            'ngay_tra' => $checkout,
            'tong_tien' => $tongTienFinal,
            'tien_coc' => $tienCocFinal
        ];

        // GỌI HÀM TỪ CLASS DatPhong
        $maDonHang = $datPhongObj->taoDonDatPhong($duLieuDon);
        
        if ($maDonHang) {
            $datThanhCong = true;
        } else {
            $thongBaoLoi = "<div class='alert alert-danger'>Lỗi hệ thống không thể tạo đơn.</div>";
        }
    }
}
?>

<main class="container page-padding">
    
    <?php if ($datThanhCong): ?>
        <div class="payment-success-wrapper">
            
            <div class="success-header">
                <div class="success-icon"><i class="fas fa-hotel"></i></div>
                <h2 style="margin: 0; font-size: 2rem;">Khách Sạn ABC</h2>
                <p style="opacity: 0.9; margin-top: 10px;">Cảm ơn <strong><?php echo htmlspecialchars($ten); ?></strong>, đơn hàng của bạn đã được khởi tạo.</p>
            </div>

            <div id="payment-box" class="payment-body">
                <div class="col-qr">
                    <h3 style="color: #2c3e50; margin-top: 0;">Quét mã để thanh toán</h3>
                    <p style="color: #7f8c8d; font-size: 0.9rem;">Sử dụng ứng dụng ngân hàng hoặc ví điện tử</p>
                    
                    <?php
                        $nganHangId = 'MB'; 
                        $soTaiKhoan = '0372036292'; 
                        $tenChuTaiKhoan = 'LUONG VAN THANG'; 
                        $noiDungCk = "DH" . $maDonHang;
                        $qrUrl = "https://img.vietqr.io/image/$nganHangId-$soTaiKhoan-compact2.jpg?amount=$tienCocFinal&addInfo=$noiDungCk&accountName=$tenChuTaiKhoan";
                    ?>
                    
                    <div class="qr-box">
                        <img src="<?php echo $qrUrl; ?>" alt="QR Payment" class="qr-img">
                    </div>
                    
                    <div class="status-waiting">
                        <i class="fas fa-sync fa-spin"></i> Đang chờ xác nhận tự động...
                    </div>
                </div>

                <div class="col-transfer">
                    <h3 style="color: #2c3e50; margin-top: 0;">Thông tin chuyển khoản</h3>
                    
                    <div class="alert alert-warning">
                        <p style="margin: 0; font-weight: bold;"><i class="fas fa-exclamation-triangle"></i> Lưu ý quan trọng:</p>
                        <p style="margin: 5px 0 0; font-size: 0.9rem;">Vui lòng nhập chính xác <strong>Nội dung chuyển khoản</strong> bên dưới để hệ thống tự động kích hoạt đơn hàng.</p>
                    </div>

                    <table class="table-bank">
                        <tr><td class="label">Ngân hàng:</td><td class="value">MB Bank</td></tr>
                        <tr><td class="label">Số tài khoản:</td><td class="value highlight"><?php echo $soTaiKhoan; ?></td></tr>
                        <tr><td class="label">Chủ tài khoản:</td><td class="value" style="text-transform: uppercase;"><?php echo $tenChuTaiKhoan; ?></td></tr>
                        <tr><td class="label">Số tiền cọc (30%):</td><td class="value money"><?php echo number_format($tienCocFinal); ?> đ</td></tr>
                        <tr><td class="label">Nội dung CK:</td><td class="value"><span class="code-box"><?php echo $noiDungCk; ?></span></td></tr>
                    </table>

                    <div style="margin-top: 30px; text-align: right;">
                        <a href="index.php" style="color: #7f8c8d;"><i class="fas fa-arrow-left"></i> Quay lại trang chủ</a>
                    </div>
                </div>
            </div>

            <div id="success-box" style="display: none; padding: 50px; text-align: center;">
                <div style="width: 100px; height: 100px; background: #d4edda; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                     <i class="fas fa-check-circle" style="font-size: 50px; color: #27ae60;"></i>
                </div>
                <h2 style="color: #27ae60; margin-bottom: 10px;">THANH TOÁN THÀNH CÔNG!</h2>
                <p style="color: #555; font-size: 1.1rem;">Hệ thống đã nhận được tiền cọc. Phòng của bạn đã được giữ.</p>
                
                <div style="background: #f9f9f9; border-radius: 8px; padding: 20px; max-width: 500px; margin: 30px auto; text-align: left; border: 1px solid #eee;">
                    <h4 style="margin: 0 0 15px 0; border-bottom: 2px solid #ddd; padding-bottom: 10px;">Chi tiết đặt phòng</h4>
                    <p style="margin: 5px 0;"><strong>Mã đơn:</strong> #<?php echo $maDonHang; ?></p>
                    <p style="margin: 5px 0;"><strong>Tên khách:</strong>   <?php echo $ten; ?></p>
                    <p style="margin: 5px 0;"><strong>Email:</strong> <?php echo $email; ?></p>
                    <p style="margin: 5px 0;"><strong>Ngày Checkin:</strong> <?php echo $checkin; ?></p>
                    <p style="margin: 5px 0;"><strong>Ngày Checkout:</strong> <?php echo $checkout; ?></p>
                    <p style="margin: 5px 0;"><strong>Phòng:</strong> <span id="room-name" style="color: #2980b9; font-weight: bold;">...</span></p>
                </div>
                <a href="index.php" class="btn-submit" style="display:inline-block; width:auto; padding: 10px 30px;">Về Trang Chủ</a>
            </div>

        </div>

        <script>
            const orderId = <?php echo $maDonHang; ?>;
            let checkInterval;
            function checkPaymentStatus() {
                fetch('check_status.php?id=' + orderId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.getElementById('payment-box').style.display = 'none';
                            document.getElementById('success-box').style.display = 'block';
                            document.getElementById('room-name').innerText = data.phong;
                            clearInterval(checkInterval);
                        }
                    });
            }
            checkInterval = setInterval(checkPaymentStatus, 3000);
        </script>
    
    <?php else: ?>
        <h1 class="tieu-de-muc">Xác nhận thông tin đặt phòng</h1>
        
        <?php echo $thongBaoLoi; ?>

        <div class="dat-phong-wrapper">
            <div class="cot-form">
                <div class="form-box">
                    <h3 class="form-header">Thông tin liên hệ</h3>
                    <form method="POST">
                        <div class="form-group" style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
                            <label class="form-label">Số lượng phòng muốn đặt:</label>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="number" name="so_luong_chot" id="so_luong" class="form-control" 
                                       value="<?php echo ($soLuongDefault <= $phongTrong) ? $soLuongDefault : 1; ?>" 
                                       min="1" max="<?php echo $phongTrong; ?>" required style="width: 100px; font-weight: bold;">
                                <span style="color: green; font-size: 0.9rem;">(Còn <?php echo $phongTrong; ?> phòng trống)</span>
                            </div>
                        </div>
                        <div class="form-group"><label class="form-label">Họ và tên:</label><input type="text" name="ten_khach" class="form-control" required placeholder="Nhập họ tên..."></div>
                        <div class="form-group"><label class="form-label">Số điện thoại:</label><input type="text" name="sdt_khach" class="form-control" required placeholder="Nhập SĐT..."></div>
                        <div class="form-group"><label class="form-label">Email:</label><input type="email" name="email_khach" class="form-control" required placeholder="Nhập email..."></div>
                        <button type="submit" class="btn-submit">XÁC NHẬN & THANH TOÁN CỌC <i class="fas fa-arrow-right"></i></button>
                    </form>
                </div>
            </div>

            <div class="cot-tom-tat">
                <div class="summary-box">
                    <img src="<?php echo $anh; ?>" class="summary-img">
                    <h3 style="color: #2980b9; font-size: 1.3rem; margin-top:0;"><?php echo $phongInfo['ten_loai']; ?></h3>
                    <div class="summary-details">
                        <p style="margin: 5px 0;"><strong>Nhận:</strong> <?php echo date('d/m/Y', strtotime($checkin)); ?></p>
                        <p style="margin: 5px 0;"><strong>Trả:</strong> <?php echo date('d/m/Y', strtotime($checkout)); ?></p>
                        <p style="margin: 5px 0; color: #666;">(<?php echo $soDem; ?> đêm)</p>
                    </div>
                    <div class="summary-row"><span>Đơn giá:</span><span><?php echo number_format($phongInfo['gia_tien'], 0, ',', '.'); ?> ₫</span></div>
                    <div class="summary-row"><span>Số lượng:</span><strong id="display_sl">x <?php echo $soLuongDefault; ?></strong></div>
                    <hr style="border:0; border-top:1px dashed #ccc; margin:15px 0;">
                    <div class="total-row"><span>Tổng cộng:</span><span id="display_total"><?php echo number_format($tongTienHienThi, 0, ',', '.'); ?> ₫</span></div>
                    <div class="deposit-row"><span>Cọc trước (30%):</span><span id="display_deposit"><?php echo number_format($tongTienHienThi * 0.3, 0, ',', '.'); ?> ₫</span></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</main>

<script>
    const inputSL = document.getElementById('so_luong');
    const displaySL = document.getElementById('display_sl');
    const displayTotal = document.getElementById('display_total');
    const displayDeposit = document.getElementById('display_deposit');
    const giaPhong = <?php echo $phongInfo['gia_tien']; ?>;
    const soDem = <?php echo $soDem; ?>;

    if(inputSL){
        inputSL.addEventListener('input', function() {
            let sl = parseInt(this.value);
            if (isNaN(sl) || sl < 1) sl = 1;
            if (sl > <?php echo $phongTrong; ?>) sl = <?php echo $phongTrong; ?>;
            displaySL.innerText = "x " + sl;
            let total = giaPhong * soDem * sl;
            displayTotal.innerText = new Intl.NumberFormat('vi-VN').format(total) + " ₫";
            displayDeposit.innerText = new Intl.NumberFormat('vi-VN').format(total * 0.3) + " ₫";
        });
    }
</script>

<?php include '../includes/footerkhachhang.php'; ?>