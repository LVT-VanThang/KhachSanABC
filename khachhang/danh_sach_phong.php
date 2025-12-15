<?php
// 1. CẤU HÌNH & KHỞI TẠO
require_once '../classes/LoaiPhong.php'; // Nhúng class
$tieuDeTrang = "Danh sách phòng - Khách sạn ABC";
include '../includes/headerkhachhang.php';

$loaiPhongObj = new LoaiPhong(); // Khởi tạo đối tượng

// 2. NHẬN DỮ LIỆU TỪ URL (Giữ nguyên logic nhận biến GET cũ)
$checkin = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$sucChua = isset($_GET['suc_chua']) && $_GET['suc_chua'] != 'all' ? (int)$_GET['suc_chua'] : 0;
$soGiuong = isset($_GET['so_giuong']) && $_GET['so_giuong'] != 'all' ? (int)$_GET['so_giuong'] : 0; // Thêm biến này nếu form có input so_giuong
$view = isset($_GET['view']) && $_GET['view'] != 'all' ? $_GET['view'] : '';
$mucGia = isset($_GET['muc_gia']) ? $_GET['muc_gia'] : 'all';

// 3. GỌI HÀM TÌM KIẾM TỪ CLASS (Thay vì viết SQL trực tiếp)
$boLoc = [
    'suc_chua' => $sucChua,
    'so_giuong' => $soGiuong,
    'view' => $view,
    'muc_gia' => $mucGia
];
$danhSachLoai = $loaiPhongObj->timKiemPhong($boLoc);
?>

<main>
    <div class="thanh-tim-kiem-sticky">
        <div class="container">
            <form action="danh_sach_phong.php" method="GET" class="form-tim-kiem-ngang">
                
                <div class="input-item">
                    <label>Ngày nhận:</label>
                    <input type="date" name="checkin" id="checkin" value="<?php echo $checkin; ?>" min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="input-item">
                    <label>Ngày trả:</label>
                    <input type="date" name="checkout" id="checkout" value="<?php echo $checkout; ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                </div>

                <div class="input-item">
                    <label>Số người:</label>
                    <select name="suc_chua">
                        <option value="all">Tất cả</option>
                        <option value="1" <?php if($sucChua == 1) echo 'selected'; ?>>1+</option>
                        <option value="2" <?php if($sucChua == 2) echo 'selected'; ?>>2+</option>
                        <option value="4" <?php if($sucChua == 4) echo 'selected'; ?>>4+</option>
                    </select>
                </div>

                <div class="input-item">
                    <label>Giá:</label>
                    <select name="muc_gia">
                        <option value="all">Tất cả</option>
                        <option value="duoi-2tr" <?php if($mucGia == 'duoi-2tr') echo 'selected'; ?>>&lt; 2tr</option>
                        <option value="2tr-3tr" <?php if($mucGia == '2tr-3tr') echo 'selected'; ?>>2-3tr</option>
                        <option value="tren-3tr" <?php if($mucGia == 'tren-3tr') echo 'selected'; ?>>&gt; 3tr</option>
                    </select>
                </div>

                <button type="submit" class="btn-filter">
                    <i class="fas fa-search"></i> TÌM
                </button>
                
                <a href="danh_sach_phong.php" class="btn-reset" title="Xóa bộ lọc">
                    <i class="fas fa-sync-alt"></i>
                </a>
            </form>
        </div>
    </div>

    <div class="container page-padding">
        
        <div style="text-align: center; margin-bottom: 40px;">
            <h2 class="tieu-de-muc">Kết quả tìm kiếm</h2>
            <?php if($checkin && $checkout): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Đang tìm từ <b><?php echo date('d/m', strtotime($checkin)); ?></b> đến <b><?php echo date('d/m', strtotime($checkout)); ?></b>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle"></i> Vui lòng chọn <b>Ngày nhận</b> và <b>Ngày trả</b> để xem giá và đặt phòng.
                </div>
            <?php endif; ?>
        </div>

        <div class="luoi-phong">
            <?php if (!empty($danhSachLoai)): ?>
                <?php foreach($danhSachLoai as $row): ?>
                    <?php
                        // Xử lý dữ liệu hiển thị
                        $idLoai = $row['id'];
                        $anh = !empty($row['anh_dai_dien']) ? 'data:image/jpeg;base64,' . base64_encode($row['anh_dai_dien']) : '../images/no-image.jpg';
                        
                        // GỌI HÀM KIỂM TRA PHÒNG TRỐNG TỪ CLASS LOAIPHONG
                        $phongTrong = $loaiPhongObj->demPhongTrong($idLoai, $checkin, $checkout);
                    ?>

                    <div class="the-phong">
                        <div class="khung-anh">
                            <img src="<?php echo $anh; ?>" alt="<?php echo $row['ten_loai']; ?>">
                        </div>
                        
                        <div class="noi-dung-phong">
                            <h3><?php echo $row['ten_loai']; ?></h3>
                            
                            <div class="thong-tin-phu">
                                <span><i class="fas fa-user"></i> <?php echo $row['suc_chua']; ?></span> | 
                                <span><i class="fas fa-bed"></i> <?php echo $row['so_giuong']; ?></span> | 
                                <span><i class="fas fa-eye"></i> <?php echo $row['huong_nhin']; ?></span>
                            </div>
                            
                            <div class="gia-phong-listing">
                                <?php echo number_format($row['gia_tien'], 0, ',', '.'); ?> VNĐ <span>/đêm</span>
                            </div>
                            
                            <?php if ($checkin && $checkout): ?>
                                <?php if ($phongTrong > 0): ?>
                                    <div class="status-text ok">
                                        <i class="fas fa-check-circle"></i> Còn <?php echo $phongTrong; ?> phòng trống
                                    </div>
                                    <a href="dat_phong.php?id=<?php echo $idLoai; ?>&checkin=<?php echo $checkin; ?>&checkout=<?php echo $checkout; ?>&soluong=1" class="nut-action dat-ngay">
                                        ĐẶT NGAY
                                    </a>
                                <?php else: ?>
                                    <div class="status-text full">
                                        <i class="fas fa-times-circle"></i> Hết phòng
                                    </div>
                                    <button disabled class="nut-action het-phong">HẾT PHÒNG</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="status-text info">
                                    <i class="fas fa-info-circle"></i> Chọn ngày để xem
                                </div>
                                <a href="#" onclick="document.getElementById('checkin').focus(); return false;" class="nut-action chon-ngay">
                                    CHỌN NGÀY
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <p style='text-align:center; width:100%;'>Không tìm thấy phòng nào phù hợp.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    const inDate = document.getElementById('checkin');
    const outDate = document.getElementById('checkout');
    inDate.addEventListener('change', function() {
        const d = new Date(this.value);
        d.setDate(d.getDate() + 1);
        outDate.min = d.toISOString().split('T')[0];
        if(outDate.value <= this.value) {
            outDate.value = outDate.min;
        }
    });
</script>

<?php 
// Không cần đóng $ketNoiDb->close() thủ công vì class tự xử lý hoặc PHP tự đóng khi hết script
include '../includes/footerkhachhang.php'; 
?>