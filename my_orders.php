<?php
//trang này dành cho user xem lsu đhang, file mới bổ sung trc 10-4
session_start(); // Khởi tạo session để sử dụng thông tin đăng nhập của người dùng
include 'db_config.php'; // Nhúng file cấu hình và kết nối cơ sở dữ liệu

if (!isset($_SESSION['user_id'])) { // Kiểm tra nếu chưa đăng nhập
    echo "Bạn cần đăng nhập!"; // Hiển thị thông báo yêu cầu đăng nhập
    exit(); // Dừng chương trình
}

// 👑 ADMIN
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) { // Nếu người dùng là admin
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC"); // Lấy tất cả đơn hàng, sắp xếp mới nhất trước
} 
// 👤 USER
else { // Nếu là người dùng thường
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC"); // Chuẩn bị câu lệnh lấy đơn hàng theo user_id
    $stmt->execute([$_SESSION['user_id']]); // Thực thi câu lệnh với id của user đang đăng nhập
}

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC); // Lấy toàn bộ kết quả truy vấn đơn hàng dưới dạng mảng kết hợp (mỗi phần tử là 1 đơn hàng)
?> <!-- Kết thúc khối PHP và chuyển sang HTML -->
<link rel="stylesheet" href="css/style.css"> <!-- Liên kết file CSS để tạo kiểu giao diện -->

<div class="orders-container"> <!-- Thẻ div bao toàn bộ khu vực hiển thị đơn hàng, dùng để style -->

<h2>🧾 Lịch sử đơn hàng</h2> <!-- Tiêu đề hiển thị danh sách lịch sử đơn hàng -->

<?php foreach ($orders as $order): ?>
    <div class="order-box">

        <div class="order-title">Đơn #<?= $order['id'] ?></div>

        Tên: <?= $order['customer_name'] ?><br>
        SĐT: <?= $order['customer_phone'] ?><br>

        📅 Ngày đặt: <?= $order['order_date'] ?><br>
        🚚 Ngày nhận: <?= $order['delivery_time'] ?><br>

        💰 Tổng: <?= number_format($order['total_amount']) ?>đ<br>

        Trạng thái: 
        <span class="order-status"><?= $order['status'] ?></span><br>


        <?php if ($order['status'] == 'Mới'): ?>
            <a href="cancel_order.php?id=<?= $order['id'] ?>" 
               onclick="return confirm('Bạn có chắc muốn hủy đơn này?')"
               class="cancel-btn">
               ❌ Hủy đơn
            </a>
        <?php endif; ?>

    </div>
<?php endforeach; ?>

</div>
