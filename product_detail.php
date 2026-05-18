<?php
//file hiện các lwuois sp, ctiet sp nằm đây lun
//bsung thêm cnang trc dl 10-4//
session_start();
include 'db_config.php'; // Kết nối Database (Giả định db_config.php nằm ở cấp thư mục này)

// 1. Kiểm tra và lấy ID sản phẩm
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Chuyển hướng về trang chủ nếu thiếu ID hoặc ID không hợp lệ
    header("Location: index.php"); 
    exit();
}

$product_id = (int)$_GET['id'];

// 2. Lấy thông tin sản phẩm từ Database
$sql = "SELECT id, name, price, quantity, description, image,image2, image3, image4  FROM products WHERE id = :id";  // Tạo câu SQL lấy chi tiết sản phẩm theo id, dùng :id để bảo vệ SQL Injection
$stmt = $pdo->prepare($sql);  // Chuẩn bị câu SQL an toàn
$stmt->execute([':id' => $product_id]);  // Thực thi câu SQL, gán giá trị $product_id vào :id
$product = $stmt->fetch(PDO::FETCH_ASSOC);  // Lấy 1 dòng kết quả dưới dạng mảng associative

// Lấy review của sản phẩm
$review_sql = "SELECT rating, comment, created_at FROM reviews WHERE product_id = :pid ORDER BY created_at DESC";
$review_stmt = $pdo->prepare($review_sql);
$review_stmt->execute([':pid' => $product_id]);
$reviews = $review_stmt->fetchAll(PDO::FETCH_ASSOC);

// rating trung bình
$avg_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total 
            FROM reviews WHERE product_id=:pid";

$avg_stmt = $pdo->prepare($avg_sql);
$avg_stmt->execute([':pid'=>$product_id]);
$rating_data = $avg_stmt->fetch(PDO::FETCH_ASSOC);

$avg_rating = round($rating_data['avg_rating'],1);
$total_reviews = $rating_data['total'];

if (!$product) {
    // Sản phẩm không tồn tại
    header("Location: index.php"); 
    exit();
}

// Định dạng giá tiền
$formatted_price = number_format($product['price'], 0, ',', '.') . ' VND';

// Giả định bạn có file CSS/style.css 
// (Tối ưu cho việc hiển thị chi tiết sản phẩm)
?>

<!DOCTYPE html>  <!-- Khai báo tài liệu HTML5 -->
<html lang="vi">  <!-- Ngôn ngữ trang là tiếng Việt -->
<head>
    <meta charset="UTF-8">  <!-- Hỗ trợ hiển thị tiếng Việt đúng -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  <!-- Responsive cho mobile -->
    <title><?php echo htmlspecialchars($product['name']); ?> - Chi tiết</title>  <!-- Tiêu đề trang hiển thị tên sản phẩm, dùng htmlspecialchars để chống XSS -->
    <link rel="stylesheet" href="css/style.css">  <!-- Gắn file CSS chính của website -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">  <!-- Icon FontAwesome -->
 <style> 
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

        /* CSS bổ sung cho trang chi tiết sản phẩm */
        .product-detail-container {
        max-width: 1300px;            /* Giới hạn chiều rộng khung chi tiết sản phẩm */
        margin: 50px auto;           /* Canh giữa trang, cách trên 50px */
        padding: 30px;               /* Khoảng cách bên trong khung */
        background: #ffffff;            /* Nền trắng */
        border-radius: 16px;          /* Bo tròn góc 8px */
        box-shadow: 0 8px 25px rgba(89, 20, 56, 0.8);  /* Đổ bóng nhẹ xung quanh khung */
        

        }
        /* layout 2 cột */
        .product-top {
        display: grid;
        grid-template-columns: 500px 1fr;  /* CỐ ĐỊNH CỘT ẢNH */
        gap: 60px;
        align-items: start;
        min-height: 500px; /* KHÓA CHIỀU CAO TOÀN BỘ KHUNG TRÊN */
        }
        /* CARD SẢN PHẨM */
.products-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
}
.product-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 20px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}
      .product-card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    border-radius: 12px;
}
.product-item img {
    width: 100%;
    height: 220px;        /* CỐ ĐỊNH CHIỀU CAO */
    object-fit: cover;    /* CẮT ẢNH CHO ĐẸP */
    border-radius: 12px;
    display: block;
}

.product-card h3 {
    min-height: 60px;
    display: -webkit-box;
  
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-align: center;
    margin: 15px 0;
}

        .main-image {
            width: 100%;
            height: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            border-radius: 14px;
            display: block;
        }
       .detail-image-box {
            width: 520px;
            height: 400px; /* KHÓA CHIỀU CAO */
            }
        .detail-image-box img {
        width: 100%;        /* Hình ảnh không vượt quá khung chứa */
        height: 250px;         /* Giữ tỉ lệ ảnh */
         object-fit: cover;
        overflow: hidden;
        border-radius: 12px;     /* Bo tròn góc ảnh */           
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);  /* Đổ bóng nhẹ cho ảnh */
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        

        .detail-info {
    display: flex;
    flex-direction: column;
}
        .detail-info h1 {
            color: #333;              /* Màu chữ xám đậm */
            margin-top: 0;            /* Bỏ khoảng cách trên */
            font-size: 28px;
            margin-bottom: 10px;
            border-bottom: 2px solid #f4c430;  /* Gạch chân vàng */
            padding-bottom: 10px;     /* Khoảng cách giữa chữ và gạch chân */
        }
        .detail-price {
            font-size: 26px;           /* Chữ lớn */
            color: #e53935;           /* Màu đỏ nổi bật */
            font-weight: bold;         /* Chữ đậm */
            margin: 15px 0;           /* Khoảng cách trên dưới */
        }
        .detail-description {
            margin-top: 60px ;           /* Khoảng cách trên dưới */
            padding-top: 30px;
            border-top: 1px solid #eee;
            line-height: 1.6;         /* Chiều cao dòng dễ đọc */
            min-height: 200px;
            color: #555;              /* Màu chữ xám */
        }
        /* box ưu đãi */
        .promo-box {
            border: 2px solid #1e40af;
            border-radius: 12px;
            padding: 20px;
            margin-top: 25px;
            min-height: 180px; /* FIX KHÔNG CO GIÃN */
            height: 180px; /* KHÓA CHIỀU CAO */

        }

        .promo-box h3 {
             margin-bottom: 10px;
            color: #1e40af;
        }
        .promo-box ul {
            padding-left: 20px;
        }
        .promo-box li {
            margin-bottom: 8px;
        }

        /* mô tả full width */
        .detail-description {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            line-height: 1.8;
             min-height: 180px; /* Dù mô tả ngắn vẫn giữ form */
        }
       .btn-add-to-cart {
        display: inline-block;           /* Hiển thị như khối nhưng nằm trên cùng dòng với nội dung khác */
        background-color: #ffcc00;       /* Nền vàng nổi bật */
        color: #000;                  /* Chữ màu đen đậm */
        padding: 14px 30px;              /* Khoảng cách bên trong nút */
        text-decoration: none;           /* Bỏ gạch chân nếu là link */
        border-radius: 8px;              /* Bo tròn góc nút */
        font-size: 16px;                /* Chữ hơi lớn */
        font-weight: bold;                /* Chữ đậm */
        transition: background-color 0.2s; /* Hiệu ứng đổi màu khi hover mượt */
        border: none;                     /* Không viền */
        cursor: pointer;                  /* Con trỏ chuột dạng tay khi hover */
}
        .btn-add-to-cart:hover {
        background-color: #e6b800;       /* Màu nền sẫm hơn khi rê chuột */
}

/* ===== GALLERY ===== */
.image-gallery {
    display: flex;
    gap: 12px;
    margin-top: 15px;
}

.image-gallery img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 10px;
    cursor: pointer;
    border: 2px solid #eee;
    transition: 0.2s;
}
.image-gallery img:hover {
    border-color: #f4c430;
    transform: scale(1.05);
}
.product-actions {
    margin-top: 30px;
    display: flex;
    gap: 20px;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.25s ease;
}

.back-home {
    background: #f3f3f3;
    color: #333;
}

.back-home:hover {
    background: #e0e0e0;
    transform: translateX(-3px);
}

.view-cart {
    background: #ffcc00;
    color: #000;
}

.view-cart:hover {
    background: #ffb700;
    transform: translateY(-2px);
}

.action-btn i {
    font-size: 16px;
}

.review-box {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    margin-top: 25px;
    height: 180px;
    overflow-y: auto;
}

.review-box h3{
    margin-bottom:10px;
}

.review-item{
    border-bottom:1px solid #eee;
    padding:8px 0;
}

.review-stars{
    color:#f4c430;
    font-size:14px;
}




.review-item{
padding:10px 0;
border-bottom:1px solid #eee;
}

.review-stars{
color:#f4c430;
font-size:16px;
}

.review-box textarea{
border-radius:8px;
padding:8px;
border:1px solid #ccc;
}
 </style>
</head>

<body>
<?php include "includes/header.php"; ?>
    <main> <!-- Phần nội dung chính của trang -->
        <div class="product-detail-container">

    <div class="product-top">
        <!-- ẢNH -->
        <div class="detail-image-box">
            <img src="<?= htmlspecialchars($product['image']) ?>"
                 alt="<?= htmlspecialchars($product['name']) ?>"
                    class="main-image">
         <!-- Gallery ảnh nhỏ -->
        <div class="image-gallery">

<?php if (!empty($product['image'])): ?>
    <img src="<?= htmlspecialchars($product['image']) ?>" 
         onclick="changeMainImage(this.src)">
<?php endif; ?>

<?php if (!empty($product['image2'])): ?>
    <img src="<?= htmlspecialchars($product['image2']) ?>" 
         onclick="changeMainImage(this.src)">
<?php endif; ?>

<?php if (!empty($product['image3'])): ?>
    <img src="<?= htmlspecialchars($product['image3']) ?>" 
         onclick="changeMainImage(this.src)">
<?php endif; ?>

<?php if (!empty($product['image4'])): ?>
    <img src="<?= htmlspecialchars($product['image4']) ?>" 
         onclick="changeMainImage(this.src)">
<?php endif; ?>

</div>

 </div>

        <!-- THÔNG TIN -->
        <div class="detail-info">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <p style="color:#f4c430;font-size:18px">

                    ⭐ <?= $avg_rating ?> / 5 
                    (<?= $total_reviews ?> đánh giá)

                    </p>
            <p class="detail-price"><?= $formatted_price ?></p>

            <p style="color:<?= $product['quantity'] > 0 ? 'green' : 'red' ?>">
                <i class="fas fa-box"></i>
                <?= $product['quantity'] > 0 ? 'CÒN HÀNG (' . $product['quantity'] . ')' : 'Hết hàng' ?>
            </p>

            <?php if ($product['quantity'] > 0): ?>
             <a href="add_to_cart.php?id=<?php echo $product['id']; ?>" 
            class="btn-add-to-cart">
    <i class="fas fa-cart-plus"></i> THÊM VÀO GIỎ HÀNG
                    </a>
                   
                </button>

                <!-- thông báo -->
                <div id="cart-message" style="display:none; margin-top:15px; color:red; font-weight:bold;">
                    ✅ Đã Thêm Sản Phẩm Vào Giỏ Hàng
                </div>

            <?php endif; ?>

            <!-- ƯU ĐÃI -->
            <div class="promo-box">
                <h3>🎁 ƯU ĐÃI ĐẶC BIỆT</h3>
                <ul>
                    <li>Miễn phí giao hoa nội thành HCM</li>
                    <li>Miễn phí thiệp & bảng rôn</li>
                    <li>Gửi ảnh thực tế trước khi giao</li>
                    <li>Hỗ trợ giao nhanh 60–120 phút</li>
                </ul>
            </div>
           <div class="review-box">

<h3>⭐ ĐÁNH GIÁ CỦA KHÁCH HÀNG</h3>

<?php if(count($reviews) > 0): ?>

    <?php foreach($reviews as $r): ?>

        <div class="review-item">

            <div class="review-stars">
                <?php
                for($i=1;$i<=5;$i++){
                    echo $i <= $r['rating'] ? "⭐" : "☆";
                }
                ?>
            </div>

            <div><?= htmlspecialchars($r['comment']) ?></div>

            <small style="color:#888">
                <?= date("d/m/Y", strtotime($r['created_at'])) ?>
            </small>

        </div>

    <?php endforeach; ?>

<?php else: ?>

<p>Chưa có đánh giá nào</p>

<?php endif; ?>


<hr>

<h4>Gửi đánh giá của bạn</h4>

<form action="submit_review.php" method="POST">

<input type="hidden" name="product_id" value="<?= $product_id ?>">

<label>Chọn số sao:</label>

<select name="rating" required>
<option value="5">⭐⭐⭐⭐⭐</option>
<option value="4">⭐⭐⭐⭐</option>
<option value="3">⭐⭐⭐</option>
<option value="2">⭐⭐</option>
<option value="1">⭐</option>
</select>

<br><br>

<textarea name="comment"
placeholder="Viết đánh giá của bạn..."
required
style="width:100%;height:60px;"></textarea>

<br><br>

<button type="submit" class="btn-add-to-cart">
Gửi đánh giá
</button>

</form>

</div>

    <!-- MÔ TẢ -->
    <div class="detail-description">
        <h2>MÔ TẢ SẢN PHẨM</h2>
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
       <div class="product-actions">
    <a href="index.php" class="action-btn back-home">
        <i class="fas fa-arrow-left"></i>
        <span>HOME</span>
    </a>
    <a href="shop.php" class="action-btn back-home">
        <span>🌸 Xem Thêm Các Sản Phẩm Khác</span>
    </a>
    <a href="cart.php" class="action-btn view-cart">
        <i class="fas fa-shopping-cart"></i>
        <span>Xem Giỏ Hàng</span>
    </a>
</div>

    </div>

</div>

    </main>
    <script>
 {
    const productId = this.dataset.id;

    fetch('add_to_cart.php?id=' + productId)
        .then(response => response.text())
        .then(() => {
            const msg = document.getElementById('cart-message');
            msg.style.display = 'block';

            // Ẩn sau 2.5 giây (tuỳ thích)
            setTimeout(() => {
                msg.style.display = 'none';
            }, 3000);
        })
        .catch(err => {
            alert('Có lỗi xảy ra, vui lòng thử lại');
        });
};
</script>
<script>
function changeMainImage(src) {
    document.querySelector('.main-image').src = src;
}
</script>
<?php include "includes/footer.php"; ?>
</body>
</html>