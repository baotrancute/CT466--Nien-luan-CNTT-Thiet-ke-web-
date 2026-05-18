<?php
// giỏ hàng //
session_start(); // Khởi tạo session để lưu và lấy dữ liệu giỏ hàng

$cart = $_SESSION['cart'] ?? []; // Lấy giỏ hàng từ session, nếu chưa có thì gán mảng rỗng
$total_price = 0; // Khởi tạo biến tổng tiền ban đầu bằng 0
// >>> XÓA LỖI LẶP LẠI session_start() VÀ CẤU TRÚC CODE <<< // Ghi chú: nhắc kiểm tra tránh gọi session_start() nhiều lần và lỗi cấu trúc code
?>

<!DOCTYPE html>
<html lang="vi"><!-- Khởi đầu trang HTML, thiết lập ngôn ngữ là tiếng Việt -->
<head>
    <meta charset="UTF-8"> <!-- Thiết lập bộ ký tự UTF-8 để hiển thị tiếng Việt đúng -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><!-- Đảm bảo trang web hiển thị tốt trên thiết bị di động và máy tính -->
    <title>Giỏ Hàng Của Bạn</title>   <!-- Tiêu đề hiển thị trên tab trình duyệt -->
    <link rel="stylesheet" href="css/style.css">  <!-- Nhúng file CSS riêng của dự án để định dạng giao diện -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
       <!-- Nhúng Font Awesome để sử dụng các biểu tượng (icon) -->
    <style>
   
      html, body {
    margin: 0;
    padding: 0;
    min-height: 100%;
}

body {
    background:
        radial-gradient(circle at 15% 25%, rgba(255, 214, 230, 0.9), transparent 55%),
        radial-gradient(circle at 85% 20%, rgba(255, 241, 200, 0.9), transparent 55%),
        radial-gradient(circle at 50% 80%, rgba(255, 220, 235, 0.8), transparent 60%);
    
    background-color: #fff7ec;
    background-repeat: no-repeat;
    background-attachment: fixed;
}

.cart-container {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}



        .cart-container { max-width: 1000px; margin: 180px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .cart-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .cart-table th, .cart-table td { border: 1px solid #120202; padding: 12px; text-align: left; }
        .cart-table th { background-color: #f28aac; }
        .cart-image { width: 50px; height: 50px; object-fit: contain; }
        .cart-total { text-align: right; margin-top: 20px; font-size: 1.2em; font-weight: bold; }
        
        .btn-checkout { background-color: #e91e63; color: white; padding: 12px 25px; border: none; border-radius: 4px; text-decoration: none; display: inline-block; margin-top: 15px; }
        .btn-continue { 
            background-color: #e91e63; 
            color: #fffdfd; 
            padding: 12px 25px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            text-decoration: none; 
            display: inline-block; 
            margin-top: 15px;
            margin-right: 10px; 
        }
        .btn-remove { 
            background: none; 
            border: none; 
            color: #dc3545; 
            cursor: pointer;
            font-size: 1.2em;
            padding: 0;
            margin: 0;
        }
        /* CSS CHO NÚT TĂNG/GIẢM SỐ LƯỢNG */
        .quantity-control {
            display: flex;
            align-items: center;
            width: 120px; 
        }
        .btn-qty {
            background-color: #f2f2f2;
            border: 1px solid #ccc;
            padding: 5px 10px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1em;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .input-qty {
            width: 60px !important;
            text-align: center;
            border-left: none;
            border-right: none;
            padding: 5px 0;
            margin: 0;
        }
        /* width: Chiều rộng đầy đủ container 
         box-shadow:Tạo bóng mờ ; margin:Canh giữa trang với khoảng cách trên và dưới
         max-width: Giới hạn chiều rộng, display: flex Hiển thị theo hàng ngang */
    </style>
</head>

<body><!-- Bắt đầu phần thân trang, chứa nội dung giỏ hàng -->
<?php include "includes/header.php"; ?>
    <main>
        <div class="cart-container">
            <h2><i class="fas fa-shopping-cart"></i> GIỎ HÀNG CỦA BẠN 🛒✨</h2> <!-- Tiêu đề chính của trang giỏ hàng, kèm biểu tượng giỏ hàng từ Font Awesome -->
            
            <?php if (empty($cart)): ?>  <!-- Kiểm tra nếu giỏ hàng rỗng -->
                <p style="text-align: center; font-size: 1.1em; color: black;">GIỎ HÀNG CỦA BẠN ĐANG TRỐNG 🛒❌ ⇒
                    <a href="shop.php" class="btn-action btn-continue" style="width: 92%; max-width: 300px;  background-color: #f7a7c1; color: black " >🌸 TIẾP TỤC MUA SẮM 🌸 </a></p> 
                    
                <!-- Hiển thị thông báo giỏ hàng trống, kèm link quay lại trang sản phẩm -->
            <?php else: ?>
                <!-- Nếu giỏ hàng có sản phẩm -->
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Tên Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Hành động</th>
                        <!-- Tiêu đề các cột trong bảng giỏ hàng: hình ảnh, tên, giá, số lượng, thành tiền, và thao tác -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $item): // Lặp qua từng sản phẩm trong giỏ hàng
                            $subtotal = $item['price'] * $item['quantity']; // Tính thành tiền của sản phẩm hiện tại
                            $total_price += $subtotal; // Tính tổng tiền CHỈ 1 LẦN
                        ?>
                            <tr>
                                <td><img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-image"></td><!-- Hiển thị hình ảnh sản phẩm, dùng htmlspecialchars để tránh lỗi XSS -->
                                <td><?php echo htmlspecialchars($item['name']); ?></td>  <!-- Hiển thị tên sản phẩm, bảo vệ dữ liệu bằng htmlspecialchars -->
                                <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VND</td> <!-- Hiển thị giá sản phẩm, định dạng số với dấu chấm phân cách hàng nghìn -->
                                
                                <td>
                                    <div class="quantity-control" data-id="<?php echo $item['id']; ?>"><!-- Khối điều khiển số lượng, lưu ID sản phẩm trong data-id -->
                                        <button type="button" class="btn-qty btn-minus" data-action="minus">-</button> <!-- Nút giảm số lượng -->
                                        <input type="text" 
                                               name="quantity" 
                                               class="input-qty" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               readonly> <!-- Hiển thị số lượng hiện tại, chỉ đọc để tránh sửa trực tiếp -->
                                        <button type="button" class="btn-qty btn-plus" data-action="plus">+</button> <!-- Nút tăng số lượng -->
                                    </div>
                                </td>
                                
                                <td id="subtotal-<?php echo $item['id']; ?>"><!-- Ô hiển thị tổng tiền; id gắn theo id sản phẩm để JS dễ cập nhật -->
                                    <?php echo number_format($subtotal, 0, ',', '.'); ?> VND</td><!-- In tổng tiền theo định dạng VN (1.000.000) và thêm VND -->
                                
                                <td><!-- Ô chứa nút xóa -->
                                    <a href="remove_from_cart.php?id=<?php echo $item['id']; ?>" class="btn-remove" title="Xóa sản phẩm này khỏi giỏ hàng" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm <?php echo htmlspecialchars($item['name']); ?>?');"> <!-- Link xóa sản phẩm theo id -->
                                       <i class="fas fa-trash-alt"></i> <!-- Icon thùng rác -->
                                    </a><!-- Kết thúc link -->
                                </td> <!-- Kết thúc ô chứa nút -->
                            </tr> <!-- Kết thúc 1 dòng sản phẩm trong bảng -->
                        <?php endforeach; ?> <!-- Kết thúc vòng lặp foreach hiển thị các sản phẩm trong giỏ -->
                    </tbody> <!-- Kết thúc phần thân bảng -->
                </table> <!-- Kết thúc bảng giỏ hàng -->

                <div class="cart-total">  <!-- Khối hiển thị tổng tiền toàn bộ giỏ hàng -->
                    Tổng tiền: <span id="cart-total-display"><?php echo number_format($total_price, 0, ',', '.'); ?></span> VND<!-- Hiển thị tổng tiền giỏ hàng theo dạng 1.000.000 -->
                </div>

                <div style="text-align: right; margin-top: 15px;" > <!-- Khối chứa nút điều hướng, căn phải -->
                   <a href="shop.php" class="btn-action btn-continue" >TIẾP TỤC MUA SẮM</a><!-- Nút quay về trang chủ để mua thêm -->
                    <a href="checkout.php" class="btn-checkout">TIẾN HÀNH THANH TOÁN</a> <!-- Nút chuyển sang trang thanh toán -->
                </div>
            <?php endif; ?><!-- Đóng phần kiểm tra nếu giỏ hàng có sản phẩm -->

        </div><!-- Kết thúc container giỏ hàng -->
    </main><!-- Kết thúc phần nội dung chính -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {  // Chờ toàn bộ trang tải xong mới chạy script//
            const qtyControls = document.querySelectorAll('.quantity-control'); // Lấy tất cả khối điều chỉnh số lượng//

            qtyControls.forEach(control => {// Lặp qua từng sản phẩm//
                const input = control.querySelector('.input-qty');// Lấy ô input hiển thị số lượng//
                const productId = control.dataset.id;// Lấy id sản phẩm từ thuộc tính data-id//
                
                control.addEventListener('click', function(e) {// Lắng nghe sự kiện click trong khối điều chỉnh số lượng//
                    if (!e.target.classList.contains('btn-qty')) return;// Nếu click không phải nút + hoặc -, thì k dc thực thi//

                    let currentQty = parseInt(input.value);// Lấy số lượng hiện tại và chuyển về dạng số//
                    const action = e.target.dataset.action;// Lấy hành động (plus hoặc minus) từ nút bấm//

                    if (action === 'plus') {// Nếu người dùng bấm nút +//
                        currentQty += 1;// tăng số lượng lên 1//
                    } else if (action === 'minus' && currentQty > 1) {// Nếu bấm - và số lượng > 1//
                        currentQty -= 1;// Giảm số lượng xuống 1//
                    } else if (action === 'minus' && currentQty === 1) {// Nếu bấm - khi số lượng = 1//
                        if(confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                            window.location.href = `remove_from_cart.php?id=${productId}`;// Chuyển đến trang xóa sản phẩm//
                        }
                        return; // Thoát mà không cập nhật số lượng//
                    }

                    input.value = currentQty;// Gán lại số lượng mới vào ô input để hiển thị cho người dùng//
                    updateCart(productId, currentQty); // Gọi hàm cập nhật giỏ hàng lên server (AJAX)//
                });
            });
            
            function updateCart(id, quantity) {// Hàm gửi yêu cầu cập nhật số lượng sản phẩm lên server//
                const xhr = new XMLHttpRequest();// Tạo đối tượng AJAX//
                xhr.open('POST', 'update_cart.php', true); // Mở kết nối POST tới file xử lý update_cart.php//
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');// Gửi dữ liệu dạng form//

                xhr.onload = function() {// Xử lý khi server trả về kết quả//
                    if (xhr.status === 200) {// Nếu kết nối OK//
                        const response = JSON.parse(xhr.responseText);// Chuyển JSON từ server thành object. JSON là chuỗi văn bản(xhr.responseText)
                        if(response.success) {// Nếu server báo cập nhật thành công//
                            // Cập nhật Tổng tiền giỏ hàng
                            document.getElementById('cart-total-display').innerText = response.total_price_formatted;// Hiển thị lại tổng tiền đã format sẵn (1.200.000)
                            
                            // Cập nhật Thành tiền cho dòng sản phẩm đó
                            document.getElementById(`subtotal-${id}`).innerText = response.item_subtotal_formatted + ' VND';// Thay đổi subtotal theo sản phẩm được cập nhật//
                        } else {
                            alert('Lỗi cập nhật giỏ hàng: ' + response.message);// Báo lỗi nếu server trả về lỗi//
                        }
                    } else {
                        alert('Lỗi kết nối máy chủ.');// Báo lỗi nếu không kết nối được server//
                    }
                };
                xhr.send(`id=${id}&quantity=${quantity}`);// Gửi dữ liệu id và số lượng lên server//
            }
        });
    </script>
<?php include "includes/footer.php"; ?>
    </body>

</html>