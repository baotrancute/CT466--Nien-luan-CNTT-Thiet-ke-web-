<?php
//hoàn tất đh, tức tton
session_start();// Khởi động session để lấy dữ liệu giỏ hàng từ $_SESSION//
include 'db_config.php'; // Kết nối Database

$cart = $_SESSION['cart'] ?? []; // Lấy giỏ hàng từ session; nếu chưa có thì gán mảng rỗng//
$message = '';// Biến chứa thông báo (nếu cần)//
$subtotal_amount = 0; // Tổng tạm tính (chưa có phí vận chuyển)//
$shipping_fee = 30000; // Phí vận chuyển cố định
$total_amount = 0;  // Tổng tiền cuối cùng sẽ tính sau//

// 1. Kiểm tra giỏ hàng
if (empty($cart)) {// Nếu giỏ hàng rỗng//
    header("Location: cart.php");// Chuyển người dùng về trang giỏ hàng//
    exit();// Dừng toàn bộ chương trình//
}

// Tính TỔNG TIỀN SẢN PHẨM (Subtotal)
foreach ($cart as $item) {// Lặp qua từng sản phẩm trong giỏ hàng//
    $subtotal_amount += $item['price'] * $item['quantity']; // Cộng dồn: giá * số lượng để tính tổng tạm tính//
}

// Tính TỔNG TIỀN CUỐI CÙNG: Subtotal + Shipping Fee (LOGIC ĐÚNG)
$total_amount = $subtotal_amount + $shipping_fee; // Tổng thanh toán = tạm tính + phí vận chuyển//

// 2. Xử lý khi Form được gửi (Đặt hàng)
if ($_SERVER["REQUEST_METHOD"] == "POST") {// Kiểm tra xem form có được submit bằng phương thức POST không//
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? ''; // Lấy địa chỉ giao hàng//
    $status = 'Mới'; // Trạng thái đơn hàng mặc định là "Mới"//

    if (empty($name) || empty($phone) || empty($address)) {// Kiểm tra các trường bắt buộc có bị bỏ trống hay không//
        $message = "<div style='color: red; text-align: center; font-weight: bold;'>Vui lòng điền đầy đủ thông tin bắt buộc.</div>";// Báo lỗi//
    } else {
        try {
            $pdo->beginTransaction();// Bắt đầu transaction để đảm bảo dữ liệu lưu đồng bộ//

            // 2.1. LƯU ĐƠN HÀNG VÀO BẢNG 'orders'
                $user_id = $_SESSION['user_id'] ?? NULL;

                    $sql_order = "INSERT INTO orders 
                    (user_id, customer_name, customer_email, customer_phone, customer_address, shipping_fee, total_amount, order_date, delivery_time, status)
                    VALUES (:user_id, :name, :email, :phone, :address, :shipping_fee, :total, NOW(), :delivery_time, :status)";
                          // Câu SQL thêm đơn hàng//
            
            $stmt_order = $pdo->prepare($sql_order);// Chuẩn bị câu truy vấn để tránh SQL Injection//
            $stmt_order->execute([ // Thực thi truy vấn với dữ liệu thật//
                ':user_id' => $user_id,
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':address' => $address,
                ':shipping_fee' => $shipping_fee, 
                ':total' => $total_amount, // Tổng thanh toán//
                ':delivery_time' => date('Y-m-d H:i:s', strtotime('+3 days')),
                ':status' => $status
            ]);
            
            $order_id = $pdo->lastInsertId();// Lấy ID của đơn hàng vừa được thêm vào bảng orders//

            // 2.2. LƯU CHI TIẾT ĐƠN HÀNG (order_items)
            $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                         VALUES (:order_id, :product_id, :quantity, :price)";
                         // Câu SQL thêm chi tiết từng sản phẩm//
            $stmt_item = $pdo->prepare($sql_item);// Chuẩn bị câu truy vấn để chống SQL Injection//

            foreach ($cart as $item) {// Lặp qua tất cả sản phẩm trong giỏ hàng//
                $stmt_item->execute([
                    ':order_id' => $order_id,// Gắn đơn hàng cha//
                    ':product_id' => $item['id'],// ID sản phẩm//
                    ':quantity' => $item['quantity'], // Số lượng//
                    ':price' => $item['price'] // Giá tại thời điểm đặt hàng//
                ]);
            }
            
            $pdo->commit(); // Xác nhận toàn bộ giao dịch - lưu dữ liệu vào database//

            // 2.3. XÓA GIỎ HÀNG 
            unset($_SESSION['cart']);// Xóa giỏ hàng sau khi đặt thành công//

            // 2.4. Chuyển hướng
            $_SESSION['order_success_message'] = 'Đơn hàng của bạn đã được đặt thành công! Mã đơn hàng: #' . $order_id;// Lưu thông báo vào session để trang order_success.php hiển thị//
           $_SESSION['order_info'] = [
                'order_id' => $order_id,
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'total' => $total_amount,
                'shipping' => $shipping_fee,
                'cart' => $cart,
                'order_time' => date('Y-m-d H:i:s'),
                'delivery_time' => date('Y-m-d H:i:s', strtotime('+3 days'))
            ];
            header("Location: order_success.php"); // Chuyển sang trang báo thành công//
            exit();// Dừng chương trình//

        } catch (Exception $e) { // Nếu có lỗi xảy ra trong quá trình đặt hàng//
            $pdo->rollBack();// Hủy toàn bộ transaction để không lưu dữ liệu lỗi//
        $message = "<div style='color: red; text-align: center; font-weight: bold;'>Lỗi đặt hàng: Vui lòng thử lại. Chi tiết: " . $e->getMessage() . "</div>";// Hiển thị thông báo lỗi
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><!-- Giúp trang responsive trên mobile -->
    <title>Tiến Hành Thanh Toán</title><!-- Tiêu đề trang hiển thị trên tab trình duyệt -->
    <link rel="stylesheet" href="css/style.css"><!-- Nhúng file CSS chính của website -->
    <style> /*style css */
    body {
    background:
        radial-gradient(circle at 15% 25%, rgba(255, 214, 230, 0.9), transparent 55%),
        radial-gradient(circle at 85% 20%, rgba(255, 241, 200, 0.9), transparent 55%),
        radial-gradient(circle at 50% 80%, rgba(255, 220, 235, 0.8), transparent 60%);
    
    background-color: #fff7ec;
    background-repeat: no-repeat;
    background-attachment: fixed;
}
  .checkout-container {
    max-width: 700px;
    margin: 40px auto;
    padding: 25px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

/* BOX CHUNG */
.order-summary {
    border: 1px solid #f3c6d3;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    background: #fff5f8;
}

/* INPUT CHUẨN KHÔNG BỊ LỆCH */
.order-summary input,
.order-summary textarea {
    width: 100%;
    padding: 10px;
    margin-top: 8px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    box-sizing: border-box;
    font-size: 14px;
}

/* ITEM PRODUCT */
.product-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.product-info {
    font-size: 14px;
}

.product-price {
    text-align: right;
    color: #e91e63;
}

/* TOTAL */
.total {
    font-size: 18px;
    font-weight: bold;
    color: #e91e63;
}

/* BUTTON CHUẨN */
.btn-group {
    margin-top: 20px;
}

.btn {
    display: block;
    width: 100%;
    padding: 14px;
    margin-top: 10px;
    text-align: center;
    border-radius: 6px;
    font-weight: bold;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-size: 15px;
}

.btn-primary {
    background: #e91e63;
    color: #fff;
}

.btn-warning {
    background: #f2b02d;
    color: #fff;
}

.btn-dark {
    background: #444;
    color: #fff;
}
    </style>
</head>
<body>
<main>
    <div class="checkout-container">
        
        <h2>XÁC NHẬN ĐƠN HÀNG</h2>
        <?php echo $message; ?>
     <form action="checkout.php" method="POST" id="checkout-form">

        <!-- 📍 ĐỊA CHỈ -->
       <div class="order-summary">
    <h3>📍 Địa chỉ nhận hàng</h3>

<input type="text" name="name" placeholder="Họ tên *" required>
<input type="text" name="email" placeholder="Email">
<input type="text" name="phone" placeholder="SĐT *" required>
<textarea name="address" placeholder="Địa chỉ *" required></textarea>
</div>

        <!-- 🛒 SẢN PHẨM -->
        <div class="order-summary">
            <h3>Sản phẩm</h3>

            <?php foreach ($cart as $item): ?>
    <div class="product-item">
        <div class="product-info">
            <b><?php echo htmlspecialchars($item['name']); ?></b><br>
            <?php echo number_format($item['price'], 0, ',', '.'); ?>đ x <?php echo $item['quantity']; ?>
        </div>

        <div class="product-price">
            <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ
        </div>
    </div>
<?php endforeach; ?>

            <hr>
            <p>Tạm tính: <?php echo number_format($subtotal_amount, 0, ',', '.'); ?>đ</p>
        </div>

        <!-- 🚚 VẬN CHUYỂN -->
        <div class="order-summary">
            <h3>Phương thức vận chuyển</h3>
            <p>🚚 Giao hàng nhanh (GHN): 
                <b><?php echo number_format($shipping_fee,0,',','.'); ?>đ</b>
            </p>
        </div>

        <!-- 💳 THANH TOÁN -->
        <div class="payment-method">
    <label>
        <input type="radio" name="payment" value="cod">
        Thanh toán sau khi nhận hàng (ship COD)
    </label>

    <label>
        <input type="radio" name="payment" value="atm">
        Thanh toán qua thẻ ATM ( chưa hỗ trợ )
    </label>

    <label>
        <input type="radio" name="payment" value="credit">
        Thanh toán qua Thẻ tín dụng ngân hàng
    </label>
</div>

        <!-- 💰 TỔNG -->
        <div class="order-summary">

    <p style="display:flex;justify-content:space-between;">
        <span>Tiền hàng:</span>
        <span><?php echo number_format($subtotal_amount, 0, ',', '.'); ?>đ</span>
    </p>

    <p style="display:flex;justify-content:space-between;">
        <span>Phí ship:</span>
        <span><?php echo number_format($shipping_fee, 0, ',', '.'); ?>đ</span>
    </p>

    <hr>

    <p class="total" style="display:flex;justify-content:space-between;">
        <span>Tổng cộng:</span>
        <span><?php echo number_format($total_amount, 0, ',', '.'); ?>đ</span>
    </p>

</div>

      
    <div class="btn-group">
        <button type="submit" class="btn btn-primary" >
            HOÀN TẤT ĐẶT HÀNG
        </button>

        <a href="shop.php" class="btn btn-dark" style = "width:96%;">
            TIẾP TỤC MUA SẮM
        </a>

        <a href="index.php" class="btn btn-dark" style="width:96%;height:18px;border-radius:6px;font-size:15px;background:#444;color:white;text-decoration:none;margin-top:10px;">
            VỀ TRANG CHỦ
        </a>
    </div>

</form>

    </div>
</main>
<script>

</script>
</body>
</html>