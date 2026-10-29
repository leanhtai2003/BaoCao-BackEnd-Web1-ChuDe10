<?php
// seed.php - Chạy 1 lần trên trình duyệt: http://localhost/hybrid-search/seed.php
require_once 'config.php';
require_once 'EmbeddingService.php';

$pdo = getDBConnection();

$products = [
    [
        'sku' => 'LAP-ASUS-M1405YA',
        'name' => 'Laptop Asus Vivobook 14 OLED',
        'category' => 'Máy tính xách tay',
        'price' => 14990000,
        'description' => 'Máy tính xách tay mỏng nhẹ 1.4kg, thời lượng pin bền bỉ cả ngày, phù hợp cho sinh viên đại học và nhân viên văn phòng hay di chuyển.'
    ],
    [
        'sku' => 'PC-ROG-RTX4080S',
        'name' => 'PC Gaming ROG Strix Thủy Quái',
        'category' => 'Máy tính để bàn',
        'price' => 52000000,
        'description' => 'Cấu hình siêu khủng Core i9, card đồ họa rời RTX 4080 Super, tản nhiệt nước AIO, chuyên trị các tựa game nặng AAA và dựng phim 3D.'
    ],
    [
        'sku' => 'CHAIR-ERGO-PRO99',
        'name' => 'Ghế Công Thái Học Ergonomic Pro',
        'category' => 'Nội thất văn phòng',
        'price' => 4500000,
        'description' => 'Thiết kế lưới thoáng khí, tựa lưng điều chỉnh đa chiều ôm sát cột sống, giảm đau mỏi vai gáy và thoát vị đĩa đệm khi ngồi làm việc liên tục.'
    ],
    [
        'sku' => 'KB-KEYCHRON-K8P',
        'name' => 'Bàn phím cơ không dây Keychron K8 Pro',
        'category' => 'Phụ kiện',
        'price' => 2650000,
        'description' => 'Bàn phím cơ Red Switch gõ cực êm không gây tiếng ồn, kết nối Bluetooth 3 thiết bị, lót tiêu âm đầy đủ cho lập trình viên gõ code ban đêm.'
    ],
    [
        'sku' => 'MOUSE-MX-VERT01',
        'name' => 'Chuột dọc Logitech Lift Vertical',
        'category' => 'Phụ kiện',
        'price' => 1590000,
        'description' => 'Chuột thiết kế nghiêng 57 độ tự nhiên giúp cổ tay thư giãn, ngăn ngừa hội chứng ống cổ tay và tê tay cho dân thiết kế, văn phòng.'
    ],
    [
        'sku' => 'HEAD-SONY-XM5',
        'name' => 'Tai nghe chụp tai Sony WH-1000XM5',
        'category' => 'Âm thanh',
        'price' => 6990000,
        'description' => 'Công nghệ chống ồn chủ động ANC đỉnh cao, triệt tiêu hoàn toàn tạp âm quán cà phê và tiếng ồn xung quanh để tập trung tuyệt đối.'
    ],
    [
        'sku' => 'MON-LG-27UP850',
        'name' => 'Màn hình đồ họa LG UltraFine 27 inch 4K',
        'category' => 'Màn hình',
        'price' => 8900000,
        'description' => 'Độ phân giải 4K sắc nét, chuẩn màu 99% DCI-P3, tích hợp công nghệ chống ánh sáng xanh bảo vệ mắt không bị nhức mỏi khi nhìn lâu.'
    ],
    [
        'sku' => 'LAMP-BASEUS-EYE',
        'name' => 'Đèn treo màn hình Baseus i-Wok Pro',
        'category' => 'Phụ kiện',
        'price' => 750000,
        'description' => 'Chiếu sáng bất đối xứng chỉ rọi xuống mặt bàn, không gây chói phản chiếu lên màn hình máy tính, bảo vệ thị lực khi làm việc khuya.'
    ]
];

echo "<h2>Đang khởi tạo dữ liệu & gọi AI tạo Vector Embedding...</h2><hr>";

// Xóa dữ liệu cũ
$pdo->exec("TRUNCATE TABLE products");

$stmt = $pdo->prepare("INSERT INTO products (sku, name, category, price, description, embedding) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($products as $p) {
    // Ghép các trường thông tin để AI hiểu toàn diện sản phẩm
    $textToEmbed = "Mã: {$p['sku']}. Tên: {$p['name']}. Danh mục: {$p['category']}. Công dụng: {$p['description']}";
    $vector = EmbeddingService::getEmbedding($textToEmbed);

    $status = $vector ? "<span style='color:green;'>Thành công (" . count($vector) . " chiều)</span>" : "<span style='color:red;'>Lỗi API (Kiểm tra lại GEMINI_API_KEY trong config.php)</span>";

    $stmt->execute([
        $p['sku'],
        $p['name'],
        $p['category'],
        $p['price'],
        $p['description'],
        $vector ? json_encode($vector) : null
    ]);

    echo "<p><b>[{$p['sku']}] {$p['name']}</b> -> Vector: {$status}</p>";
    flush();
}

echo "<hr><h3>Hoàn tất! <a href='index.php'>Nhấn vào đây để mở trang Demo Hybrid Search</a></h3>";