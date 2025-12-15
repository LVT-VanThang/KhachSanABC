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

// --- PHẦN XỬ LÝ TÌM KIẾM ---
// 1. Lấy dữ liệu danh mục để đổ vào Dropdown
$dsLoaiPhong = $adminPhong->layDanhSachLoaiPhong();
$dsTang = $adminPhong->layDanhSachTang();

// 2. Nhận từ khóa từ URL (nếu người dùng bấm tìm)
$kw = isset($_GET['kw']) ? trim($_GET['kw']) : '';
$lid = isset($_GET['lid']) ? (int)$_GET['lid'] : 0;
$ftang = isset($_GET['ftang']) ? (int)$_GET['ftang'] : 0;

// 3. Gọi hàm lấy danh sách với tham số lọc
$result = $adminPhong->layDanhSachSoPhong($kw, $lid, $ftang);
?>

<main class="container page-padding">
    <div class="d-flex justify-between align-center mb-20">
        <div>
            <h1 class="tieu-de-muc" style="margin:0;">Quản lý Số phòng</h1>
            <p class="text-muted">Danh sách tất cả các phòng trong khách sạn</p>
        </div>
        <a href="them_so_phong.php" class="btn-big-cta"><i class="fas fa-plus"></i> Thêm phòng mới</a>
    </div>

    <div class="table-card mb-20" style="padding: 20px; background: #f8f9fa;">
        <form action="" method="GET" class="d-flex align-center" style="gap: 15px; flex-wrap: wrap;">
            
            <div style="flex: 1; min-width: 200px;">
                <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Số phòng:</label>
                <input type="text" name="kw" class="form-control" placeholder="Nhập số phòng..." value="<?php echo htmlspecialchars($kw); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="min-width: 200px;">
                <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Loại phòng:</label>
                <select name="lid" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="0">-- Tất cả loại --</option>
                    <?php foreach ($dsLoaiPhong as $l): ?>
                        <option value="<?php echo $l['id']; ?>" <?php echo ($lid == $l['id']) ? 'selected' : ''; ?>>
                            <?php echo $l['ten_loai']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="min-width: 150px;">
                <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 5px;">Tầng:</label>
                <select name="ftang" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="0">-- Tất cả --</option>
                    <?php foreach ($dsTang as $t): ?>
                        <option value="<?php echo $t['tang']; ?>" <?php echo ($ftang == $t['tang']) ? 'selected' : ''; ?>>
                            Tầng <?php echo $t['tang']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="align-self: flex-end;">
                <button type="submit" class="btn-orange" style="padding: 9px 20px; border: none; border-radius: 4px; color: white; cursor: pointer;">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <?php if($kw != '' || $lid != 0 || $ftang != 0): ?>
                    <a href="quan_ly_so_phong.php" class="btn-gray" style="padding: 9px 15px; text-decoration: none; border-radius: 4px; background: #ddd; color: #333; margin-left: 5px;">
                        <i class="fas fa-undo"></i> Đặt lại
                    </a>
                <?php endif; ?>
            </div>

        </form>
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
                    <tr><td colspan="5" style="text-align:center; padding:30px; color:#999;">Không tìm thấy phòng nào phù hợp.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include '../includes/footeradmin.php'; ?>
