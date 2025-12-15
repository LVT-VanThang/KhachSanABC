<?php
session_start();
require_once '../classes/AdminAuth.php';
require_once '../classes/AdminLienHe.php';

$auth = new AdminAuth();
$auth->kiemTraDangNhap();

$adminLienHe = new AdminLienHe();
include '../includes/headeradmin.php';

// Xử lý Hành động
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_GET['action'] == 'xoa') $adminLienHe->xoaLienHe($id);
    elseif ($_GET['action'] == 'da_xem') $adminLienHe->danhDauDaXem($id);
    echo "<script>window.location.href='quan_ly_lien_he.php';</script>";
}

// Lấy dữ liệu
$stats = $adminLienHe->layThongKe();
$listLienHe = $adminLienHe->layDanhSachLH();
?>

<main class="container page-padding">
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-inbox"></i></div>
            <div class="stat-info"><h3><?php echo $stats['tong']; ?></h3><p>Tổng tin nhắn</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-envelope-open-text"></i></div>
            <div class="stat-info"><h3><?php echo $stats['chua_xem']; ?></h3><p>Chưa đọc</p></div>
        </div>
    </div>

    <div class="table-card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-envelope"></i> Hộp thư khách hàng</div>
            <a href="quan_ly_lien_he.php" class="btn btn-sm btn-outline"><i class="fas fa-sync"></i> Làm mới</a>
        </div>
        <div style="overflow-x: auto;">
            <table class="modern-table">
                <thead><tr><th>ID</th><th>Người gửi</th><th>Nội dung</th><th>Ngày gửi</th><th>Trạng thái</th><th class="text-center">Xóa</th></tr></thead>
                <tbody>
                    <?php if (!empty($listLienHe)): foreach($listLienHe as $row): ?>
                        <tr style="<?php echo ($row['trang_thai'] == 'Chua_xem') ? 'background-color:var(--warning-light);' : ''; ?>">
                            <td>#<?php echo $row['id']; ?></td>
                            <td>
                                <b><?php echo htmlspecialchars($row['ho_ten']); ?></b><br>
                                <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
                            </td>
                            <td><div style="max-height:60px; overflow:auto;"><?php echo nl2br(htmlspecialchars($row['noi_dung'])); ?></div></td>
                            <td><?php echo date('H:i d/m/Y', strtotime($row['ngay_gui'])); ?></td>
                            <td>
                                <?php if($row['trang_thai'] == 'Chua_xem'): ?>
                                    <a href="?action=da_xem&id=<?php echo $row['id']; ?>" class="badge badge-warning"><i class="fas fa-eye"></i> Mới</a>
                                <?php else: ?>
                                    <span class="badge badge-gray">Đã xem</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="?action=xoa&id=<?php echo $row['id']; ?>" class="action-btn btn-red" onclick="return confirm('Xóa tin nhắn này?')"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; else: echo "<tr><td colspan='6' class='text-center'>Hộp thư trống.</td></tr>"; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include '../includes/footeradmin.php'; ?>