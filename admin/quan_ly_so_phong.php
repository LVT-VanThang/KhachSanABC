<?php
session_start();
require_once '../classes/AdminAuth.php';
require_once '../classes/AdminPhong.php';

$auth = new AdminAuth();
$auth->kiemTraDangNhap();

$adminPhong = new AdminPhong();
include '../includes/headeradmin.php';

// Xử lý Xóa
if (isset($_GET['xoa'])) {
    $kq = $adminPhong->xoaSoPhong($_GET['xoa']);
    if ($kq === true) echo "<script>alert('Đã xóa phòng thành công!'); window.location.href='quan_ly_so_phong.php';</script>";
    else echo "<script>alert('$kq'); window.location.href='quan_ly_so_phong.php';</script>";
}

// Lấy danh sách
$result = $adminPhong->layDanhSachSoPhong();
?>

<main class="container page-padding">
    <div class="d-flex justify-between align-center mb-20">
        <div>
            <h1 class="tieu-de-muc" style="margin:0;">Quản lý Số phòng</h1>
            <p class="text-muted">Danh sách tất cả các phòng trong khách sạn</p>
        </div>
        <a href="them_so_phong.php" class="btn-big-cta"><i class="fas fa-plus"></i> Thêm phòng mới</a>
    </div>

    <div class="table-card">
        <table class="modern-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Số phòng</th>
                    <th style="width: 30%;">Loại phòng</th>
                    <th style="width: 15%; text-align:center;">Tầng</th>
                    <th style="width: 20%; text-align:center;">Trạng thái</th>
                    <th style="width: 20%; text-align:center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($result)): ?>
                    <?php foreach($result as $row): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: var(--text-dark); font-size: 1.1rem;">
                                <?php echo $row['so_phong']; ?>
                            </div>
                        </td>
                        <td style="color: #555;"><?php echo $row['ten_loai']; ?></td>
                        <td style="text-align:center; font-weight:600; color: #666;"><?php echo $row['tang']; ?></td>
                        <td style="text-align:center;">
                            <?php 
                                $st = $row['trang_thai'];
                                $classBadge = 'st-maintenance'; $icon = 'fa-tools';
                                if($st == 'Sẵn sàng') { $classBadge = 'st-ready'; $icon = 'fa-check'; }
                                elseif($st == 'Đang ở') { $classBadge = 'st-occupied'; $icon = 'fa-user'; }
                                elseif($st == 'Đã đặt') { $classBadge = 'st-booked'; $icon = 'fa-clock'; }
                                elseif($st == 'Đang dọn') { $classBadge = 'st-cleaning'; $icon = 'fa-broom'; }
                            ?>
                            <span class="status-badge <?php echo $classBadge; ?>">
                                <i class="fas <?php echo $icon; ?>" style="font-size:0.8rem; margin-right:4px;"></i> <?php echo $st; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="them_so_phong.php?id=<?php echo $row['id']; ?>" class="action-btn btn-orange" title="Sửa"><i class="fas fa-edit"></i></a>
                                <?php if ($row['trang_thai'] == 'Đang ở'): ?>
                                    <span class="action-btn btn-gray" title="Đang có khách"><i class="fas fa-trash"></i></span>
                                <?php else: ?>
                                    <a href="quan_ly_so_phong.php?xoa=<?php echo $row['id']; ?>" class="action-btn btn-red" title="Xóa" onclick="return confirm('Xóa phòng <?php echo $row['so_phong']; ?>?')"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px; color:#999;">Chưa có dữ liệu.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include '../includes/footeradmin.php'; ?>