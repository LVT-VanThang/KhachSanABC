<?php
session_start();
require_once '../classes/AdminAuth.php';
require_once '../classes/AdminDonHang.php';

$auth = new AdminAuth();
$auth->kiemTraDangNhap();

$adminDon = new AdminDonHang();
include __DIR__ . '/../includes/headeradmin.php'; 

// Lấy danh sách đang ở
$listDangO = $adminDon->layDanhSachDangO();
?>

<main class="container page-padding">
    
    <div class="d-flex justify-between align-center mb-20">
        <div>
            <h1 class="tieu-de-muc" style="margin:0;">Khách đang lưu trú</h1>
            <p style="color:#666;">Quản lý danh sách khách và thủ tục trả phòng</p>
        </div>
        <a href="quan_ly_don.php" class="btn btn-cancel"><i class="fas fa-arrow-left"></i> Về trang chủ</a>
    </div>

    <div class="table-card">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách Hàng</th>
                    <th>Phòng / Loại</th>
                    <th>Thời gian</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($listDangO)): foreach ($listDangO as $row): 
                    $idDon = $row['id'];
                    $dsPhong = $adminDon->layPhongCuaDon($idDon);
                ?>
                    <tr>
                        <td><strong>#<?php echo $idDon; ?></strong></td>
                        <td>
                            <b><?php echo htmlspecialchars($row['ten_khach']); ?></b><br>
                            <span class="text-muted"><i class="fas fa-phone-alt"></i> <?php echo $row['sdt_khach']; ?></span>
                        </td>
                        <td>
                            <div style="color:var(--info); font-weight:bold;"><?php echo $row['ten_loai']; ?></div>
                            <div class="mt-2">
                                <?php foreach($dsPhong as $p): ?>
                                    <span class="badge badge-success"><i class="fas fa-key"></i> P.<?php echo $p; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td>
                            <div style="color:var(--success);">Check-in: <?php echo date('d/m/Y', strtotime($row['ngay_nhan'])); ?></div>
                            <div style="color:var(--warning);">Dự kiến out: <?php echo date('d/m/Y', strtotime($row['ngay_tra'])); ?></div>
                        </td>
                        <td class="text-center">
                            <a href="thanh_toan.php?id_don=<?php echo $idDon; ?>" 
                            class="btn-big-cta" style="background: #e74c3c; color: white; padding: 8px 15px; font-size: 0.9rem; text-decoration: none; display: inline-block;">
                                <i class="fas fa-file-invoice-dollar"></i> Thanh toán
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan='5' class='text-center' style='padding:50px;'>Hiện không có khách nào đang lưu trú.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../includes/footeradmin.php'; ?>