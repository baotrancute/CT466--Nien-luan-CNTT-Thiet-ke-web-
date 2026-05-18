<?php
//file mới goojp tất cả vào hd để dễ đổi trong them, nhớ include all trang
if (session_status() === PHP_SESSION_NONE) { // Kiểm tra nếu session chưa được khởi tạo
    session_start(); // Bắt đầu session để sử dụng biến phiên
}
if (!isset($pdo)) { // Kiểm tra nếu biến kết nối database $pdo chưa tồn tại
    include __DIR__ . '/../db_config.php'; // Nhúng file cấu hình database từ thư mục cha
}

$themeQuery = $pdo->query("SELECT setting_key, setting_value FROM theme_settings"); // Truy vấn các thiết lập giao diện từ bảng theme_settings
$theme = []; // Khởi tạo mảng rỗng để lưu dữ liệu theme

while ($row = $themeQuery->fetch(PDO::FETCH_ASSOC)) { // Lặp qua từng dòng dữ liệu lấy từ database
    $theme[$row['setting_key']] = $row['setting_value']; // Gán giá trị vào mảng $theme với key là setting_key
}

$headerColor = $theme['header_bg_color'] ?? '#2c3e50'; // Lấy màu nền header từ theme, nếu không có thì dùng mặc định
$hoverColor = $theme['header_hover_color'] ?? '#e1b12c'; // Lấy màu khi hover menu header, nếu không có thì dùng mặc định

$logoPath = $theme['logo_path'] ?? 'uploads/default_logo.png'; // Lấy đường dẫn logo từ theme, nếu không có thì dùng logo mặc định
?>
<style>
.main-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 60px;
    height: 90px;
    background-color: <?= $headerColor ?>;
}

.main-header nav {
    display: flex;
    gap: 40px;
}

.main-header nav a {
    text-decoration: none;
    color: #ffffff;
    font-size: 14px;
    letter-spacing: 1px;
    font-weight: 500;
    transition: 0.3s;
}

.main-header nav a:hover {
    color: <?= $hoverColor ?>;
}

.logo {
    background: #ffffff;
    padding: 6px 16px;
    border-radius: 14px;
    display: flex;
    align-items: center;
}

.logo img {
    height: 38px;
    width: auto;
}
</style>

 <header class="main-header">

    <div class="left-group">

        <div class="logo">
            <a href="index.php">
                <img src="<?= $theme['logo_path'] ?? 'images/default-logo.png' ?>" alt="Flower Shop Logo">
            </a>
        </div>

        <!-- Form tìm kiếm -->
        <form action="index.php" method="GET" class="search-form">
            <div class="search-type">
                <i class="fas fa-filter"></i>
                <select name="type">
                    <option value="name" <?= ($_GET['type'] ?? '') === 'name' ? 'selected' : '' ?>>
                        Tên sản phẩm 🌸
                    </option>
                    <option value="price" <?= ($_GET['type'] ?? '') === 'price' ? 'selected' : '' ?>>
                        Giá 🏷️ (VND)
                    </option>
                    
                </select>
            </div>

            <input type="text" name="search" placeholder="Nhập từ khóa hoặc giá, sale..."
                   value="<?= htmlspecialchars($_GET['search'] ?? ''); ?>">

            <button type="submit"><i class="fas fa-search"></i></button>

            <div class="search-hint">
                <i class="fas fa-info-circle"></i>
                Có thể tìm theo <b>tên</b>, <b>giá</b> hoặc <b>sản phẩm đang sale</b>
            </div>
        </form>

        <!-- Menu -->
        <nav class="nav">
            <a href="index.php" class="current-page">HOME</a>
            <a href="cart.php"> CART</a>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['is_admin'] == 0): ?>
    <a href="my_orders.php">🧾 MY ORDER</a>
<?php endif; ?>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                <a href="admin/users.php" class="admin-btn">ADMIN</a>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Tài khoản'); ?>
                </span>
                <a href="logout.php">LOGOUT</a>
            <?php else: ?>
                <a href="login.php">LOGIN</a>
                <a href="register.php">SIGN UP</a>
            <?php endif; ?>
        </nav>

    </div>

    <!-- SUPPORT thim mới) -->
    <div class="header-support">
        <a href="tel:0337558602" class="hotline-btn">☎ Hotline: 0337558603</a>
        <button class="chat-btn">💬 Chat hỗ trợ</button>
    </div>

</header>