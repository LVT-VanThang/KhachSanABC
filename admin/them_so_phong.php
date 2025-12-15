<?php
session_start();
require_once '../classes/AdminAuth.php';
require_once '../classes/AdminPhong.php';

$auth = new AdminAuth();
$auth->kiemTraDangNhap();

$adminPhong = new AdminPhong();
include '../includes/headeradmin.php';

$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$data = ['so_phong'=>'','loai_phong_id'=>'','tang'=>'','trang_thai'=>'Sẵn sàng'];
$isEdit = false;

// Lấy thông tin cũ nếu sửa
if ($id > 0) {
    $oldData = $adminPhong->layChiTietSoPhong($id);
    if($oldData) { $data = $oldData; $isEdit = true; }
}

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $res = $adminPhong->luuSoPhong($_POST);
    if ($res === true) echo "<script>window.location.href='quan_ly_so_phong.php';</script>";
    else echo "<script>alert('$res');</script>";
}

// Lấy danh sách loại phòng cho Select box
$listLoai = $adminPhong->layDanhSachLoaiPhong();
?>

<main class="container page-padding">
    <div class="form-box" style="max-width:600px; margin:0 auto; padding:30px;">
        <h2 class="form-header"><?php echo $isEdit ? "SỬA PHÒNG $data[so_phong]" : "THÊM PHÒNG MỚI"; ?></h2>
        
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <div class="form-group">
                <label class="form-label">Số phòng:</label>
                <input type="text" name="so_phong" class="form-control" value="<?php echo htmlspecialchars($data['so_phong']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Loại phòng:</label>
                <select name="loai_phong_id" class="form-control" required>
                    <?php foreach($listLoai as $l): ?>
                        <option value="<?php echo $l['id']; ?>" <?php if($data['loai_phong_id']==$l['id']) echo 'selected';?>><?php echo $l['ten_loai'];?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Tầng:</label>
                <input type="number" name="tang" class="form-control" value="<?php echo $data['tang']; ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Trạng thái:</label>
                <select name="trang_thai" class="form-control">
                    <?php foreach(['Sẵn sàng','Đang ở','Đang dọn','Bảo trì'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php if($data['trang_thai']==$s) echo 'selected';?>><?php echo $s; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="text-center mt-3">
                <a href="quan_ly_so_phong.php" class="btn btn-cancel">Hủy</a>
                <button type="submit" class="btn btn-submit" style="width:auto;">LƯU LẠI</button>
            </div>
        </form>
    </div>
</main>
<?php include '../includes/footeradmin.php'; ?>