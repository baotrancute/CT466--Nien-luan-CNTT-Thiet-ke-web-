<?php
//file chat.php là trang dành cho admin xem các đoạn chat (nó nằm chung với các trang admin khác)// 
session_start(); // Khởi động session để truy cập các biến phiên lưu thông tin đăng nhập của người dùng

$currentPage = basename($_SERVER['PHP_SELF']); // Lấy tên file PHP hiện tại đang chạy (ví dụ chat.php) từ đường dẫn của server

include '../db_config.php'; // Nhúng file cấu hình kết nối database để sử dụng biến $pdo truy cập cơ sở dữ liệu

if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) != 1) { // Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
    header("Location: ../index.php"); // Chuyển hướng người dùng về trang chủ nếu không có quyền truy cập
    exit(); // Kết thúc chương trình ngay sau khi chuyển hướng để không chạy tiếp các đoạn code phía dưới
}

$message = ""; // Khởi tạo biến message rỗng để dùng lưu thông báo hoặc nội dung tin nhắn khi cần hiển thị trên trang
//mes.. dùng để lưu kq tvan db, msgcou thì lưu sl tnhan
/* Lấy toàn bộ tin nhắn */
$messages = $pdo->query("SELECT * FROM chat_messages ORDER BY id DESC"); // Thực hiện truy vấn SQL để lấy tất cả các tin nhắn từ bảng chat_messages và sắp xếp theo id giảm dần (tin nhắn mới nhất hiển thị trước)
//pdo gọi pthuc query của pdo, sau khi qry thì mes..trở thành oject kq tvan
$msg_count = $messages->rowCount(); // Đếm tổng số bản ghi (số tin nhắn) lấy được từ kết quả truy vấn và lưu vào biến msg_count
?>
<!DOCTYPE html> <!-- Khai báo loại tài liệu HTML5 -->
<html lang="vi"> <!-- Bắt đầu trang HTML và đặt ngôn ngữ là tiếng Việt -->
<head> <!-- Phần đầu của trang web chứa thông tin cấu hình và tài nguyên -->
<meta charset="UTF-8"> <!-- Thiết lập bảng mã ký tự UTF-8 để hiển thị đúng tiếng Việt -->
<meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Thiết lập hiển thị responsive để trang web phù hợp với các thiết bị di động -->
<title>QUẢN LÝ CHAT - ADMIN</title> <!-- Tiêu đề trang hiển thị trên tab trình duyệt -->

<link rel="stylesheet" href="../css/style.css"> <!-- Liên kết file CSS chính của website để áp dụng kiểu giao diện -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Nhúng thư viện icon Font Awesome từ CDN để sử dụng các biểu tượng (icon) trên trang -->

<style> 

/* ===== BACKGROUND ===== */
body {
    margin:0;
    padding:30px;
    font-family:'Segoe UI',sans-serif;

    background:
        radial-gradient(circle at 15% 25%, rgba(255,214,230,0.9), transparent 55%),
        radial-gradient(circle at 85% 20%, rgba(255,241,200,0.9), transparent 55%),
        radial-gradient(circle at 50% 80%, rgba(255,220,235,0.8), transparent 60%);

    background-color:#fff7ec;
    background-attachment:fixed;
}

/* ===== CONTAINER ===== */
.admin-container{
    max-width:1200px;
    margin:40px auto;
    padding:35px;
    background:white;
    border-radius:18px;
    box-shadow:0 15px 40px rgba(0,0,0,0.08);
}

/* ===== MENU ===== */
.admin-menu{
    margin:20px 0;
}

.admin-menu a{
    background:#1a1a1a;
    color:white;
    padding:10px 18px;
    margin-right:10px;
    text-decoration:none;
    border-radius:8px;
    transition:0.3s;
}

.admin-menu a:hover,
.admin-menu .active{
    background:#ffcc00;
    color:#1a1a1a;
}

/* ===== TABLE ===== */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th{
    background:#1a1a1a;
    color:white;
    padding:12px;
}

td{
    padding:10px;
    border-bottom:1px solid #eee;
}

tr:hover{
    background:#fff3d9;
}

th,td{
    text-align:center;
}

/* ===== CHAT INPUT ===== */
.reply-box{
    display:flex;
    gap:6px;
}

.reply-box input{
    padding:6px;
    border-radius:6px;
    border:1px solid #ccc;
}

.reply-box button{
    background:#ffcc00;
    border:none;
    padding:6px 10px;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
}

.reply-box button:hover{
    background:#ffb700;
}

</style>
</head>

<body>

<main>

<div class="admin-container">

<div class="admin-header-info">
<h2><i class="fas fa-comments"></i> QUẢN LÝ CHAT</h2>

<p>
CHÀO MỪNG ADMIN:
<span style="font-weight:bold;color:#1a1a1a;font-size:15px;">
<?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?>
</span>
<a href="../logout.php" style="color:red;">ĐĂNG XUẤT</a>
</p>

</div>

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
<i class="fas fa-home"></i> HOME
</a>

</div>

<hr>

<h3>Danh sách tin nhắn khách hàng (Tổng: <?= $msg_count ?>)</h3>

<table>

<tr>
<th>ID</th>
<th>Người gửi</th>
<th>Nội dung</th>
<th>Thời gian</th>
<th>Trả lời</th>
</tr>

<?php foreach($messages as $msg): ?>

<tr>

<td><?= $msg['id'] ?></td>

<td>
<?= $msg['sender']=="admin" ? "🏪 Admin" : "👤 Khách" ?>
</td>

<td><?= htmlspecialchars($msg['message']) ?></td>

<td><?= $msg['created_at'] ?></td>

<td>

<?php if($msg['sender']=="user"): ?>

<form class="reply-box" method="POST" action="reply_chat.php">

<input type="hidden" name="message_id" value="<?= $msg['id'] ?>">

<input type="text" name="reply" placeholder="Trả lời khách..." required>

<button type="submit">
<i class="fas fa-paper-plane"></i>
</button>

</form>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

</main>

</body>
</html>