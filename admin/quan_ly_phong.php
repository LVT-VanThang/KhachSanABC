<?php
session_start();
require_once '../classes/AdminAuth.php';
require_once '../classes/AdminPhong.php';

// Kiểm tra login
$auth = new AdminAuth();
$auth->kiemTraDangNhap();

// Khởi tạo đối tượng
$adminPhong = new AdminPhong();

include '../includes/headeradmin.php';

// Xử lý Xóa
if (isset($_GET['action']) && $_GET['action'] == 'xoa' && isset($_GET['id'])) {
    $ketQua = $adminPhong->xoaLoaiPhong($_GET['id']);
    if ($ketQua === true) {
        echo "<script>alert('Đã xóa!'); window.location.href='quan_ly_phong.php';</script>";
    } else {
        echo "<script>alert('$ketQua'); window.location.href='quan_ly_phong.php';</script>";
    }
}

// Lấy danh sách (OOP)
$danhSach = $adminPhong->layDanhSachLoaiPhong();
?>

<main class="container page-padding">
    <div class="d-flex justify-between align-center mb-20">
        <h1 class="tieu-de-muc" style="margin:0;">Quản lý Loại Phòng</h1>
        <a href="them_sua_phong.php" class="btn-big-cta"><i class="fas fa-plus"></i> Thêm mới</a>
    </div>

    <div class="table-card">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hình ảnh</th>
                    <th>Tên phòng</th>
                    <th>Giá / Đêm</th>
                    <th>Thông tin</th>
                    <th>Tổng SL</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($danhSach as $row): 
                    $img = !empty($row['anh_dai_dien']) ? 'data:image/jpeg;base64,'.base64_encode($row['anh_dai_dien']) : '../images/no-image.jpg';
                    $busy = ($row['dang_o'] > 0);
                ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td><img src="<?php echo $img; ?>" style="width:80px;height:60px;object-fit:cover;border-radius:4px;"></td>
                    <td>
                        <b style="font-size:1.1rem;"><?php echo $row['ten_loai']; ?></b><br>
                        <small class="text-muted">Đang ở: <?php echo $row['dang_o']; ?></small>
                    </td>
                    <td style="color:var(--warning);font-weight:bold;"><?php echo number_format($row['gia_tien']); ?>đ</td>
                    <td class="text-muted"><i class="fas fa-user"></i> <?php echo $row['suc_chua']; ?> | <i class="fas fa-bed"></i> <?php echo $row['so_giuong']; ?></td>
                    <td><span class="badge badge-info"><?php echo $row['tong']; ?> phòng</span></td>
                    <td>
                        <?php if($busy): ?>
                            <span class="action-btn btn-gray"><i class="fas fa-trash"></i></span>
                        <?php else: ?>
                            <a href="them_sua_phong.php?id=<?php echo $row['id']; ?>" class="action-btn btn-orange"><i class="fas fa-edit"></i></a>
                            <a href="?action=xoa&id=<?php echo $row['id']; ?>" class="action-btn btn-red" onclick="return confirm('Xóa?')"><i class="fas fa-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include '../includes/footeradmin.php'; ?>