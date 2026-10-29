<?php
// config.php - Cấu hình Database & API Key
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'hybrid_search_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Khai báo API Key (Đã tích hợp cơ chế tự động sinh Vector 768 chiều dự phòng)
define('GEMINI_API_KEY', 'LOCAL_AI_EMBEDDING_768_READY');

function getDBConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die(json_encode(['status' => 'error', 'message' => 'Lỗi kết nối MySQL: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}