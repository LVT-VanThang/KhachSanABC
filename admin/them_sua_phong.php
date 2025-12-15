<?php
session_start();
require_once '../classes/AdminAuth.php';
require_once '../classes/AdminPhong.php';

$auth = new AdminAuth();
$auth->kiemTraDangNhap();

$adminPhong = new AdminPhong();
include '../includes/headeradmin.php';

$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$isEdit = false;
$data = ['ten_loai'=>'','gia_tien'=>'','suc_chua'=>'','so_giuong'=>'','huong_nhin'=>'Biển','mo_ta'=>''];

// Lấy dữ liệu cũ
if ($id > 0) {
    $res = $adminPhong->layChiTietLoai($id);
    if ($res) { $data = $res; $isEdit = true; }
}

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Thêm ID vào mảng POST để gửi cho hàm
    $_POST['id'] = $id; 
    if($adminPhong->luuLoaiPhongFull($_POST, $_FILES)) {
        echo "<script>alert('Thành công!'); window.location.href='quan_ly_phong.php';</script>";
    } else {
        echo "<script>alert('Lỗi hệ thống!');</script>";
    }
}
?>

<main class="container page-padding">
    <div class="form-box" style="max-width:800px; margin:0 auto; padding:30px;">
        <h2 class="form-header"><?php echo $isEdit ? "CẬP NHẬT LOẠI PHÒNG" : "THÊM LOẠI PHÒNG MỚI"; ?></h2>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="form-group">
                <label class="form-label">Tên loại phòng:</label>
                <input type="text" name="ten_loai" class="form-control" value="<?php echo htmlspecialchars($data['ten_loai']); ?>" required>
            </div>

            <div class="d-flex gap-20">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Giá (VNĐ):</label>
                    <input type="number" name="gia_tien" class="form-control" value="<?php echo $data['gia_tien']; ?>" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Sức chứa:</label>
                    <input type="number" name="suc_chua" class="form-control" value="<?php echo $data['suc_chua']; ?>" required>
                </div>
            </div>

            <div class="d-flex gap-20">
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Số giường:</label>
                    <input type="number" name="so_giuong" class="form-control" value="<?php echo $data['so_giuong']; ?>" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label class="form-label">Hướng nhìn:</label>
                    <select name="huong_nhin" class="form-control">
                        <?php foreach(['Biển','Thành phố','Sân vườn','Hồ bơi','Khác'] as $v): ?>
                            <option value="<?php echo $v; ?>" <?php if($data['huong_nhin']==$v) echo 'selected';?>><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Ảnh đại diện:</label>
                <input type="file" name="anh_dai_dien" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">Mô tả:</label>
                <textarea name="mo_ta" class="form-control" rows="4"><?php echo htmlspecialchars($data['mo_ta']); ?></textarea>
            </div>

            <div class="text-center mt-3">
                <a href="quan_ly_phong.php" class="btn btn-cancel">Hủy</a>
                <button type="submit" class="btn btn-submit" style="width:auto;">LƯU LẠI</button>
            </div>
        </form>
    </div>
</main>
<?php include '../includes/footeradmin.php'; ?>