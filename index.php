<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chủ đề 10: Demo Hybrid Search (MySQL FULLTEXT + AI Embeddings)</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">

    <!-- Header -->
    <header class="bg-indigo-700 text-white py-6 shadow-md">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center">
            <div>
                <span class="bg-indigo-900 text-xs font-semibold px-2.5 py-1 rounded uppercase tracking-wider">Tuần 9 - Chủ đề 10</span>
                <h1 class="text-2xl font-bold mt-1">Hệ thống Tìm kiếm Thông minh: Hybrid Search</h1>
                <p class="text-indigo-200 text-sm">Kết hợp MySQL FULLTEXT SEARCH (Từ khóa) & Gemini Embeddings (Ngữ nghĩa AI)</p>
            </div>
            <a href="seed.php" class="mt-4 md:mt-0 bg-white text-indigo-700 hover:bg-indigo-50 px-4 py-2 rounded-lg text-sm font-semibold shadow">
                ↻ Nạp lại dữ liệu mẫu (Seed DB)
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 py-6">
        
        <!-- Khung tìm kiếm & Điều chỉnh Trọng số Alpha -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6">
            <form id="searchForm" class="space-y-4">
                <div class="flex flex-col md:flex-row gap-3">
                    <input type="text" id="searchInput" 
                           placeholder="Nhập từ khóa, mã SKU hoặc mô tả nhu cầu của bạn..." 
                           class="flex-1 px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-base"
                           required>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-lg transition shadow">
                        Tìm kiếm & Phân tích
                    </button>
                </div>

                <!-- Thanh trượt điều chỉnh trọng số Alpha -->
                <div class="flex flex-col md:flex-row items-center justify-between bg-slate-50 p-3 rounded-lg border border-slate-200 text-sm">
                    <div class="flex items-center gap-3 w-full md:w-2/3">
                        <span class="font-medium text-slate-700 whitespace-nowrap">Trọng số kết hợp (α):</span>
                        <input type="range" id="alphaSlider" min="0" max="1" step="0.1" value="0.4" class="w-full accent-indigo-600">
                        <span id="alphaDisplay" class="font-bold text-indigo-700 whitespace-nowrap">40% Fulltext / 60% AI</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-2 md:mt-0">
                        Công thức: <code class="bg-slate-200 px-1.5 py-0.5 rounded text-slate-800">Hybrid = α * FT_Score + (1 - α) * Cosine_Score</code>
                    </div>
                </div>
            </form>

            <!-- Gợi ý Kịch bản Demo nhanh cho Giảng viên -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap gap-2 items-center text-xs">
                <span class="font-semibold text-slate-500">Kịch bản Demo nhanh:</span>
                <button type="button" onclick="quickTest('ngồi làm việc lâu bị đau mỏi lưng và cổ tay')" class="bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 px-3 py-1.5 rounded-full transition">
                    1. Tìm theo ngữ nghĩa ("ngồi làm việc lâu bị đau mỏi lưng và cổ tay")
                </button>
                <button type="button" onclick="quickTest('LAP-ASUS-M1405YA RTX 4080')" class="bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 px-3 py-1.5 rounded-full transition">
                    2. Tìm theo mã SKU & thông số ("LAP-ASUS-M1405YA RTX 4080")
                </button>
                <button type="button" onclick="quickTest('thiết bị giúp tập trung ở nơi đông người ồn ào')" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-full transition">
                    3. Tìm theo vấn đề ("thiết bị giúp tập trung ở nơi đông người ồn ào")
                </button>
            </div>
        </div>

        <!-- Thanh trạng thái -->
        <div id="statusBar" class="hidden mb-6 p-3 rounded-lg bg-slate-800 text-white text-sm flex justify-between items-center">
            <span id="statusText">Đang xử lý...</span>
            <span id="timeText" class="text-emerald-400 font-mono"></span>
        </div>

        <!-- Bảng so sánh 3 cột -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- CỘT 1: MySQL FULLTEXT -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="bg-slate-100 p-4 border-b border-slate-200">
                    <h2 class="font-bold text-slate-800 flex items-center justify-between">
                        <span>1. MySQL FULLTEXT</span>
                        <span class="text-xs bg-slate-200 text-slate-700 px-2 py-0.5 rounded">Lexical Only</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">So khớp từ khóa chính xác bằng chỉ mục Inverted Index.</p>
                </div>
                <div id="colFulltext" class="p-4 space-y-3 flex-1">
                    <p class="text-sm text-slate-400 italic text-center py-8">Chưa có dữ liệu tìm kiếm</p>
                </div>
            </div>

            <!-- CỘT 2: AI SEMANTIC SEARCH -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="bg-purple-50 p-4 border-b border-purple-200">
                    <h2 class="font-bold text-purple-900 flex items-center justify-between">
                        <span>2. AI Semantic Search</span>
                        <span class="text-xs bg-purple-200 text-purple-800 px-2 py-0.5 rounded">Vector Cosine</span>
                    </h2>
                    <p class="text-xs text-purple-600 mt-1">Hiểu ý định & ngữ cảnh qua Vector 768 chiều.</p>
                </div>
                <div id="colSemantic" class="p-4 space-y-3 flex-1">
                    <p class="text-sm text-slate-400 italic text-center py-8">Chưa có dữ liệu tìm kiếm</p>
                </div>
            </div>

            <!-- CỘT 3: HYBRID SEARCH -->
            <div class="bg-white rounded-xl shadow-md border-2 border-indigo-500 overflow-hidden flex flex-col">
                <div class="bg-indigo-600 text-white p-4">
                    <h2 class="font-bold flex items-center justify-between">
                        <span>3. HYBRID SEARCH (Tối ưu)</span>
                        <span class="text-xs bg-indigo-900 text-indigo-100 px-2 py-0.5 rounded">Fulltext + AI</span>
                    </h2>
                    <p class="text-xs text-indigo-200 mt-1">Kết hợp chuẩn hóa điểm số từ cả 2 phương pháp.</p>
                </div>
                <div id="colHybrid" class="p-4 space-y-3 flex-1 bg-indigo-50/20">
                    <p class="text-sm text-slate-400 italic text-center py-8">Chưa có dữ liệu tìm kiếm</p>
                </div>
            </div>

        </div>
    </main>

    <script>
        const slider = document.getElementById('alphaSlider');
        const display = document.getElementById('alphaDisplay');

        slider.addEventListener('input', () => {
            const a = Math.round(slider.value * 100);
            const b = 100 - a;
            display.textContent = `${a}% Fulltext / ${b}% AI`;
        });

        function quickTest(queryText) {
            document.getElementById('searchInput').value = queryText;
            document.getElementById('searchForm').dispatchEvent(new Event('submit'));
        }

        function renderCard(item, mode, rank) {
            let badgeHtml = '';
            if (mode === 'ft') {
                badgeHtml = `<span class="bg-slate-200 text-slate-800 text-xs font-mono px-2 py-0.5 rounded">FT Norm: ${item.ft_score} (Raw: ${item.raw_ft_score})</span>`;
            } else if (mode === 'sem') {
                badgeHtml = `<span class="bg-purple-100 text-purple-800 text-xs font-mono px-2 py-0.5 rounded">Cosine: ${item.semantic_score}</span>`;
            } else {
                badgeHtml = `
                    <div class="flex flex-wrap gap-1 mt-2">
                        <span class="bg-indigo-600 text-white text-xs font-mono px-2 py-0.5 rounded font-bold">Hybrid: ${item.hybrid_score}</span>
                        <span class="bg-slate-200 text-slate-700 text-xs font-mono px-1.5 py-0.5 rounded">FT: ${item.ft_score}</span>
                        <span class="bg-purple-100 text-purple-800 text-xs font-mono px-1.5 py-0.5 rounded">AI: ${item.semantic_score}</span>
                    </div>
                `;
            }

            return `
                <div class="p-3.5 rounded-lg border border-slate-200 bg-white hover:shadow-md transition">
                    <div class="flex justify-between items-start gap-2">
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">#${rank} - ${item.sku}</span>
                        <span class="text-xs font-semibold text-rose-600">${item.price}</span>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm mt-1.5">${item.name}</h3>
                    <p class="text-xs text-slate-600 mt-1 line-clamp-3">${item.description}</p>
                    <div class="mt-2 pt-2 border-t border-slate-100">
                        ${badgeHtml}
                    </div>
                </div>
            `;
        }

        document.getElementById('searchForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const q = document.getElementById('searchInput').value.trim();
            const alpha = slider.value;
            if (!q) return;

            const statusBar = document.getElementById('statusBar');
            const statusText = document.getElementById('statusText');
            const timeText = document.getElementById('timeText');

            statusBar.classList.remove('hidden');
            statusText.textContent = `Đang truy vấn MySQL FULLTEXT và tính toán Vector AI cho: "${q}"...`;
            timeText.textContent = '';

            try {
                const res = await fetch(`api_search.php?q=${encodeURIComponent(q)}&alpha=${alpha}`);
                const data = await res.json();

                if (data.status !== 'success') {
                    alert(data.message || 'Có lỗi xảy ra');
                    return;
                }

                statusText.textContent = data.ai_connected 
                    ? `Hoàn tất! Đã kết hợp MySQL FULLTEXT và Gemini Vector Embedding.` 
                    : `Cảnh báo: Chưa cấu hình GEMINI_API_KEY hoặc chưa chạy seed.php (Điểm AI = 0).`;
                timeText.textContent = `Thời gian phản hồi: ${data.execution_time_ms} ms`;

                // Render cột 1
                const colFt = document.getElementById('colFulltext');
                colFt.innerHTML = data.fulltext_results.length 
                    ? data.fulltext_results.map((item, idx) => renderCard(item, 'ft', idx + 1)).join('')
                    : `<div class="p-6 text-center text-rose-600 bg-rose-50 rounded-lg text-xs font-medium">Không tìm thấy sản phẩm nào khớp từ khóa! (0 kết quả)</div>`;

                // Render cột 2
                const colSem = document.getElementById('colSemantic');
                colSem.innerHTML = data.semantic_results.length 
                    ? data.semantic_results.map((item, idx) => renderCard(item, 'sem', idx + 1)).join('')
                    : `<div class="p-6 text-center text-slate-500 bg-slate-50 rounded-lg text-xs">Không có kết quả ngữ nghĩa phù hợp.</div>`;

                // Render cột 3
                const colHyb = document.getElementById('colHybrid');
                colHyb.innerHTML = data.hybrid_results.length 
                    ? data.hybrid_results.map((item, idx) => renderCard(item, 'hyb', idx + 1)).join('')
                    : `<div class="p-6 text-center text-slate-500 bg-slate-50 rounded-lg text-xs">Không có kết quả.</div>`;

            } catch (err) {
                statusText.textContent = 'Lỗi kết nối Server Backend.';
            }
        });
    </script>
</body>
</html>