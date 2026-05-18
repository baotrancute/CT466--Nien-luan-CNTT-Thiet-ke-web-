<?php
// FILE: theme.php - qli cấu hình gdien cho admin (phải có trang này)

session_start();


ini_set('display_errors', 1); // Bật hiển thị lỗi PHP trên trình duyệt
ini_set('display_startup_errors', 1); // Hiển thị lỗi xảy ra trong quá trình khởi động PHP
error_reporting(E_ALL); // Báo tất cả các loại lỗi, cảnh báo và thông tin (rất hữu ích khi debug)


// Bao gồm file kết nối DB. Nếu DB lỗi, chương trình sẽ dừng tại db_config.php
include '../db_config.php'; 

$message = '';


// 🚨 PHẦN DEBUG: KIỂM TRA BIẾN KẾT NỐI PDO
// Dùng goto để chuyển thẳng đến phần render form nếu $pdo bị lỗi
if (!isset($pdo) || !($pdo instanceof PDO)) { // Kiểm tra xem biến $pdo có tồn tại và là đối tượng PDO không
    $message = "❌ LỖI KẾT NỐI DB: Biến \$pdo không được khởi tạo từ db_config.php. Vui lòng kiểm tra lại file đó."; // Nếu không, thông báo lỗi
    goto render_form; // Chuyển tới nhãn render_form để hiển thị form và thông báo lỗi
}

// Hàm xử lý Upload ảnh
function upload_image($file_key, $target_dir = 'images/') { 
    // Hàm upload hình ảnh, $file_key là tên input file, $target_dir là thư mục lưu ảnh
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == 0) { 
        // Chỉ xử lý nếu file được upload thành công (error = 0)
        $file = $_FILES[$file_key];
        
        $max_size = 5 * 1024 * 1024; // Giới hạn dung lượng 5MB
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif']; // Các định dạng ảnh cho phép

        if ($file['size'] > $max_size) {
            return "ERROR: Tệp quá lớn (max 5MB)."; // Thông báo nếu file vượt quá giới hạn
        }
        if (!in_array($file['type'], $allowed_types)) {
            return "ERROR: Chỉ cho phép tệp JPG, PNG, GIF."; // Thông báo nếu file không đúng định dạng
        }

        
      $ext = pathinfo($file['name'], PATHINFO_EXTENSION); // Lấy phần mở rộng file (jpg, png,...)
        $new_filename = uniqid('theme_') . '.' . $ext; // Tạo tên file mới duy nhất, tránh trùng tên
        $target_file = $target_dir . $new_filename; // Đường dẫn đầy đủ để lưu file

        if (!is_dir($target_dir)) { // Nếu thư mục chưa tồn tại
            // Tạo thư mục với quyền 0777 (test), nên dùng 0755 cho production
            if (!mkdir($target_dir, 0777, true)) {
                return "ERROR: Không thể tạo thư mục '$target_dir'. Kiểm tra quyền thư mục cha.";
            }
        }

        if (move_uploaded_file($file['tmp_name'], $target_file)) { 
            // Di chuyển file từ tmp lên thư mục đích
           return 'images/' . $new_filename; // Trả về đường dẫn file thành công
        } else {
            return "ERROR: Không thể di chuyển tệp. Kiểm tra quyền ghi của thư mục 'images'.";
        }

    }
    return null; // Không có tệp nào được upload hoặc lỗi khác (error != 0)
}

// Hàm lấy tất cả settings
function get_settings($pdo) {
    // Lấy tất cả cặp key => value từ bảng theme_settings
    // PDOException sẽ được ném nếu bảng không tồn tại
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM theme_settings"); 
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // fetch dưới dạng [key => value]
    return $settings; // Trả về mảng settings
}

// Xử lý POST request khi Admin lưu thay đổi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// ==========================================================
    /* 🚨 PHẦN DEBUG: HIỂN THỊ DỮ LIỆU FORM ĐÃ GỬI
    echo '<h2>DEBUG: Dữ liệu POST và Upload</h2>';
    echo '<h3>$_POST (Text/Color/Number Data)</h3>';
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';
    echo '<h3>$_FILES (File Upload Data)</h3>';
    echo '<pre>';
    print_r($_FILES);
    echo '</pre>';
    echo '<hr>';
    // ==========================================================*/

try { // Bắt đầu khối try để xử lý ngoại lệ (exception) nếu có lỗi xảy ra
  $pdo->beginTransaction(); // Bắt đầu transaction (giao dịch), giúp có thể rollback nếu xảy ra lỗi

if (isset($_POST['toggle_banner'])) { // Kiểm tra nếu form gửi lên có yêu cầu bật/tắt banner
    $current = $pdo->query("
        SELECT setting_value 
        FROM theme_settings 
        WHERE setting_key = 'banner_abc_enabled'
    ")->fetchColumn(); // Truy vấn giá trị hiện tại của banner (bật/tắt) và lấy trực tiếp 1 giá trị cột

    $new = ($current == '1') ? '0' : '1'; // Nếu đang bật (1) thì chuyển thành tắt (0), ngược lại thì bật (1)

    $stmt = $pdo->prepare("
        INSERT INTO theme_settings (setting_key, setting_value)
        VALUES ('banner_abc_enabled', :v)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    "); // Chuẩn bị câu lệnh: nếu chưa có thì insert, nếu đã tồn tại thì update giá trị

    $stmt->execute(['v' => $new]); // Thực thi câu lệnh, truyền giá trị mới ($new) vào placeholder :v
}

        // Danh sách các trường upload và cột DB tương ứng
        $image_keys = [
            'logo_upload' => 'logo_path',       // input file logo → cột logo_path
            'hero_image_upload' => 'hero_image_path', // input file hero → cột hero_image_path
            'banner_a_upload' => 'banner_a_path',     // input file banner A → cột banner_a_path
            'banner_b_upload' => 'banner_b_path',     // input file banner B → cột banner_b_path
            'banner_c_upload' => 'banner_c_path',     // input file banner C → cột banner_c_path
        ];

       foreach ($image_keys as $file_key => $db_key) {
            $path_or_error = upload_image($file_key,'../images/'); // Gọi hàm upload, trả về đường dẫn hoặc lỗi
            
            if (is_string($path_or_error) && strpos($path_or_error, 'ERROR:') === 0) {
                // Nếu upload gặp lỗi → ném Exception để rollback giao dịch
                throw new Exception("Lỗi Upload cho {$db_key}: " . $path_or_error);
            }
            
            if ($path_or_error) {
                // Nếu upload thành công → lưu đường dẫn vào DB (nếu đã tồn tại thì cập nhật)
                $update_sql = "INSERT INTO theme_settings (setting_key, setting_value) 
                            VALUES (:key, :path) 
                            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
                $stmt = $pdo->prepare($update_sql);
                $stmt->execute(['key' => $db_key, 'path' => $path_or_error]);
                }
            }

        
        // --- 2. Xử lý các Settings dạng Text/Color/Number ---
      $settings_to_update = [
                // Header & Navigation: cấu hình màu nền, màu chữ, chiều cao và padding của header
                'header_bg_color' => $_POST['header_bg_color'] ?? '#2c3e50', // Nếu không gửi thì mặc định #2c3e50
                'header_text_color' => $_POST['header_text_color'] ?? 'white', // màu chữ mặc định trắng
                'header_height' => $_POST['header_height'] ?? '100px', // chiều cao header mặc định
                'header_padding' => $_POST['header_padding'] ?? '0 50px', // khoảng cách padding mặc định
                
                // Hero Section: tiêu đề và dòng phụ của phần hero
                'hero_headline' => $_POST['hero_headline'] ?? 'Món Quà Từ Thiên Nhiên, Trao Trọn Yêu Thương',
                'hero_subtext' => $_POST['hero_subtext'] ?? 'Tuyển chọn những bó hoa tươi thắm nhất cho mọi dịp.',
                // Hero colors
                'hero_title_color' => $_POST['hero_title_color'] ?? '#ffffff',
                'hero_text_color'  => $_POST['hero_text_color'] ?? '#f2f2f2',

                // Footer: màu nền và nội dung footer
                'footer_bg_color' => $_POST['footer_bg_color'] ?? '#2c3e50',
                'footer_text_color' => $_POST['footer_text_color'] ?? '#ffffff',
                'footer_text_content' => $_POST['footer_text_content'] ?? '&copy; 2025 Flower Shop.',
            ];

        
                $update_sql = "INSERT INTO theme_settings (setting_key, setting_value) 
                        VALUES (:key, :value) 
                        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            // Chuẩn bị câu lệnh PDO
            $stmt = $pdo->prepare($update_sql);

            // Lặp qua từng cài đặt trong mảng và lưu vào DB
            foreach ($settings_to_update as $key => $value) {
                $stmt->execute(['key' => $key, 'value' => $value]); // Bind giá trị an toàn
            }

            $pdo->commit(); // Xác nhận tất cả thay đổi trong transaction
            $message = "✅ Đã lưu cấu hình giao diện thành công!";

            } catch (Exception $e) {
                // Nếu có lỗi, rollback transaction để DB không bị nửa chừng
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
    }

        // Thông báo lỗi CSDL hoặc lỗi Upload
        $message = "❌ LỖI TRUY VẤN CSDL hoặc UPLOAD: " . $e->getMessage();
    }
}

render_form:
// Nhãn để điều hướng `goto render_form` nếu cần
$settings = []; // Khởi tạo mảng rỗng để chứa các cài đặt
// Chỉ lấy dữ liệu nếu $pdo tồn tại và là đối tượng PDO hợp lệ
if (isset($pdo) && ($pdo instanceof PDO)) {
    try {
        $settings = get_settings($pdo); // Gọi hàm lấy cấu hình từ DB
    } catch (Exception $e) {
        // Nếu có lỗi (ví dụ bảng chưa tồn tại), gán thông báo lỗi
        $message = (strpos($message, 'LỖI') === false) 
                   ? "❌ LỖI TRUY VẤN CSDL: Không thể đọc settings. Chi tiết: " . $e->getMessage() 
                   : $message; // Nếu đã có lỗi trước đó thì giữ nguyên
    }
}

// Hàm hỗ trợ lấy giá trị với giá trị mặc định
function get_setting($settings, $key, $default) {
    return htmlspecialchars($settings[$key] ?? $default);
    // Lấy giá trị cài đặt từ mảng $settings theo $key
    // Nếu không tồn tại, trả về $default
    // htmlspecialchars để tránh XSS khi hiển thị trong HTML
}
?>
<!DOCTYPE html> <!-- Khai báo HTML5 -->
<html lang="vi"> <!-- Ngôn ngữ trang -->
<head>
    <meta charset="UTF-8"> <!-- Mã hóa UTF-8 -->
    <title>Quản lý Giao Diện</title> <!-- Tiêu đề trang -->
<style>

/* ===== RESET ===== */
*{
box-sizing:border-box;
}

html,body{
margin:0;
padding:0;
font-family:'Segoe UI',Tahoma,sans-serif;
}

/* ===== BACKGROUND PASTEL ===== */
body{
padding:40px;

background:
radial-gradient(circle at 15% 25%, rgba(255,214,230,0.9), transparent 55%),
radial-gradient(circle at 85% 20%, rgba(255,241,200,0.9), transparent 55%),
radial-gradient(circle at 50% 80%, rgba(255,220,235,0.8), transparent 60%);

background-color:#fff7ec;
background-attachment:fixed;
}

/* ===== MAIN TITLE ===== */
h1{
text-align:center;
margin-bottom:35px;
font-size:32px;
color:#333;
}

/* ===== CARD CONTAINER ===== */
form{
max-width:1100px;
margin:auto;

background:white;
padding:35px 45px;

border-radius:16px;
box-shadow:0 15px 40px rgba(0,0,0,0.08);
}

/* ===== SECTION TITLE ===== */
h2{
margin-top:35px;
margin-bottom:20px;

padding-bottom:8px;

border-bottom:2px solid #eee;

font-size:20px;
color:#444;
}

/* ===== LABEL ===== */
label{
display:block;
font-weight:600;
margin-bottom:6px;
color:#333;
}

/* ===== INPUT ===== */
input[type="text"],
textarea{

width:100%;

padding:10px;

border:1px solid #ddd;
border-radius:6px;

font-size:14px;
transition:0.2s;
}

input[type="text"]:focus,
textarea:focus{
border-color:#e91e63;
outline:none;
}

/* ===== COLOR INPUT ===== */
input[type="color"]{
width:60px;
height:35px;
border:none;
cursor:pointer;
}

/* ===== FILE INPUT ===== */
input[type="file"]{
margin-top:5px;
}

/* ===== FLEX ROW ===== */
.flex-row{
display:flex;
gap:30px;
flex-wrap:wrap;
margin-bottom:20px;
}

.flex-row > div{
flex:1;
min-width:220px;
}

/* ===== PATH TEXT ===== */
p{
font-size:13px;
color:#777;
margin-top:5px;
}

/* ===== BANNER GRID ===== */
#banner-abc-wrapper{
margin-top:20px;
}

#banner-abc-wrapper div{
margin-bottom:15px;
}

/* ===== MESSAGE ===== */
.success-msg{
background:#e8f8f0;
color:#2e7d32;
padding:12px;
border-radius:6px;
margin-bottom:20px;
}

.error-msg{
background:#fdecea;
color:#c62828;
padding:12px;
border-radius:6px;
margin-bottom:20px;
}

/* ===== BUTTON ===== */
button{
padding:12px 24px;
background:#e91e63;
color:white;

border:none;
border-radius:6px;

cursor:pointer;
font-weight:bold;

transition:0.25s;
}

button:hover{
background:#d81b60;
}

/* ===== LINK BUTTON ===== */
a.btn-checkout{

padding:12px 24px;
margin-left:10px;

background:#555;
color:white;

border-radius:6px;
text-decoration:none;

font-weight:bold;
transition:0.25s;
}

a.btn-checkout:hover{
background:#333;
}

/* ===== SEPARATOR ===== */
hr{
border:none;
border-top:1px solid #eee;
margin:30px 0;
}

</style>
</head>
<body>
   <h1>🛠️ Quản lý Giao Diện (theme.creator)</h1> <!-- Tiêu đề chính của trang quản lý giao diện -->

    <?php if ($message): ?> <!-- Kiểm tra nếu có thông báo ($message không rỗng) -->
    <p class="<?php echo (strpos($message, '✅') !== false) ? 'success-msg' : 'error-msg'; ?>"> <!-- Nếu message chứa ký tự '✅' thì dùng class success, ngược lại dùng error -->
        <?php echo $message; ?> <!-- Hiển thị nội dung thông báo -->
    </p>
    <?php endif; ?> <!-- Kết thúc điều kiện message -->
    <form method="POST" enctype="multipart/form-data"> <!-- Form gửi dữ liệu bằng POST & hỗ trợ upload file -->

    <h2>1. Header & Navigation (Thanh đầu trang)</h2> <!-- Tiêu đề phần chỉnh Header -->

    <div style="display: flex; gap: 20px; margin-bottom: 15px;"> <!-- Chia hai cột bằng flex với khoảng cách 20px -->

        <div> <!-- Cột 1 -->
            <label for="header_bg_color">Màu nền Header:</label> <!-- Label chọn màu nền header -->
            <input type="color" name="header_bg_color" id="header_bg_color"
                   value="<?php echo get_setting($settings, 'header_bg_color', '#2c3e50'); ?>"> <!-- Input chọn màu, lấy giá trị trong DB hoặc mặc định -->
        </div>
        
       <div style="margin-bottom:15px;">
    <label for="logo_upload">Upload Logo:</label>
    <input type="file" name="logo_upload" id="logo_upload" accept="image/*">
    <p>Logo hiện tại: 
        <?= get_setting($settings, 'logo_path', 'Chưa có logo'); ?>
    </p>
</div>
<!-- Kết thúc cột thứ 2 -->
        </div> <!-- Kết thúc nhóm flex đầu tiên -->

        <div style="display: flex; gap: 20px; margin-bottom: 15px;"> <!-- Nhóm flex thứ 2 cho chiều cao & padding -->
            <div> <!-- Cột 1 -->
                <label for="header_height">Chiều cao Logo/Header (vd: 100px):</label> <!-- Nhãn nhập chiều cao header/logo -->
                <input type="text" name="header_height" id="header_height"
                    value="<?php echo get_setting($settings, 'header_height', '100px'); ?>"> <!-- Input text, giá trị từ DB hoặc mặc định 100px -->
            </div>

            <div> <!-- Cột 2 -->
                <label for="header_padding">Padding (Lề) Header (vd: 0 50px):</label> <!-- Nhãn nhập padding của header -->
                <input type="text" name="header_padding" id="header_padding"
                    value="<?php echo get_setting($settings, 'header_padding', '0 50px'); ?>"> <!-- Input padding, mặc định '0 50px' -->
          </div>

</div> <!-- Kết thúc nhóm flex thứ 2 -->

         
            <hr style="margin: 20px 0;"> <!-- Đường gạch phân cách, trên và dưới 20px -->


       <h2>2. Hero Section (Banner Chính)</h2> <!-- Tiêu đề phần chỉnh sửa Hero/Banner -->
            <div style="display: flex; gap: 20px; margin-bottom: 15px;">
    <div>
        <label for="hero_title_color">Màu chữ tiêu đề Hero:</label>
        <input type="color" name="hero_title_color" id="hero_title_color"
            value="<?php echo get_setting($settings, 'hero_title_color', '#ffffff'); ?>">
    </div>

    <div>
        <label for="hero_text_color">Màu chữ mô tả Hero:</label>
        <input type="color" name="hero_text_color" id="hero_text_color"
            value="<?php echo get_setting($settings, 'hero_text_color', '#f2f2f2'); ?>">
    </div>
</div>

            <div style="margin-bottom: 15px;"> <!-- Khối chỉnh nội dung headline -->
                <label for="hero_headline">Nội dung/Tiêu đề (Headline text):</label> <!-- Nhãn nhập headline -->
                <input type="text" name="hero_headline" id="hero_headline" style="width: 95%;"
                    value="<?php echo get_setting($settings, 'hero_headline', 'Món Quà Từ Thiên Nhiên, Trao Trọn Yêu Thương'); ?>"> <!-- Input text, lấy từ DB hoặc giá trị mặc định -->
            </div>
            <div style="margin-bottom: 15px;"> <!-- Khối nhập subtext -->
                <label for="hero_subtext">Nội dung phụ (Subtext):</label> <!-- Nhãn nhập subtext -->
                <input type="text" name="hero_subtext" id="hero_subtext" style="width: 95%;"
                    value="<?php echo get_setting($settings, 'hero_subtext', 'Tuyển chọn những bó hoa tươi thắm nhất cho mọi dịp.'); ?>"> <!-- Input text cho subtext -->
            </div>
            <div style="margin-bottom: 15px;"> <!-- Khối upload ảnh nền banner -->
                <label for="hero_image_upload">Thay ảnh nền Banner (hiện tại: <?php echo get_setting($settings, 'hero_image_path', 'Mặc định'); ?>):</label> <!-- Nhãn + đường dẫn ảnh hiện tại -->
                <input type="file" name="hero_image_upload" id="hero_image_upload" accept="image/*"> <!-- Input upload ảnh mới -->
            </div>
       <hr style="margin: 20px 0;"> <!-- Đường phân cách giữa các mục, trên dưới 20px -->

<div id="banner-abc-wrapper">
        <h2>3. Banner Phụ (A, B, C)</h2> <!-- Tiêu đề phần quản lý 3 banner phụ -->
        <p style="font-style: italic;">Các banner này sẽ được chèn vào Hero Section (Banner Chính) ở các vị trí cố định (tham khảo trong index.php).</p> <!-- Ghi chú mô tả chức năng banner phụ -->
        <div style="display: flex; gap: 20px; flex-wrap: wrap;"> <!-- Chia bố cục thành nhiều ô, tự xuống dòng nếu thiếu chỗ -->
            <div style="width: 30%;"> <!-- Ô banner A, kích thước chiếm 30% khung -->
                <label for="banner_a_upload">Banner phụ A:</label> <!-- Nhãn upload banner A -->
                <input type="file" name="banner_a_upload" id="banner_a_upload" accept="image/*"> <!-- Upload file hình ảnh banner A -->
                <p>Path: <?php echo get_setting($settings, 'banner_a_path', 'Chưa có'); ?></p> <!-- Hiển thị đường dẫn banner hiện tại hoặc "Chưa có" -->
            </div>
</div>
    <div style="width: 30%;"> <!-- Ô banner B, chiếm 30% chiều ngang -->
        <label for="banner_b_upload">Banner phụ B:</label> <!-- Nhãn upload banner B -->
        <input type="file" name="banner_b_upload" id="banner_b_upload" accept="image/*"> <!-- Input tải lên hình banner B -->
        <p>Path: <?php echo get_setting($settings, 'banner_b_path', 'Chưa có'); ?></p> <!-- Đường dẫn ảnh đang dùng hoặc 'Chưa có' -->
      </div>
    <div style="width: 30%;"> <!-- Ô banner C, chiếm 30% chiều ngang -->
        <label for="banner_c_upload">Banner phụ C:</label> <!-- Nhãn upload banner C -->
        <input type="file" name="banner_c_upload" id="banner_c_upload" accept="image/*"> <!-- Input tải lên hình banner C -->
        <p>Path: <?php echo get_setting($settings, 'banner_c_path', 'Chưa có'); ?></p> <!-- Đường dẫn banner hiện tại -->
    </div>
  <button type="submit"
        name="toggle_banner"
        value="1"
        style="margin-bottom:15px;padding:10px 20px;background:#333;color:#fff;border:none;border-radius:5px;">
    <?= (($settings['banner_abc_enabled'] ?? '1') == '1')
        ? 'ẨN banner A, B, C'
        : 'HIỆN banner A, B, C' ?>
</button>
        </div>
        <hr style="margin: 20px 0;"> <!-- Đường phân cách giữa mục Banner Phụ và mục tiếp theo, cách trên dưới 20px -->

      <h2>4. Footer</h2> <!-- Tiêu đề phần chỉnh sửa Footer -->
        <div style="margin-bottom: 15px;"> <!-- Khối chọn màu nền Footer -->
            <label for="footer_bg_color">Màu nền Footer:</label> <!-- Nhãn chọn màu nền -->
            <input type="color" name="footer_bg_color" id="footer_bg_color"
                value="<?php echo get_setting($settings, 'footer_bg_color', '#2c3e50'); ?>"> <!-- Input chọn màu, lấy từ DB hoặc mặc định #2c3e50 -->
        </div>
        <div style="margin-bottom: 15px;">
            <label for="footer_text_color">Màu chữ Footer:</label>
            <input type="color" name="footer_text_color" id="footer_text_color"
                value="<?php echo get_setting($settings, 'footer_text_color', '#ffffff'); ?>">
        </div>

         <div style="margin-bottom: 15px;"> <!-- Khối nhập nội dung bản quyền Footer -->
            <label for="footer_text_content">Nội dung Bản quyền:</label> <!-- Nhãn cho textarea -->
            <textarea name="footer_text_content" id="footer_text_content" style="width: 95%; height: 50px;"><?php echo $settings['footer_text_content'] ?? '&copy; 2025 Flower Shop.'; ?></textarea> <!-- Textarea nhập nội dung, lấy từ DB hoặc mặc định -->
        </div>

        <hr style="margin: 20px 0;"> <!-- Đường phân cách trước nút lưu -->

     <button type="submit" style="padding: 15px 30px; background-color: #e91e63; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 13px;"> <!-- Nút gửi form -->
            💾 LƯU GIAO DIỆN
        </button>

        <a href="products.php" style="padding: 15px 30px; background-color: #e91e63; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 15px; text-decoration: none;" class="btn-checkout">QUAY LẠI</a> <!-- Nút link quay lại trang products.php -->
        <a href="/flower_shop/index.php"style="padding: 15px 30px; background-color: #e91e63; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 15px; text-decoration: none;" class="btn-checkout">
                        <i class="fas fa-home"></i> HOME
                    </a>
     </form> <!-- Kết thúc form chỉnh sửa giao diện -->


</body>
</html>
