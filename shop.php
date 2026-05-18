<?php
//trang này xem các sp nhưng k có ctiet, dồn file vô product detail
session_start();
include 'db_config.php';
$themeQuery = $pdo->query("SELECT setting_key, setting_value FROM theme_settings");// Thực thi câu lệnh SQL → lấy tất cả cặp (key, value) từ bảng theme_settings
// $themeQuery lúc này là một PDOStatement chứa dữ liệu trả về//
$headerColor = $theme['header_color'] ?? '#6d5a75';
$hoverColor  = $theme['hover_color'] ?? '#fbbf24';
$footerColor = $theme['footer_color'] ?? '#4b3f57';
$footerTextColor = $theme['footer_text_color'] ?? '#ffffff';
$footerText = $theme['footer_text_content'] ?? '© 2025 Flower Shop.';

$theme = [];// Tạo mảng rỗng để lưu các cấu hình giao diện (theme)//
while ($row = $themeQuery->fetch(PDO::FETCH_ASSOC)) { // fetch() lấy từng dòng kết quả dạng mảng associative: ['setting_key' => ..., 'setting_value' => ...]//
    $theme[$row['setting_key']] = $row['setting_value'];
    // Gán vào mảng $theme theo dạng://
    // $theme['ten_cau_hinh'] = 'gia_tri'//
}
$sql = "SELECT id, name, price, image FROM products ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tất cả sản phẩm</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="home">

<!-- ===== HEADER GIỐNG INDEX ===== -->
<?php include "includes/header.php"; ?>
<style>
/* ===== PRODUCT GRID CSS ===== */
.product-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 sản phẩm / hàng */
    gap: 30px;

    max-width: 1200px;
    margin: 40px auto; /* CĂN GIỮA TRANG */
}

 body {
    background:
        radial-gradient(circle at 15% 25%, rgba(255, 214, 230, 0.9), transparent 55%),
        radial-gradient(circle at 85% 20%, rgba(255, 241, 200, 0.9), transparent 55%),
        radial-gradient(circle at 50% 80%, rgba(255, 220, 235, 0.8), transparent 60%);
    background-color: #fff7ec;
    background-repeat: no-repeat;
    background-attachment: fixed;

    min-height: 50vh;
    display: flex;
    flex-direction: column;

}

.product-item {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    transition: 0.3s;

    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 480px; /* khóa chiều cao */
}
/* KHUNG ẢNH CỐ ĐỊNH */
.product-item .image-wrapper {
    width: 100%;
    height: 220px;
    overflow: hidden;
    border-radius: 12px;
}
.product-item:hover {
    transform: translateY(-5px);
}
.product-item img {
    width: 100%;
    height: 220px;        /* CỐ ĐỊNH CHIỀU CAO */
    object-fit: cover;    /* CẮT ẢNH CHO ĐẸP */
    border-radius: 12px;
    display: block;
}
.product-item h3 {
    min-height: 50px;
    display: -webkit-box;
    
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.btn-order {
    background: #eda933;
    color: #000;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    width: 85%;
}

.btn-order:hover {
    background: #fbbf24;
}

.btn-view {
    display: block;
    text-align: center;
    background: #e11d48;
    color: #fff;
    padding: 10px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}

.btn-view:hover {
    background: #be123c;
}

.header {
    background-color: <?= $headerColor ?>;
}

.nav a {
    color: #fff;
    transition: 0.3s;
}

.nav a:hover {
    color: <?= $hoverColor ?>;
}

.footer {
    background-color: <?= $footerColor ?>;
    color: <?= $footerTextColor ?>;
    text-align: center;
    padding: 20px 0;
}

</style>



    <section class="featured-products" id="product-section"><!-- Tiêu đề của phần sản phẩm bán chạy -->
        <h2 class="section-title">CÁC MẪU HOA BÁN CHẠY </h2><!-- Tiêu đề của phần sản phẩm bán chạy -->
        <?php if (!empty($noResultMessage)): ?>
                <p class="no-result"><?= $noResultMessage ?></p>
            <?php endif; ?>
            <div class="page-layout">
                  <div class="left-space"></div>
        <div class="product-grid">
            <?php if (!empty($noResultMessage)): ?>
            <p style="
                text-align:center;
                color:#e91e63;
                font-weight:bold;
                margin:30px 0;
                font-size:16px;
            ">
                <?= $noResultMessage ?>
            </p>
        <?php endif; ?>

            <?php if (!empty($products)): ?><!-- Kiểm tra mảng sản phẩm có dữ liệu hay không -->
                <?php foreach ($products as $product): ?> <!-- Lặp qua từng sản phẩm để hiển thị -->
                            <div class="product-item">
                <a href="product_detail.php?id=<?php echo $product['id']; ?>"> <!-- Bấm vào ảnh để xem chi tiết sản phẩm -->
                    <!-- Truyền id sản phẩm theo URL -->
                      <div class="image-wrapper">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                        alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <!-- Hiển thị ảnh sản phẩm -->
                        <!-- htmlspecialchars → chống XSS khi hiển thị dữ liệu -->
                          </div>
                </a>
                
                <h3>
                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;"> <!-- Tên sản phẩm → bấm vào để vào trang chi tiết sản phẩm Bỏ gạch chân + giữ màu chữ -->
                        <?php echo htmlspecialchars($product['name']); ?><!-- Hiển thị tên sản phẩm (có chống XSS) -->
                    </a>
                </h3>
                
                <p class="product-price"><!-- Hiển thị giá sản phẩm -->
                    <?php echo number_format($product['price'], 0, ',', '.'); ?> VND<!-- Định dạng số: 1000000 → 1.000.000 -->
                </p>
                
                <a href="add_to_cart.php?id=<?= $product['id'] ?>"
                        class="btn-order"
                        style="display:inline-block; margin-bottom:10px;">
                        🛒 Đặt Hàng Nhanh
                        </a>               <!-- Nút đặt hàng nhanh → gửi id sản phẩm sang file add_to_cart.php -->
                    <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn-view">
                        👁️ Xem Sản Phẩm
                    </a>

            </div><!-- Kết thúc .product-item -->
                <?php endforeach; ?><!-- Kết thúc vòng lặp sản phẩm -->
            <?php else: ?>
            <?php endif; ?>

        </div><!-- Kết thúc .product-grid -->
    </section>

<?php include "includes/footer.php"; ?>

</body>
</html>
