# Chủ đề 10: Tìm kiếm thông minh - Kết hợp MySQL FULLTEXT SEARCH với AI Embeddings (Hybrid Search)

Dự án thuộc học phần **Back-end Web 1 (Tuần 9)**. Ứng dụng minh họa kỹ thuật **Hybrid Search** bằng cách kết hợp tìm kiếm từ khóa truyền thống (**MySQL FULLTEXT SEARCH**) và tìm kiếm ngữ nghĩa (**Google Gemini AI Embeddings**) trên nền tảng PHP & MySQL.

---

## 👥 Thông tin Nhóm thực hiện
| STT | Họ và Tên | MSSV | Vai trò & Nhiệm vụ |
| :-: | :--- | :---: | :--- |
| 1 | [Điền tên TV 1] | [Điền MSSV] | Nhóm trưởng, Thiết kế CSDL & MySQL FULLTEXT |
| 2 | [Điền tên TV 2] | [Điền MSSV] | Tích hợp Gemini Embedding API & Thuật toán Cosine |
| 3 | [Điền tên TV 3] | [Điền MSSV] | Xây dựng API Hybrid Search & Chuẩn hóa điểm số |
| 4 | [Điền tên TV 4] | [Điền MSSV] | Thiết kế Giao diện Demo 3 cột & Thuyết trình |

---

## 🎯 Tính năng cốt lõi & Kiến trúc hệ thống
1. **Lexical Search (MySQL FULLTEXT):** Tìm kiếm chính xác theo từ khóa, mã SKU, tên sản phẩm dựa trên chỉ mục `FULLTEXT INDEX` (`MATCH ... AGAINST IN NATURAL LANGUAGE MODE`).
2. **Semantic Search (AI Vector Embeddings):** Sử dụng mô hình `text-embedding-004` của Google Gemini để chuyển đổi văn bản thành mảng Vector 768 chiều và đo lường độ tương đồng ngữ cảnh bằng **Cosine Similarity**.
3. **Hybrid Ranking (Weighted Score Fusion):** Chuẩn hóa điểm số Min-Max về đoạn `[0, 1]` và kết hợp theo trọng số $\alpha$:
   $$\text{Hybrid Score} = \alpha \times \text{Normalized\_FT} + (1 - \alpha) \times \text{Cosine\_Score}$$
4. **Live Comparison Dashboard:** Giao diện chia 3 cột trực quan giúp đối chiếu trực tiếp kết quả giữa **MySQL FULLTEXT**, **AI Semantic Search** và **Hybrid Search**, kèm thanh trượt điều chỉnh trọng số $\alpha$ theo thời gian thực.

---

## 📂 Cấu trúc thư mục dự án
```text
hybrid-search/
├── database.sql          # Script khởi tạo CSDL và bảng products (có FULLTEXT Index)
├── config.php            # Cấu hình kết nối PDO MySQL & khai báo GEMINI_API_KEY
├── EmbeddingService.php  # Class gọi Gemini API bằng cURL & hàm tính Cosine Similarity
├── seed.php              # Script nạp dữ liệu mẫu & tự động sinh Vector lưu vào MySQL
├── api_search.php        # Backend API xử lý tìm kiếm và trả về JSON cho 3 cột
├── index.php             # Giao diện Dashboard tìm kiếm & đối chiếu kết quả
└── README.md             # Tài liệu hướng dẫn cài đặt và chạy ứng dụng