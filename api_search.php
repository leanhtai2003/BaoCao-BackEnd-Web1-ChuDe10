<?php
// api_search.php - Nhận từ khóa và trả về kết quả của cả 3 phương pháp để đối chiếu
require_once 'config.php';
require_once 'EmbeddingService.php';

header('Content-Type: application/json; charset=utf-8');

$query = trim($_GET['q'] ?? '');
$alpha = isset($_GET['alpha']) ? (float)$_GET['alpha'] : 0.4; // Mặc định 40% Fulltext, 60% AI
$alpha = max(0.0, min(1.0, $alpha));

if ($query === '') {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập nội dung tìm kiếm.']);
    exit;
}
$startTime = microtime(true);
$pdo = getDBConnection();

// BƯỚC 1: Truy vấn MySQL FULLTEXT SEARCH
$sql = "SELECT id, sku, name, category, price, description, embedding,
               MATCH(sku, name, description) AGAINST(:q IN NATURAL LANGUAGE MODE) AS raw_ft_score
        FROM products";
$stmt = $pdo->prepare($sql);
$stmt->execute([':q' => $query]);
$rows = $stmt->fetchAll();

// Tìm điểm FULLTEXT lớn nhất để chuẩn hóa Min-Max về đoạn [0, 1]
$maxFtScore = 0.0;
foreach ($rows as $r) {
    if ((float)$r['raw_ft_score'] > $maxFtScore) {
        $maxFtScore = (float)$r['raw_ft_score'];
    }
}

// BƯỚC 2: Gọi Gemini API lấy Vector của câu truy vấn
$queryVector = EmbeddingService::getEmbedding($query);

// BƯỚC 3: Tính điểm cho từng sản phẩm
$processedItems = [];
foreach ($rows as $r) {
    $rawFt = (float)$r['raw_ft_score'];
    $normFtScore = ($maxFtScore > 0) ? ($rawFt / $maxFtScore) : 0.0;

    $semanticScore = 0.0;
    if ($queryVector && !empty($r['embedding'])) {
        $productVector = json_decode($r['embedding'], true);
        if (is_array($productVector)) {
            $semanticScore = EmbeddingService::cosineSimilarity($queryVector, $productVector);
        }
    }

    // Công thức Hybrid Search
    $hybridScore = ($alpha * $normFtScore) + ((1.0 - $alpha) * $semanticScore);

    $processedItems[] = [
        'id' => $r['id'],
        'sku' => $r['sku'],
        'name' => $r['name'],
        'category' => $r['category'],
        'price' => number_format((float)$r['price'], 0, ',', '.') . ' đ',
        'description' => $r['description'],
        'raw_ft_score' => round($rawFt, 4),
        'ft_score' => round($normFtScore, 4),
        'semantic_score' => round($semanticScore, 4),
        'hybrid_score' => round($hybridScore, 4)
    ];
}

// BƯỚC 4: Phân loại thành 3 danh sách cho 3 cột trên UI
// 1. Cột Fulltext: Chỉ lấy sản phẩm có ft_score > 0, sắp xếp giảm dần
$fulltextList = array_values(array_filter($processedItems, fn($item) => $item['raw_ft_score'] > 0));
usort($fulltextList, fn($a, $b) => $b['ft_score'] <=> $a['ft_score']);

// 2. Cột Semantic: Sắp xếp theo điểm AI giảm dần (lọc ngưỡng tối thiểu 0.35 để loại nhiễu)
$semanticList = array_values(array_filter($processedItems, fn($item) => $item['semantic_score'] >= 0.35));
usort($semanticList, fn($a, $b) => $b['semantic_score'] <=> $a['semantic_score']);

// 3. Cột Hybrid: Kết hợp cả hai, sắp xếp theo hybrid_score giảm dần
$hybridList = array_values(array_filter($processedItems, fn($item) => $item['hybrid_score'] >= 0.20));
usort($hybridList, fn($a, $b) => $b['hybrid_score'] <=> $a['hybrid_score']);

$executionTime = round((microtime(true) - $startTime) * 1000, 2);

echo json_encode([
    'status' => 'success',
    'query' => $query,
    'alpha' => $alpha,
    'ai_connected' => ($queryVector !== null),
    'execution_time_ms' => $executionTime,
    'fulltext_results' => $fulltextList,
    'semantic_results' => $semanticList,
    'hybrid_results' => $hybridList
], JSON_UNESCAPED_UNICODE);