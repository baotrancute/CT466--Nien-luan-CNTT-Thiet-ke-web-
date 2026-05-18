<?php
//file mới gộp ft xong ở mỗi trang incule là dc
if (!isset($pdo)) { // Kiểm tra nếu biến kết nối database $pdo chưa tồn tại
    include __DIR__ . '/db_config.php'; // Nhúng file cấu hình để tạo kết nối database (dùng __DIR__ để lấy đúng đường dẫn thư mục hiện tại)
}

$themeQuery = $pdo->query("SELECT setting_key, setting_value FROM theme_settings"); // Truy vấn tất cả cấu hình giao diện (theme) từ bảng theme_settings
$theme = []; // Khởi tạo mảng rỗng để lưu các cấu hình theme

while ($row = $themeQuery->fetch(PDO::FETCH_ASSOC)) { // Duyệt từng dòng dữ liệu lấy được từ database dưới dạng mảng kết hợp
    $theme[$row['setting_key']] = $row['setting_value']; // Gán giá trị vào mảng $theme với key là setting_key và value là setting_value
}

$footerBg   = $theme['footer_bg_color']   ?? '#111'; // Lấy màu nền footer từ theme, nếu không có thì dùng mặc định #111
$footerText = $theme['footer_text_color'] ?? '#ffffff'; // Lấy màu chữ footer từ theme, nếu không có thì dùng mặc định màu trắng
$footerContent = $theme['footer_text_content'] ?? '&copy; 2025 Flower Shop.'; // Lấy nội dung footer từ theme, nếu không có thì dùng nội dung mặc định
?>
<style>
.footer {
    background-color: <?= $footerBg ?>;
    color: <?= $footerText ?>;
    text-align: center;
    padding: 40px 10px;
    font-size: 15px;
}

/* ===== CHAT BOX ===== */

.chat-box{
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 300px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    display: none;
    flex-direction: column;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.chat-header{
    background:#1e88e5;
    color:white;
    padding:10px;
    border-radius:10px 10px 0 0;
    font-weight:bold;
}

.chat-body{
    height:200px;
    padding:10px;
    overflow-y:auto;
    font-size:14px;
}

.chat-input{
    display:flex;
    border-top:1px solid #ddd;
}

.chat-input input{
    flex:1;
    border:none;
    padding:8px;
}

.chat-input button{
    border:none;
    background:#1e88e5;
    color:white;
    padding:8px 12px;
    cursor:pointer;
}
</style>


<footer class="footer">
  <p><?= $footerContent ?></p>
</footer>
<!-- CHATBOX, thim mới chức năng hỗ trợ ng dùng -->
<div class="chat-box" id="chatBox">

    <div class="chat-header">
        Hỗ trợ khách hàng
        <span onclick="toggleChat()">✖</span>
    </div>

    <div class="chat-body" id="chatMessages">
        Xin chào 👋 Shop hoa có thể giúp gì cho bạn?
    </div>

    <div class="chat-input">
        <input type="text" id="chatInput" placeholder="Nhập tin nhắn...">
        <button onclick="sendMessage()">Gửi</button>
    </div>

</div>

<script>

const chatBtn = document.querySelector(".chat-btn"); // Lấy phần tử đầu tiên có class "chat-btn" (nút mở chat)
const chatbox = document.getElementById("chatBox"); // Lấy phần tử có id "chatBox" (khung chat)

if(chatBtn){ // Kiểm tra nếu tồn tại nút chat trên trang
    chatBtn.onclick = () =>{ // Gán sự kiện click cho nút chat
        chatbox.style.display =
        chatbox.style.display === "flex" ? "none" : "flex"; // Nếu đang hiển thị (flex) thì ẩn đi (none), nếu đang ẩn thì hiện ra (flex)
    }
}

function toggleChat(){ // Tạo hàm toggleChat để bật/tắt khung chat
    const chat = document.getElementById("chatBox"); // Lấy lại phần tử chatBox

    if(chat.style.display === "flex"){ // Nếu đang hiển thị
        chat.style.display = "none"; // Ẩn khung chat
    }else{ // Nếu đang ẩn
        chat.style.display = "flex"; // Hiện khung chat
    }
}
function sendMessage(){ // Định nghĩa hàm gửi tin nhắn

    const input = document.getElementById("chatInput"); // Lấy ô input nơi người dùng nhập nội dung chat
    const message = input.value; // Lấy giá trị (nội dung tin nhắn) từ input

    if(message.trim() === "") return; // Nếu nội dung rỗng (sau khi bỏ khoảng trắng) thì dừng hàm, không gửi

    fetch("send_chat.php",{ // Gửi request đến file send_chat.php để xử lý lưu tin nhắn
        method:"POST", // Sử dụng phương thức POST để gửi dữ liệu
        headers:{
            "Content-Type":"application/x-www-form-urlencoded" // Khai báo kiểu dữ liệu gửi đi là form URL encoded
        },
        body:"message="+encodeURIComponent(message) // Gửi nội dung tin nhắn, encode để tránh lỗi ký tự đặc biệt
    })
    .then(res=>res.json()) // Nhận phản hồi từ server và chuyển sang dạng JSON
    .then(data=>{ // Xử lý dữ liệu JSON trả về từ server

        if(data.status === "success"){ // Kiểm tra nếu server trả về trạng thái thành công

            const box = document.getElementById("chatMessages"); // Lấy khung hiển thị danh sách tin nhắn

            box.innerHTML += "<div>👤 "+message+"</div>"; // Thêm tin nhắn vừa gửi vào cuối khung chat (hiển thị phía người dùng)

            input.value=""; // Xóa nội dung trong ô input sau khi gửi xong

            box.scrollTop = box.scrollHeight; // Tự động cuộn xuống cuối khung chat để thấy tin nhắn mới nhất
        }

    }); // Kết thúc xử lý promise của fetch

}

</script>