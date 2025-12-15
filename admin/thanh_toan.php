<?php
session_start();
require_once '../classes/AdminAuth.php';
require_once '../classes/AdminDonHang.php';

$auth = new AdminAuth();
$auth->kiemTraDangNhap();

$adminDon = new AdminDonHang();
include __DIR__ . '/../includes/headeradmin.php';

// 1. Kiểm tra ID
if (!isset($_GET['id_don']) || empty($_GET['id_don'])) {
    die("<div class='container page-padding'><div class='alert alert-danger'>Lỗi: Thiếu mã đơn!</div></div>");
}
$idDon = (int)$_GET['id_don'];

// 2. Lấy dữ liệu đơn
$donData = $adminDon->layChiTietThanhToan($idDon);
if (!$donData) die("Đơn không tồn tại.");

// 3. XỬ LÝ LOGIC TÍNH TOÁN
$ngayNhan = strtotime($donData['ngay_nhan']);
$ngayHienTai = time(); 

// FIX LỖI CHECK-IN TƯƠNG LAI
if ($ngayHienTai < $ngayNhan) {
    $soNgayO = 1; // Khách check-in sớm hoặc test, tính 1 ngày
} else {
    $diff = $ngayHienTai - $ngayNhan;
    $soNgayO = ceil($diff / (60 * 60 * 24)); 
    if ($soNgayO < 1) $soNgayO = 1; 
}

$tongTienPhong = $donData['gia_tien'] * $soNgayO * $donData['so_luong'];
$tienDaCoc = $donData['tien_coc'];
$tienConLai = $tongTienPhong - $tienDaCoc;

// 4. XỬ LÝ POST THANH TOÁN
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ngayTra = date('Y-m-d H:i:s');
    $tong = $_POST['tong_tien_phong'];
    
    // Gọi hàm xử lý từ Class
    $adminDon->thucHienTraPhong($idDon, $tong, $ngayTra);
    
    echo "<script>alert('Thanh toán thành công!'); window.location.href='danh_sach_dang_o.php';</script>";
    exit;
}
?>

<main class="container page-padding">
    <div class="invoice-box">
        <div class="invoice-header">
            <div>
                <h2 style="margin:0; font-size:1.5rem;"><i class="fas fa-file-invoice"></i> QUYẾT TOÁN</h2>
                <span style="opacity:0.8; font-size:0.9rem;">Hóa đơn điện tử</span>
            </div>
            <div style="text-align:right;">
                <div style="font-weight:bold; font-size:1.1rem;">#<?php echo $idDon; ?></div>
                <div style="font-size:0.9rem;"><?php echo date('d/m/Y H:i'); ?></div>
            </div>
        </div>

        <div class="invoice-body">
            <div class="info-row">
                <div class="info-col">
                    <h3>Khách hàng</h3>
                    <p style="font-size:1.2rem;"><?php echo htmlspecialchars($donData['ten_khach']); ?></p>
                    <p><i class="fas fa-phone-alt"></i> <?php echo $donData['sdt_khach']; ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo $donData['email_khach']; ?></p>
                </div>
                <div class="info-col" style="text-align:right;">
                    <h3>Dịch vụ phòng</h3>
                    <p class="text-primary" style="font-weight:bold; font-size:1.1rem; color: #d4af37;">
                        <?php echo $donData['ten_loai']; ?>
                    </p>
                    <p>Số lượng: <strong><?php echo $donData['so_luong']; ?></strong> phòng</p>
                    <p>
                        <?php echo date('d/m', $ngayNhan); ?> <i class="fas fa-arrow-right"></i> <?php echo date('d/m', $ngayHienTai); ?>
                        <br>
                        <span class="badge badge-info">(<?php echo $soNgayO; ?> đêm)</span>
                    </p>
                </div>
            </div>

            <table class="invoice-table">
                <thead><tr><th>Khoản mục</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th></tr></thead>
                <tbody>
                    <tr>
                        <td>Tiền phòng (<?php echo $soNgayO; ?> đêm)</td>
                        <td><?php echo number_format($donData['gia_tien']); ?> ₫</td>
                        <td><?php echo $donData['so_luong']; ?></td>
                        <td><strong><?php echo number_format($tongTienPhong); ?> ₫</strong></td>
                    </tr>
                    <tr class="row-deposit"><td colspan="3" style="text-align:right;">Đã đặt cọc (Trừ lại)</td><td>- <?php echo number_format($donData['tien_coc']); ?> ₫</td></tr>
                    <tr class="row-final"><td colspan="3" style="text-align:right;">TỔNG CẦN THANH TOÁN:</td><td><?php echo number_format($tienConLai); ?> ₫</td></tr>
                </tbody>
            </table>

            <form method="POST" class="invoice-actions">
                <input type="hidden" name="tong_tien_phong" value="<?php echo $tongTienPhong; ?>">
                
                <a href="danh_sach_dang_o.php" class="btn btn-cancel btn-pill" style="display:flex; align-items:center; text-decoration:none; margin-right: 0;"><i class="fas fa-arrow-left"></i> Quay lại</a>
                <button type="submit" class="btn btn-submit btn-pill" style="width:auto; background: #dc2626;" onclick="return confirm('Xác nhận đã thu đủ <?php echo number_format($tienConLai); ?> đ và trả phòng?');">
                    <i class="fas fa-cash-register"></i> THANH TOÁN & TRẢ PHÒNG
                </button>
            </form>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footeradmin.php'; ?>