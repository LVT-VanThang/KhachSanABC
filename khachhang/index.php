<?php 
// 1. Cấu hình & Class
$tieuDeTrang = "Trang chủ - Khách sạn ABC Luxury";
require_once '../classes/LoaiPhong.php'; 

// Khởi tạo đối tượng để lấy dữ liệu phòng
$loaiPhongObj = new LoaiPhong();
// Lấy danh sách phòng, sau đó ta sẽ chỉ lấy 3 phòng đầu tiên để hiển thị trang chủ
$dsPhong = $loaiPhongObj->timKiemPhong([]); 
$phongNoiBat = array_slice($dsPhong, 0, 3); // Lấy 3 phần tử đầu

include '../includes/headerkhachhang.php'; 
?>

<main>
    <section class="banner-intro">
        <video autoplay muted loop playsinline class="video-bg">
            <source src="../images/videokhachsan.mp4" type="video/mp4">
        </video>
        <div class="overlay"></div>
        <div class="intro-content">
            <span class="sub-title">Chào mừng đến với</span>
            <h1 class="intro-title">ABC LUXURY HOTEL</h1>
            <p class="intro-desc">
                Nơi hội tụ tinh hoa kiến trúc và dịch vụ đẳng cấp 5 sao.<br>
                Hãy để chúng tôi kể câu chuyện về kỳ nghỉ mơ ước của bạn.
            </p>
            <div class="btn-group">
                <a href="danh_sach_phong.php" class="btn-big-cta">Đặt Phòng Ngay</a>
            </div>
        </div>
    </section>

    <section class="container page-padding text-center">
        <h2 class="tieu-de-muc">Trải Nghiệm Sự Khác Biệt</h2>
        <p class="about-desc" style="max-width: 800px; margin: 0 auto 40px;">
            Tọa lạc tại vị trí "tựa sơn hướng thủy", Khách sạn ABC không chỉ là nơi lưu trú, mà là một tác phẩm nghệ thuật. 
            Với hệ thống phòng ốc hiện đại, hồ bơi vô cực nhìn thẳng ra biển và ẩm thực tinh tế, chúng tôi cam kết mang lại những giây phút thăng hoa cảm xúc.
        </p>
        
        <div class="features-grid">
            <div class="service-card-home">
                <div class="icon-wrapper"><i class="fas fa-swimming-pool"></i></div>
                <h3>Hồ Bơi Vô Cực</h3>
                <p>Thư giãn trong làn nước xanh mát với tầm nhìn bao quát đại dương.</p>
            </div>
            <div class="service-card-home">
                <div class="icon-wrapper"><i class="fas fa-utensils"></i></div>
                <h3>Nhà Hàng 5 Sao</h3>
                <p>Thưởng thức tinh hoa ẩm thực Á - Âu từ những đầu bếp hàng đầu.</p>
            </div>
            <div class="service-card-home">
                <div class="icon-wrapper"><i class="fas fa-spa"></i></div>
                <h3>Spa & Wellness</h3>
                <p>Liệu trình massage trị liệu giúp tái tạo năng lượng và cân bằng cơ thể.</p>
            </div>
            <div class="service-card-home">
                <div class="icon-wrapper"><i class="fas fa-concierge-bell"></i></div>
                <h3>Phục Vụ 24/7</h3>
                <p>Đội ngũ nhân viên chuyên nghiệp luôn sẵn sàng hỗ trợ bạn mọi lúc.</p>
            </div>
        </div>
    </section>

    <section class="bg-gray page-padding">
        <div class="container">
            <div class="section-header text-center mb-50">
                <h2 class="tieu-de-muc">Phòng Nghỉ Đẳng Cấp</h2>
            </div>

            <div class="room-grid-home">
                <?php if (!empty($phongNoiBat)): ?>
                    <?php foreach ($phongNoiBat as $room): 
                        $anh = !empty($room['anh_dai_dien']) ? 'data:image/jpeg;base64,' . base64_encode($room['anh_dai_dien']) : '../images/no-image.jpg';
                    ?>
                    <div class="room-card-home">
                        <div class="room-img-box">
                            <img src="<?php echo $anh; ?>" alt="<?php echo $room['ten_loai']; ?>">
                            <div class="price-badge"><?php echo number_format($room['gia_tien'], 0, ',', '.'); ?>đ <small>/đêm</small></div>
                        </div>
                        <div class="room-content-box">
                            <div class="room-meta">
                                <span><i class="fas fa-user"></i> <?php echo $room['suc_chua']; ?> người</span>
                                <span><i class="fas fa-bed"></i> <?php echo $room['so_giuong']; ?> giường</span>
                            </div>
                            <h3><a href="dat_phong.php?id=<?php echo $room['id']; ?>&checkin=<?php echo date('Y-m-d'); ?>&checkout=<?php echo date('Y-m-d', strtotime('+1 day')); ?>"><?php echo $room['ten_loai']; ?></a></h3>
                            <p class="room-desc"><?php echo substr($room['mo_ta'], 0, 80) . '...'; ?></p>
                            <a href="dat_phong.php?id=<?php echo $room['id']; ?>&checkin=<?php echo date('Y-m-d'); ?>&checkout=<?php echo date('Y-m-d', strtotime('+1 day')); ?>" class="btn-link">Đặt ngay <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">Đang cập nhật danh sách phòng...</p>
                <?php endif; ?>
            </div>
            
            <div class="text-center" style="margin: 60px 0;">
             <a href="danh_sach_phong.php" class="btn-primary-outline">Xem Tất Cả Phòng</a>
            </div>
        </div>
    </section>

    <section class="container page-padding">
        <h2 class="tieu-de-muc text-center">Khách Hàng Nói Gì</h2>
        <div class="testimonials-grid">
            <div class="testi-item">
                <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p class="testi-text">"Một trải nghiệm tuyệt vời! Phòng ốc sạch sẽ, view biển cực đẹp. Nhân viên thì vô cùng thân thiện. Chắc chắn tôi sẽ quay lại."</p>
                <div class="testi-author">
                    <img src="https://ui-avatars.com/api/?name=Nguyen+An&background=random" alt="User">
                    <div><strong>Nguyễn Văn An</strong><span>Du khách từ Hà Nội</span></div>
                </div>
            </div>
            <div class="testi-item">
                <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p class="testi-text">"Dịch vụ Spa ở đây thật sự đẳng cấp. Đồ ăn sáng buffet rất đa dạng và ngon miệng. Cảm ơn ABC Hotel vì kỳ nghỉ đáng nhớ."</p>
                <div class="testi-author">
                    <img src="https://ui-avatars.com/api/?name=Tran+My&background=random" alt="User">
                    <div><strong>Trần Thị My</strong><span>Du khách từ TP.HCM</span></div>
                </div>
            </div>
            <div class="testi-item">
                <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p class="testi-text">"Gia đình tôi đã có những giây phút hạnh phúc tại đây. Hồ bơi vô cực là điểm nhấn tuyệt vời nhất."</p>
                <div class="testi-author">
                    <img src="https://ui-avatars.com/api/?name=Le+Bao&background=random" alt="User">
                    <div><strong>Lê Quốc Bảo</strong><span>Du khách từ Đà Nẵng</span></div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footerkhachhang.php'; ?>