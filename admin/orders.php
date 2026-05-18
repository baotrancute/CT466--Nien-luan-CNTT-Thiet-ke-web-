<?php
// file này xem đơn hàng cho admin(ql trang đơn y như các trang admin kia)
session_start(); // Khởi tạo session để sử dụng biến phiên (lưu thông tin đăng nhập người dùng)
include '../db_config.php'; // Nhúng file cấu hình để kết nối đến cơ sở dữ liệu

if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) != 1) { // Kiểm tra nếu chưa đăng nhập hoặc không phải admin
    header("Location: ../index.php"); // Chuyển hướng về trang chủ nếu không đủ quyền
    exit(); // Dừng thực thi chương trình ngay sau khi chuyển hướng
}

if(isset($_POST['update_status'])){

$order_id = $_POST['order_id'];
$status = $_POST['status'];

$stmt = $pdo->prepare("UPDATE orders SET status=? WHERE id=?");
$stmt->execute([$status,$order_id]);

}

$currentPage = basename($_SERVER['PHP_SELF']); // Lấy tên file hiện tại (trang đang chạy)

$stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC"); // Thực hiện truy vấn lấy tất cả đơn hàng, sắp xếp theo id giảm dần (mới nhất trước)
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC); // Lấy toàn bộ kết quả truy vấn dưới dạng mảng kết hợp (associative array)
?>

<!DOCTYPE html> <!-- Khai báo loại tài liệu HTML5 -->
<html lang="vi"> <!-- Mở thẻ HTML và đặt ngôn ngữ là tiếng Việt -->
<head> <!-- Phần đầu của trang, chứa cấu hình và liên kết -->
<meta charset="UTF-8"> <!-- Thiết lập bảng mã ký tự UTF-8 để hiển thị tiếng Việt đúng -->
<title>Quản lý đơn hàng</title> <!-- Tiêu đề trang hiển thị trên tab trình duyệt -->
<link rel="stylesheet" href="../css/style.css"> <!-- Liên kết file CSS nội bộ để tạo kiểu cho trang -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Liên kết thư viện Font Awesome để sử dụng icon -->
<style>
/* DÙNG Y HỆT STYLE PRODUCT để chung 1 dạng style */
body {
    margin: 0;
    padding: 30px;
    font-family: 'Segoe UI', sans-serif;
    background:
        radial-gradient(circle at 15% 25%, rgba(255, 214, 230, 0.9), transparent 55%),
        radial-gradient(circle at 85% 20%, rgba(255, 241, 200, 0.9), transparent 55%),
        radial-gradient(circle at 50% 80%, rgba(255, 220, 235, 0.8), transparent 60%);
    background-color: #fff7ec;
}

.admin-container {
    max-width: 1200px;
    margin: auto;
    padding: 30px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 45px rgba(0,0,0,0.08);
}

.admin-menu a {
    background: #1a1a1a;
    color: white;
    padding: 10px 18px;
    margin-right: 10px;
    border-radius: 10px;
    text-decoration: none;
}

.admin-menu a.active,
.admin-menu a:hover {
    background: #ffcc00;
    color: black;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th {
    background: #1a1a1a;
    color: white;
    padding: 10px;
}

td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    text-align: center;
}

tr:hover {
    background: #f3e6c6;
}
</style>
</head>

<body>

 <div class="admin-container"> <!--Khối chứa nội dung Admin.-->
            <div class="admin-header-info"> <!--Khối tiêu đề và thông tin Admin.-->
                <h2><i class="fas fa-tools"></i> TRANG QUẢN LÝ ĐƠN HÀNG</h2> <!--Tiêu đề trang.-->
                <p>CHÀO MỪNG ADMIN: 
                    <span style="font-weight: bold; color: #1a1a1a;"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Quản trị viên'); ?></span>. <!-- Hiển thị tên Admin.-->
                    <a href="../logout.php" style="color: red;">ĐĂNG XUẤT</a> <!--Liên kết Đăng xuất.-->
                </p>
            </div> <!-- Kết thúc khối admin-header-info.-->
            
           <div class="admin-menu">

                <a href="users.php"
                class="<?= $currentPage == 'users.php' ? 'active' : '' ?>">
                    <i class="fas fa-users-cog"></i> QUẢN TRỊ NGƯỜI DÙNG
                </a>

                <a href="products.php"
                class="<?= $currentPage == 'products.php' ? 'active' : '' ?>">
                    <i class="fas fa-box"></i> QUẢN LÝ SẢN PHẨM 
                </a>
                <a href="orders.php"
                class="<?= $currentPage == 'orders.php' ? 'active' : '' ?>">
                    <i class="fas fa-receipt"></i> QUẢN LÝ ĐƠN HÀNG
                </a>

                <a href="theme.php"
                class="<?= $currentPage == 'theme.php' ? 'active' : '' ?>">
                    <i class="fas fa-paint-brush"></i> QUẢN LÝ THEME
                </a>
                <a href="chat.php"
                    class="<?= $currentPage == 'chat.php' ? 'active' : '' ?>">
                    <i class="fas fa-comments"></i> QUẢN LÝ CHAT
                    </a>
                <a href="/flower_shop/index.php">
                        <i class="fas fa-home"></i> HOME </a>

            </div>
 
<table>
<tr>
    <th>ID</th>
    <th>Khách</th>
    <th>SĐT</th>
    <th>Tổng</th>
    <th>Ngày đặt</th>
    <th>Trạng thái</th>
    <th>Hành động</th>
</tr>

<?php foreach ($orders as $o): ?> <!-- Duyệt qua từng đơn hàng trong mảng $orders, mỗi lần lặp gán vào biến $o -->
<tr> <!-- Bắt đầu một dòng (row) trong bảng -->
    <td><?= $o['id'] ?></td> <!-- Hiển thị ID của đơn hàng -->
    <td><?= $o['customer_name'] ?></td> <!-- Hiển thị tên khách hàng -->
    <td><?= $o['customer_phone'] ?></td> <!-- Hiển thị số điện thoại khách hàng -->
    <td><?= number_format($o['total_amount']) ?>đ</td> <!-- Hiển thị tổng tiền của đơn hàng, có định dạng số và thêm ký hiệu tiền tệ -->
    <td><?= $o['order_date'] ?></td> <!-- Hiển thị ngày đặt hàng -->
    <td>

        <form method="POST">

        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">

        <select name="status" onchange="this.form.submit()">

        <option value="Mới" <?= $o['status']=='Mới' ? 'selected' : '' ?>>Mới</option>

        <option value="Đang giao" <?= $o['status']=='Đang giao' ? 'selected' : '' ?>>Đang giao</option>

        <option value="Hoàn thành" <?= $o['status']=='Hoàn thành' ? 'selected' : '' ?>>Hoàn thành</option>

        <option value="Đã hủy" <?= $o['status']=='Đã hủy' ? 'selected' : '' ?>>Đã hủy</option>

        </select>

        <input type="hidden" name="update_status">

        </form>

</td> <!-- Hiển thị trạng thái đơn hàng -->
<!-- biến có chữ o là biến kiểu như nó dùng cho 1 đơn hàng cụ thể
 khác biến order  -->
    <td>
        <?php if ($o['status'] == 'Mới'): ?>
            <a href="cancel_order_admin.php?id=<?= $o['id'] ?>"
               onclick="return confirm('Admin xác nhận hủy đơn này?')"
               style="color:white; background:red; padding:5px 10px; border-radius:6px; text-decoration:none;">
               ❌ Hủy
            </a>
            <a href="../order_success.php?id=<?= $o['id'] ?>" 
   style="background:#28a745;color:white;padding:5px 10px;border-radius:6px;text-decoration:none;">
   👁 Xem
</a>
        <?php else: ?>
            —
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
</div>

</body>
</html>