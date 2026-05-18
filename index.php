<?php 
//file tchu của sốp

session_start(); 
// >> BƯỚC 1: KẾT NỐI DATABASE VÀ TRUY VẤN DỮ LIỆU <<
include 'db_config.php'; // Đảm bảo file kết nối DB nằm ở thư mục gốc
$themeQuery = $pdo->query("SELECT setting_key, setting_value FROM theme_settings");// Thực thi câu lệnh SQL → lấy tất cả cặp (key, value) từ bảng theme_settings
// $themeQuery lúc này là một PDOStatement chứa dữ liệu trả về//

$theme = [];// Tạo mảng rỗng để lưu các cấu hình giao diện (theme)//
while ($row = $themeQuery->fetch(PDO::FETCH_ASSOC)) { // fetch() lấy từng dòng kết quả dạng mảng associative: ['setting_key' => ..., 'setting_value' => ...]//
    $theme[$row['setting_key']] = $row['setting_value'];
    // Gán vào mảng $theme theo dạng://
    // $theme['ten_cau_hinh'] = 'gia_tri'//
}

// Xử lý Tìm kiếm (Bổ sung mới)
$search_query = "";// Chuỗi điều kiện WHERE cho SQL (ban đầu để trống)//
$search_term = "";// Biến chứa từ khóa tìm kiếm người dùng nhập (nếu có)//
$search_type  = $_GET['type'] ?? 'name';// mặc định tìm theo tên
$limit = 10; // Giới hạn mặc định//

if (isset($_GET['type']))   { // Kiểm tra xem URL có biến ?search= và người dùng có nhập ký tự không (không rỗng)//
    $search_term = trim($_GET['search']); // Lấy từ khóa tìm kiếm và xóa khoảng trắng dư ở đầu/cuối//
    $search_type = $_GET['type'] ?? 'name';
    // Tạo điều kiện tìm kiếm: tìm tên sản phẩm chứa từ khóa
     if ($search_type === 'sale') {
        // Chỉ lọc sản phẩm đang sale
        $search_query = " WHERE is_sale = 1 ";
        $limit = 100;

     } elseif (!empty($_GET['search'])) {
         $search_term = trim($_GET['search']);
         if ($search_type === 'price') {
            $search_query = " WHERE price <= :search_term ";
        } else {
            $search_query = " WHERE name LIKE :search_term ";
        }
           $limit = 100;
    }
}  
$showProducts = false;

// Có tìm kiếm
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $showProducts = true;
}

// Click Khám phá ngay
if (isset($_GET['view']) && $_GET['view'] === 'products') {
    $showProducts = true;
}

// Lấy sản phẩm dựa trên tìm kiếm hoặc mặc định
try {
    $sql = "SELECT id, name, price, image 
            FROM products 
            {$search_query} 
            ORDER BY id DESC 
            LIMIT {$limit}";
             //Câu SQL động:
    // - Nếu có tìm kiếm → thêm WHERE name LIKE :search_term//
    // - Nếu không → bỏ qua WHERE//
    // Luôn sắp xếp sản phẩm giảm dần theo ID (mới nhất lên trước)//
    // LIMIT để giới hạn số sản phẩm hiển thị//

    $stmt = $pdo->prepare($sql); // Chuẩn bị truy vấn SQL (an toàn hơn query trực tiếp)//
    
    if ($search_query !== "" && $search_type !== 'sale') {// Nếu có tìm kiếm → bind giá trị cho :search_term//
        // Bind parameter cho truy vấn an toàn (chống SQL Injection)
        $stmt->bindValue(':search_term', 
            $search_type === 'price'
                 ? (float)$search_term
                 :  '%' . $search_term . '%'); // Gắn từ khóa vào placeholder :search_term
        // Dùng %keyword% để tìm "chứa từ khóa" (LIKE)//
    }
    
    $stmt->execute();  // Thực thi truy vấn sau khi đã bind parameter//
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);// Lấy tất cả sản phẩm trả về → dạng mảng associative//
        $noResultMessage = '';

                    if (
                        isset($_GET['search']) &&
                        trim($_GET['search']) !== '' &&
                        empty($products)
                    ) {
                        if ($search_type === 'price') {
                            $noResultMessage = '❌ Không có sản phẩm có giá ≤ ' 
                                . number_format((float)$search_term, 0, ',', '.') . ' VND';
                        } elseif ($search_type === 'sale') {
                            $noResultMessage = '❌ Hiện không có sản phẩm đang sale';
                        } else {
                            $noResultMessage = '❌ Không tìm thấy sản phẩm phù hợp';
                        }
            }

            } catch (PDOException $e) {// Nếu truy vấn bị lỗi (ví dụ SQL sai)//
                // Xử lý lỗi (ẩn lỗi với người dùng cuối)
                $products = [];// Tránh lỗi hiển thị giao diện, trả về mảng rỗng
                // echo "Lỗi truy vấn: " . $e->getMessage(); // Chỉ dùng khi debug
            }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><!-- Thiết lập bảng mã ký tự để hiển thị tiếng Việt -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  <!-- Giúp trang web responsive trên điện thoại, tự co giãn phù hợp màn hình -->
    <title>HOME - Flower & Co. Shop Hoa Online</title><!-- Tiêu đề hiển thị trên tab trình duyệt -->
    
    <link rel="stylesheet" href="css/style.css"> <!-- Link tới file CSS chính để định dạng giao diện cho trang -->
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Thư viện icon Font Awesome để dùng các icon như fa-search, fa-user, fa-cart v.v. -->
    
    <style>
        :root {
    --hero-title-color: <?= $theme['hero_title_color'] ?? '#ffffff' ?>;
    --hero-text-color: <?= $theme['hero_text_color'] ?? '#f2f2f2' ?>;
 }
        /* BƯỚC 2: CSS ĐỂ HIỂN THỊ SẢN PHẨM DƯỚI DẠNG CARD (NHƯ ẢNH MẪU) */
        
        .section-title {
            color: #1a1a1a;
            font-size: 28px;
            margin-bottom: 30px;
            font-weight: bold;
            text-align: center;
        }

        .product-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            max-width: 1200px; /* Giới hạn chiều rộng lưới sản phẩm */
            margin: 0 auto;
        }

        .product-item {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            width: calc(25% - 20px); /* 4 cột trên desktop */
            min-width: 250px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .product-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        /* Ảnh sản phẩm */
        .product-item img {
            max-width: 100%;
            height: 150px; /* Giới hạn chiều cao cho đồng nhất */
            object-fit: contain;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        /* Tiêu đề sản phẩm */
        .product-item h3 {
            font-size: 18px;
            color: #1a1a1a;
            margin: 10px 0;
            min-height: 40px; /* Giúp các thẻ có chiều cao đồng nhất */
        }

        /* Giá sản phẩm */
        .product-price {
            color: #e91e63; /* Màu hồng dâu */
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
        }

        /* Nút Đặt hàng */
        .btn-detail {
            background-color: #e91e63; 
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .btn-detail:hover {
            background-color: #ff6e8a;
        }
.btn-order {
    background: #f3af39;
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

 /* Phần Theme chỉnh sửa */
   .main-header {
    background-color: <?= $theme['header_bg_color'] ?> !important;
    color: <?= $theme['header_text_color'] ?> !important;
    padding: <?= $theme['header_padding'] ?>;
    height: <?= $theme['header_height'] ?>;
    display: flex;
    align-items: center;
}

    footer {
        background-color: <?= $theme['footer_bg_color'] ?> !important;
        color: <?= $theme['footer_text_color'] ?> !important;
    }
   .hero {
    background-image: url('<?= $theme['hero_image_path'] ?? '' ?>');
    background-size: cover;
    background-position: center;
    min-height: 300px;
     width: 100%;
    height: 600px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}
.hero h1 {
    color: var(--hero-title-color);
}

.hero p {
    color: var(--hero-text-color);
}

   .hero-banners {
    position: absolute;
    top: 160px;
    right: 60px;
    display: flex;
    gap: 18px;
    align-items: center;
    z-index: 3;
}
.hero-content{
    display: flex;
    flex-direction: column; /* xếp theo chiều dọc */
    align-items: center;    /* căn giữa ngang */
    justify-content: center;
    text-align: center;
    margin: 0 auto;
    margin-left: 180px;
}
    
.hero-banners img {
    width: 250px;
    height: 150px;
    object-fit: cover;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.35);
    transition: all 0.3s ease;
    opacity: 0.9;
}

/* Banner trung tâm nổi bật */
.hero-banners img:nth-child(2) {
    width: 300px;
    height: 130px;
    transform: translateY(-10px);
    opacity: 1;
}

/* Hover */
.hero-banners img:hover {
    transform: scale(1.08);
    opacity: 1;
}
.logo img {
    height: 70px;
    width: 250px;
    object-fit: contain;
    border-radius: 14px;
    padding: 6px;
    background: white;
}

.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.left-group {
    display: flex;
    align-items: center;
    gap: 20px;
}



.menu {
    display: flex;
    gap: 25px;
}


 </style>
</head>

<body>
<?php include "includes/header.php"; ?>
<section class="hero">
    <div class="hero-content">
        <h1><?= $theme['hero_headline'] ?? 'Món Quà Từ Thiên Nhiên, Trao Trọn Yêu Thương' ?></h1>
        <p><?= $theme['hero_subtext'] ?? 'Tuyển chọn những bó hoa tươi thắm nhất cho mọi dịp.' ?></p>
        <a href="shop.php" class="hero-button">KHÁM PHÁ NGAY</a>
    </div>
<?php if (($theme['banner_abc_enabled'] ?? '1') == '1'): ?>
    <div class="hero-banners">
        <?php if (!empty($theme['banner_a_path'])): ?>
            <img src="<?= $theme['banner_a_path'] ?>" class="hero-banner" alt="Banner A">
        <?php endif; ?>
        <?php if (!empty($theme['banner_b_path'])): ?>
            <img src="<?= $theme['banner_b_path'] ?>" class="hero-banner" alt="Banner B">
        <?php endif; ?>
        <?php if (!empty($theme['banner_c_path'])): ?>
            <img src="<?= $theme['banner_c_path'] ?>" class="hero-banner" alt="Banner C">
        <?php endif; ?>
    </div>
    <?php endif; ?>
</section>

<?php if ($showProducts): ?>
    <section class="featured-products" id="product-section"><!-- Tiêu đề của phần sản phẩm bán chạy -->
        <h2 class="section-title">CÁC MẪU HOA </h2><!-- Tiêu đề của phần sản phẩm bán chạy -->
       

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
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                        alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <!-- Hiển thị ảnh sản phẩm -->
                        <!-- htmlspecialchars → chống XSS khi hiển thị dữ liệu -->
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
                        </a>

                <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn-view">
                            👁️ Xem Sản Phẩm
                        </a>

            </div><!-- Kết thúc .product-item -->
                <?php endforeach; ?><!-- Kết thúc vòng lặp sản phẩm -->
            <?php else: ?>
            <?php endif; ?>

        </div><!-- Kết thúc .product-grid -->
</section>
<?php endif; ?>


<?php if (isset($_GET['search']) && trim($_GET['search']) !== ''): ?>
<script>
    window.addEventListener('load', function () {
        const productSection = document.getElementById('product-section');
        if (productSection) {
            productSection.scrollIntoView({ behavior: 'smooth' });
        }
    });
</script>

<?php endif; ?>

<?php include "includes/footer.php"; ?>
</body>
</html>