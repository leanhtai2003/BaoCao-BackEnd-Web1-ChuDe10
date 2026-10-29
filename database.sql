CREATE DATABASE IF NOT EXISTS hybrid_search_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hybrid_search_db;

DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(12, 0) NOT NULL,
    description TEXT NOT NULL,
    embedding LONGTEXT NULL COMMENT 'Lưu mảng JSON Vector 768 chiều từ AI',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Tạo chỉ mục FULLTEXT trên tên, mã SKU và mô tả sản phẩm
    FULLTEXT INDEX idx_fulltext_search (sku, name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;