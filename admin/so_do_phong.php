<?php
session_start();
// 1. NHÚNG CÁC CLASS
require_once '../classes/AdminAuth.php';
require_once '../classes/AdminPhong.php';
require_once '../classes/AdminDonHang.php';

// 2. KIỂM TRA ĐĂNG NHẬP
$auth = new AdminAuth();
$auth->kiemTraDangNhap();

// 3. KHỞI TẠO ĐỐI TƯỢNG
$adminPhong = new AdminPhong();
$adminDon = new AdminDonHang();

include '../includes/headeradmin.php';

// =================================================================================
// 4. CẤU HÌNH TIMELINE (14 NGÀY) - Logic PHP giữ nguyên
// =================================================================================
$soNgayHienThi = 14;
$homNay = date('Y-m-d');
$dsNgay = [];
for ($i = 0; $i < $soNgayHienThi; $i++) {
    $dsNgay[] = date('Y-m-d', strtotime($homNay . " + $i days"));
}
$start = $dsNgay[0];
$end = end($dsNgay);

// =================================================================================
// 5. XỬ LÝ LỌC & LẤY DỮ LIỆU TỪ CLASS
// =================================================================================
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$loaiId = isset($_GET['loai_phong_id']) ? (int)$_GET['loai_phong_id'] : 0;

// A. Lấy danh sách phòng (Gọi hàm từ AdminPhong)
$dsPhong = $adminPhong->layDanhSachPhongTimeline($keyword, $loaiId);

// B. Lấy dữ liệu booking (Gọi hàm từ AdminDonHang)
$rawBookings = $adminDon->layDuLieuSoDo($start, $end);

// C. Map dữ liệu vào mảng 2 chiều [phong_id][ngay] (Logic xử lý mảng giữ nguyên)
$dataMap = [];
if (!empty($rawBookings)) {
    foreach ($rawBookings as $row) {
        $pid = $row['pid'];
        // Tạo khoảng ngày từ check-in đến check-out
        $period = new DatePeriod(
            new DateTime($row['ngay_nhan']),
            new DateInterval('P1D'),
            (new DateTime($row['ngay_tra']))->modify('+1 day')
        );

        foreach ($period as $dt) {
            $d = $dt->format('Y-m-d');
            if (in_array($d, $dsNgay)) {
                $dataMap[$pid][$d] = [
                    'khach' => $row['ten_khach'],
                    'trang_thai' => $row['trang_thai']
                ];
            }
        }
    }
}

// Lấy danh sách loại phòng cho dropdown bộ lọc
$listLoai = $adminPhong->layDanhSachLoaiPhong();
?>

<main class="container page-padding">
    
    <div class="d-flex justify-between align-center mb-20">
        <div>
            <h1 class="tieu-de-muc" style="margin:0;">Sơ đồ phòng (Timeline)</h1>
            <p class="text-muted">Theo dõi trạng thái phòng trong 14 ngày tới</p>
        </div>
        <div class="text-right">
            <span class="text-muted">Hôm nay:</span> 
            <strong style="font-size:1.1rem; color:var(--text-dark);"><?php echo date('d/m/Y'); ?></strong>
        </div>
    </div>

    <div class="table-card mb-20" style="padding: 20px; overflow: visible;">
        <form method="GET" class="d-flex align-center gap-20" style="flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" 
                       class="form-control" placeholder="Tìm theo số phòng...">
            </div>
            
            <div style="flex: 1; min-width: 200px;">
                <select name="loai_phong_id" class="form-control">
                    <option value="0">-- Tất cả loại phòng --</option>
                    <?php foreach($listLoai as $l): ?>
                        <option value="<?php echo $l['id']; ?>" <?php if($loaiId == $l['id']) echo 'selected'; ?>>
                            <?php echo $l['ten_loai']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-big-cta" style="height: 45px;">
                <i class="fas fa-filter"></i> Lọc dữ liệu
            </button>
            
            <?php if($keyword || $loaiId): ?>
                <a href="so_do_phong.php" class="btn btn-outline" style="height: 45px;">
                    <i class="fas fa-undo"></i> Xóa lọc
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="timeline-container">
        <table class="tl-table">
            <thead>
                <tr>
                    <th style="min-width: 150px; z-index: 20;">PHÒNG</th>
                    <?php foreach ($dsNgay as $ngay): ?>
                        <th class="<?php echo ($ngay == $homNay) ? 'is-today' : ''; ?>">
                            <?php echo date('d/m', strtotime($ngay)); ?><br>
                            <small class="text-muted" style="font-weight: normal;">
                                <?php echo date('D', strtotime($ngay)); ?>
                            </small>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($dsPhong)): ?>
                    <?php foreach ($dsPhong as $p): ?>
                        <tr>
                            <th>
                                <div style="font-weight: 700; color: var(--text-dark); font-size: 1rem;">
                                    P.<?php echo $p['so_phong']; ?>
                                </div>
                                <div class="text-muted" style="font-size: 0.8rem; font-weight: normal;">
                                    <?php echo $p['ten_loai']; ?>
                                </div>
                            </th>

                            <?php foreach ($dsNgay as $ngay): ?>
                                <td class="<?php echo ($ngay == $homNay) ? 'is-today' : ''; ?>">
                                    <?php
                                    // 1. Ưu tiên hiển thị đơn đặt phòng
                                    if (isset($dataMap[$p['id']][$ngay])) {
                                        $info = $dataMap[$p['id']][$ngay];
                                        $status = $info['trang_thai'];
                                        
                                        // Class màu sắc theo trạng thái đơn
                                        $bgClass = 'bg-booked'; // Mặc định: Đã đặt
                                        if (stripos($status, 'ở') !== false) $bgClass = 'bg-active'; // Đang ở
                                        if (stripos($status, 'chờ') !== false) $bgClass = 'bg-wait'; // Chờ duyệt

                                        echo "<div class='cell-data $bgClass' title='Khách: {$info['khach']} - ($status)'>";
                                        echo "<span style='white-space:nowrap; overflow:hidden; text-overflow:ellipsis; width:100%; display:block;'>";
                                        echo $info['khach'];
                                        echo "</span>";
                                        echo "</div>";
                                    } 
                                    // 2. Nếu không có đơn, check trạng thái phòng (Bảo trì/Dọn dẹp)
                                    // Chỉ hiện icon ở cột "Hôm nay" để đỡ rối
                                    elseif ($ngay == $homNay) {
                                        if ($p['trang_thai'] == 'Bảo trì') {
                                            echo "<span class='bg-maintenance'><i class='fas fa-tools'></i></span>";
                                        } elseif ($p['trang_thai'] == 'Đang dọn') {
                                            echo "<span class='bg-cleaning'><i class='fas fa-broom'></i></span>";
                                        }
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo count($dsNgay) + 1; ?>" style="padding: 30px; color: #999;">
                            Không tìm thấy phòng nào phù hợp bộ lọc.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-center gap-20 mt-3" style="flex-wrap: wrap;">
        <div class="d-flex align-center gap-10">
            <span class="cell-data bg-booked" style="width: 20px; height: 20px; border-radius: 4px;"></span>
            <small>Đã đặt (Giữ chỗ)</small>
        </div>
        <div class="d-flex align-center gap-10">
            <span class="cell-data bg-active" style="width: 20px; height: 20px; border-radius: 4px;"></span>
            <small>Đang ở (Check-in)</small>
        </div>
        <div class="d-flex align-center gap-10">
            <span class="cell-data bg-wait" style="width: 20px; height: 20px; border-radius: 4px;"></span>
            <small>Chờ xác nhận</small>
        </div>
        <div class="d-flex align-center gap-10">
            <span class="bg-maintenance" style="width: 20px; height: 20px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-tools" style="font-size:10px;"></i></span>
            <small>Bảo trì</small>
        </div>
        <div class="d-flex align-center gap-10">
            <span class="bg-cleaning" style="width: 20px; height: 20px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-broom" style="font-size:10px;"></i></span>
            <small>Đang dọn</small>
        </div>
    </div>

</main>

<?php include '../includes/footeradmin.php'; ?>