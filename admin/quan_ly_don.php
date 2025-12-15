<?php
session_start();
require_once '../classes/AdminAuth.php';
require_once '../classes/AdminDonHang.php';

$auth = new AdminAuth();
$auth->kiemTraDangNhap();

$adminDon = new AdminDonHang();

include '../includes/headeradmin.php'; 

// Xử lý hành động (Duyệt, Hủy, Check-in...)
if (isset($_GET['action']) && isset($_GET['id'])) {
    // Chặn hành động xóa nếu người dùng cố tình nhập URL
    if ($_GET['action'] == 'xoa') {
        echo "<script>alert('Chức năng xóa đã bị vô hiệu hóa!'); window.location.href='quan_ly_don.php';</script>";
    } else {
        $ketQua = $adminDon->xuLyTrangThai($_GET['id'], $_GET['action']);
        if ($ketQua === true) {
            echo "<script>alert('Thao tác thành công!'); window.location.href='quan_ly_don.php';</script>";
        } elseif ($ketQua !== false) {
            echo "<script>alert('$ketQua'); window.location.href='quan_ly_don.php';</script>";
        }
    }
}

// --- XỬ LÝ TÌM KIẾM ---
$kw = isset($_GET['kw']) ? trim($_GET['kw']) : '';

// Lấy thống kê & Danh sách (có lọc theo từ khóa)
$stats = $adminDon->layThongKe();
$danhSachDon = $adminDon->layDanhSachDon($kw);
?>

<main class="container page-padding">
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-file-alt"></i></div>
            <div class="stat-info"><h3><?php echo $stats['tong_don']; ?></h3><p>Tổng đơn</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div class="stat-info"><h3><?php echo $stats['cho_xac_nhan']; ?></h3><p>Chờ xác nhận</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-info"><h3><?php echo $stats['da_giu_cho']; ?></h3><p>Đã giữ chỗ</p></div>
        </div>
    </div>

    <div class="table-card mb-20" style="padding: 20px; background: #fff; border-bottom: 1px solid #eee;">
        <form action="" method="GET" class="d-flex align-center" style="gap: 10px;">
            <div style="flex: 1; max-width: 400px; position: relative;">
                <input type="text" name="kw" class="form-control" 
                       placeholder="Nhập Mã đơn (ID) hoặc Tên khách..." 
                       value="<?php echo htmlspecialchars($kw); ?>" 
                       style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 20px;">
                <button type="submit" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); border: none; background: none; color: #666; cursor: pointer;">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            
            <?php if($kw != ''): ?>
                <a href="quan_ly_don.php" class="btn btn-sm btn-outline" style="border-radius: 20px;">
                    <i class="fas fa-times"></i> Xóa lọc
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-list"></i> Quản lý Đặt phòng</div>
            <a href="quan_ly_don.php" class="btn btn-sm btn-outline"><i class="fas fa-sync"></i> Làm mới</a>
        </div>

        <div style="overflow-x: auto;">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Thông tin phòng</th>
                        <th>Thời gian</th>
                        <th>Tổng tiền</th> 
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($danhSachDon)): ?>
                        <?php foreach($danhSachDon as $row): 
                            $idDon = $row['id'];
                            $dsPhong = $adminDon->layPhongCuaDon($idDon);
                        ?>
                            <tr>
                                <td><b>#<?php echo $row['id']; ?></b></td>
                                <td>
                                    <b><?php echo htmlspecialchars($row['ten_khach']); ?></b><br>
                                    <small class="text-muted"><?php echo $row['sdt_khach']; ?></small>
                                </td>
                                <td>
                                    <div style="color:var(--info); font-weight:bold;"><?php echo $row['ten_loai']; ?></div>
                                    <div style="margin-top:5px;">
                                        <span class="badge badge-gray">SL: <?php echo $row['so_luong']; ?></span>
                                        <?php foreach($dsPhong as $p): ?>
                                            <span class="badge badge-success">P.<?php echo $p; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="color:var(--success);">In: <?php echo date('d/m/Y', strtotime($row['ngay_nhan'])); ?></div>
                                    <div style="color:var(--danger);">Out: <?php echo date('d/m/Y', strtotime($row['ngay_tra'])); ?></div>
                                </td>
                                <td style="font-weight:bold; color:var(--warning);">
                                    <?php echo number_format($row['tong_tien'], 0, ',', '.'); ?>đ
                                </td>
                                <td class="text-center">
                                    <?php 
                                        $st = trim($row['trang_thai']);
                                        if ($st == 'Chờ xác nhận') echo '<span class="badge badge-warning">Chờ thanh toán</span>';
                                        elseif ($st == 'Đã đặt') echo '<span class="badge badge-info">Đã đặt</span>';
                                        elseif ($st == 'Đang ở') echo '<span class="badge badge-danger">Đang ở</span>';
                                        elseif ($st == 'Đã trả') echo '<span class="badge badge-success">Hoàn thành</span>';
                                        elseif ($st == 'Đã hủy') echo '<span class="badge badge-gray" style="background:#eee; color:#999;">Đã hủy</span>';
                                        else echo '<span class="badge badge-gray">' . $st . '</span>';
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($st == 'Chờ xác nhận'): ?>
                                        <a href="?action=duyet_giu_cho&id=<?php echo $row['id']; ?>" class="action-btn btn-green" title="Duyệt"><i class="fas fa-check"></i></a>
                                        <a href="?action=huy&id=<?php echo $row['id']; ?>" class="action-btn btn-orange" title="Hủy" onclick="return confirm('Hủy đơn?')"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>

                                    <?php if ($st == 'Đã đặt'): ?>
                                        <a href="?action=check_in&id=<?php echo $row['id']; ?>" class="action-btn btn-blue" title="Check-in" onclick="return confirm('Khách đến nhận phòng?')"><i class="fas fa-key"></i></a>
                                        <a href="?action=huy&id=<?php echo $row['id']; ?>" class="action-btn btn-orange" title="Hủy" onclick="return confirm('Hủy đơn?')"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>
                                    
                                    </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center" style="padding:30px;">Không tìm thấy đơn hàng nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include '../includes/footeradmin.php'; ?>
