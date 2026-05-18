<?php
//cần chỉnh htai đang bị thiếu, ttona đhang
session_start(); // Khởi tạo session để lấy dữ liệu đơn hàng vừa đặt
require_once __DIR__ . '/db_config.php';


$order = null;


if (isset($_SESSION['order_info'])) {
    $order = $_SESSION['order_info'];
}


elseif (isset($_GET['id'])) {

    $order_id = $_GET['id'];

    // Lấy thông tin đơn hàng
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order_db = $stmt->fetch();

    // Lấy sản phẩm trong đơn hàng
    $stmt_items = $pdo->prepare("
        SELECT oi.*, p.name 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");

    $stmt_items->execute([$order_id]);
    $items = $stmt_items->fetchAll();

    if ($order_db) {

        $order = [
            'order_id' => $order_db['id'],
            'name' => $order_db['customer_name'] ?? '---',
            'phone' => $order_db['customer_phone'] ?? '---',
            'address' => $order_db['customer_address'] ?? '---',
            'order_time' => $order_db['order_date'],
            'delivery_time' => '---',
            'total' => $order_db['total_amount'] ?? 0,
            'shipping' => $order_db['shipping_fee'] ?? 0,
            'cart' => $items
        ];
    }
}

// 👉 nếu không có id thì dùng session (user)
elseif (isset($_SESSION['order_info'])) {
    $order = $_SESSION['order_info'];
}

// 👉 nếu vẫn không có → báo lỗi
if (!$order) {
    echo "<h2 style='text-align:center;margin-top:50px;'>Không tìm thấy đơn hàng ❌</h2>";
    exit();
}
?>



<!DOCTYPE html> <!-- Khai báo tài liệu HTML5 -->
<html lang="vi"> <!-- Đặt ngôn ngữ trang là tiếng Việt -->
<head> <!-- Phần đầu trang -->
<meta charset="UTF-8"> <!-- Thiết lập bảng mã UTF-8 để hiển thị tiếng Việt -->
<meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Giúp trang hiển thị responsive trên điện thoại -->
<title>Đặt hàng thành công</title> <!-- Tiêu đề tab trình duyệt -->

<link rel="stylesheet" href="css/style.css"> <!-- Liên kết file CSS chính -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <!-- Liên kết thư viện icon Font Awesome -->

<style>
 body {
    background:
        radial-gradient(circle at 15% 25%, rgba(255, 214, 230, 0.9), transparent 55%),
        radial-gradient(circle at 85% 20%, rgba(255, 241, 200, 0.9), transparent 55%),
        radial-gradient(circle at 50% 80%, rgba(255, 220, 235, 0.8), transparent 60%);
    
    background-color: #fff7ec;
    background-repeat: no-repeat;
    background-attachment: fixed;
}

.container {
    max-width: 800px;
    margin: 50px auto;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.success {
    text-align: center;
    color: #28a745;
}

.success i {
    font-size: 60px;
}

.section {
    margin-top: 20px;
    padding: 15px;
    border-radius: 8px;
    background: #fafafa;
}

.section h3 {
    margin-bottom: 10px;
    color: #333;
}

.item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.total {
    font-weight: bold;
    color: #e91e63;
    font-size: 18px;
}

.btn-home {
    display: block;
    margin: 20px auto 0;
    text-align: center;
    padding: 12px;
    width: 200px;
    background: #e91e63;
    color: white;
    border-radius: 6px;
    text-decoration: none;
}
</style>

</head>
<body>

<div class="container">

    <div class="success">
        <i class="fas fa-check-circle"></i>
        <h2>ĐẶT HÀNG THÀNH CÔNG 🎉</h2>
        <p>Mã đơn hàng: <b>#<?php echo $order['order_id']; ?></b></p>
    </div>

    <!-- 👤 THÔNG TIN KHÁCH -->
    <div class="section">
        <h3>Thông tin khách hàng</h3>
        <p><b>Họ tên:</b> <?php echo $order['name']; ?></p>
        <p><b>SĐT:</b> <?php echo $order['phone']; ?></p>
        <p><b>Địa chỉ:</b> <?php echo $order['address']; ?></p>
    </div>

    <!-- ⏰ THỜI GIAN -->
    <div class="section">
        <h3>Thời gian</h3>
        <p><b>Đặt lúc:</b> <?php echo $order['order_time']; ?></p>
        <p><b>Dự kiến nhận:</b> <?php echo $order['delivery_time']; ?></p>
    </div>

    <!-- 🛒 SẢN PHẨM -->
    <div class="section">
        <h3>Sản phẩm đã mua</h3>



    <?php if (!empty($order['cart'])): ?>
        <?php foreach ($order['cart'] as $item): ?>
            <div class="item">
                <div>
                    <b><?php echo $item['name']; ?></b><br>
                    <?php echo number_format($item['price'],0,',','.'); ?>đ x <?php echo $item['quantity']; ?>
                </div>
                <div>
                    <?php echo number_format($item['price'] * $item['quantity'],0,',','.'); ?>đ
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Không có sản phẩm</p>
    <?php endif; ?>

</div>

    <!-- 💰 THANH TOÁN -->
    <div class="section">
        <h3>Thanh toán</h3>

        <p>Tiền hàng: <?php echo number_format($order['total'] - $order['shipping'],0,',','.'); ?>đ</p>
        <p>Phí ship: <?php echo number_format($order['shipping'],0,',','.'); ?>đ</p>

        <p class="total">
            Tổng cộng: <?php echo number_format($order['total'],0,',','.'); ?>đ
        </p>
    </div>

    <a href="index.php" class="btn-home">
        <i class="fas fa-home"></i> HOME
    </a>

</div>

</body>
</html> 