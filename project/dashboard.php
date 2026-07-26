<?php
session_start();

// Cek login
if(!isset($_SESSION['login']) || $_SESSION['login'] !== true){
    header("Location: login.php");
    exit;
}

$activePage = 'dashboard';
require_once __DIR__ . '/../config/config.php';

// Ambil data user dari session
$fullname = $_SESSION['username'] ?? 'User';
$email = $_SESSION['email'] ?? '';

// Hitung total langsung dari sensor_logs
$stats_query = mysqli_query($conn, "
    SELECT
        COUNT(*)             AS total_sortir,
        SUM(warna = 'MERAH') AS total_merah,
        SUM(warna = 'HIJAU') AS total_hijau,
        SUM(berat)           AS total_berat
    FROM sensor_logs
");
$stats = mysqli_fetch_assoc($stats_query);

$total_merah  = (int)($stats['total_merah']  ?? 0);
$total_hijau  = (int)($stats['total_hijau']  ?? 0);
$total_sortir = (int)($stats['total_sortir'] ?? 0);
$total_berat  = round((float)($stats['total_berat'] ?? 0), 2);

// Ambil history dari sensor_logs (10 data terakhir)
$history_query = mysqli_query($conn, "SELECT id, warna, r, g, b, berat, created_at FROM sensor_logs ORDER BY created_at DESC LIMIT 10");
$history_data = [];
while($row = mysqli_fetch_assoc($history_query)){
    $history_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard IoT Coffee</title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --bg: #071207;
            --sidebar: #081808;
            --card: #0d1d0e;
            --primary: #75ff43;
            --text: #ffffff;
            --muted: #8fa08f;
            --border: rgba(255,255,255,0.05);
        }

        body {
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 260px;
            background: var(--sidebar);
            border-right: 1px solid var(--border);
            padding: 24px 16px;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 40px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border);
        }

        .logo-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(117,255,67,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 20px;
        }

        .logo-text h2 {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
        }

        .logo-text span {
            color: var(--muted);
            font-size: 12px;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            border-radius: 12px;
            color: var(--muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: 0.3s;
        }

        .menu a i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .menu a:hover {
            background: rgba(117,255,67,0.06);
            color: var(--text);
        }

        .menu a.active {
            background: rgba(117,255,67,0.12);
            color: var(--primary);
        }

        .menu a.active i {
            color: var(--primary);
        }

        .menu .logout {
            margin-top: 20px;
            border-top: 1px solid var(--border);
            padding-top: 20px;
            color: #ff6b6b;
        }

        .menu .logout:hover {
            background: rgba(255,68,68,0.08);
            color: #ff6b6b;
        }

        /* ===== MAIN CONTENT ===== */
        .main {
            flex: 1;
            min-width: 0;
            padding: 24px 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .topbar h1 {
            font-size: 28px;
            font-weight: 700;
        }

        .topbar p {
            color: var(--muted);
            margin-top: 4px;
            font-size: 14px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--card);
            padding: 8px 16px 8px 20px;
            border-radius: 30px;
            border: 1px solid var(--border);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(117,255,67,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 16px;
        }

        .user-name {
            font-size: 14px;
            font-weight: 500;
        }

        .user-email {
            font-size: 11px;
            color: var(--muted);
        }

        /* ===== ESP32 STATUS ===== */
        .esp-status {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .esp-status .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #75ff43;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.3; }
            100% { opacity: 1; }
        }

        .esp-status .status-dot.offline {
            background: #ff4d4d;
            animation: none;
        }

        .esp-status .status-text {
            font-size: 13px;
            color: var(--muted);
        }

        .esp-status .status-text strong {
            color: var(--text);
        }

        /* ===== CARDS SENSOR ===== */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px 22px;
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-2px);
            border-color: rgba(117,255,67,0.2);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .card-header span {
            font-size: 12px;
            font-weight: 500;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-header i {
            font-size: 20px;
            opacity: 0.5;
        }

        .card-value {
            font-size: 36px;
            font-weight: 700;
            line-height: 1;
        }

        .card-value .unit {
            font-size: 16px;
            font-weight: 400;
            color: var(--muted);
            margin-left: 4px;
        }

        .card-badge {
            display: inline-block;
            font-size: 11px;
            padding: 2px 12px;
            border-radius: 20px;
            margin-top: 8px;
            font-weight: 500;
        }

        .badge-green {
            background: rgba(117,255,67,0.15);
            color: var(--primary);
        }

        .badge-orange {
            background: rgba(255,165,0,0.15);
            color: orange;
        }

        .badge-red {
            background: rgba(255,68,68,0.15);
            color: #ff4d4d;
        }

        .badge-blue {
            background: rgba(0, 210, 255, 0.15);
            color: #00d2ff;
        }

        .card-red .card-value { color: #ff4d4d; }
        .card-hijau .card-value { color: var(--primary); }
        .card-blue .card-value { color: #00d2ff; }

        .card-red .card-header i { color: #ff4d4d; opacity: 0.7; }
        .card-hijau .card-header i { color: var(--primary); opacity: 0.7; }
        .card-blue .card-header i { color: #00d2ff; opacity: 0.7; }

        .badge-merah {
            background: rgba(255,77,77,0.15);
            color: #ff4d4d;
        }

        /* ===== HISTORY TABLE ===== */
        .history-section {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px 22px;
            margin-top: 0;
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .history-header h3 {
            font-size: 18px;
            font-weight: 600;
        }

        .history-header .badge-count {
            font-size: 12px;
            color: var(--muted);
            background: rgba(255,255,255,0.05);
            padding: 4px 14px;
            border-radius: 20px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        thead {
            background: rgba(255,255,255,0.03);
        }

        th {
            height: 46px;
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            letter-spacing: 0.5px;
            text-align: left;
            padding: 0 16px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        td {
            height: 52px;
            padding: 0 16px;
            border-top: 1px solid var(--border);
            font-size: 13px;
            color: #d7ddd7;
            white-space: nowrap;
        }

        tbody tr:hover {
            background: rgba(255,255,255,0.02);
        }

        .color-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .dot-merah { background: #ff4d4d; }
        .dot-hijau { background: var(--primary); }

        .text-merah { color: #ff4d4d; }
        .text-hijau { color: var(--primary); }

        .empty-state {
            text-align: center;
            color: var(--muted);
            padding: 30px 0;
        }

        .empty-state i {
            font-size: 40px;
            opacity: 0.3;
            margin-bottom: 10px;
            display: block;
        }

        /* ===== PAGINATION ===== */
        .pagination-wrap {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 18px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .pagination-info {
            font-size: 12px;
            color: var(--muted);
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pagination button {
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: rgba(255,255,255,0.04);
            color: var(--muted);
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: 0.2s;
        }

        .pagination button:hover:not(:disabled) {
            background: rgba(117,255,67,0.1);
            color: var(--primary);
            border-color: rgba(117,255,67,0.3);
        }

        .pagination button.active {
            background: rgba(117,255,67,0.15);
            color: var(--primary);
            border-color: rgba(117,255,67,0.4);
            font-weight: 600;
        }

        .pagination button:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .pagination .dots {
            color: var(--muted);
            font-size: 13px;
            padding: 0 4px;
            line-height: 34px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 16px;
            }

            .main {
                padding: 16px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .cards {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="dashboard">
    <!-- ===== SIDEBAR ===== -->
    <div class="sidebar">
        <div class="logo">
            <div class="logo-icon">
                <i class="fa-solid fa-mug-hot"></i>
            </div>
            <div class="logo-text">
                <h2>IoT Coffee</h2>
                <span>Control Center</span>
            </div>
        </div>

        <div class="menu">
            <a href="profile.php">
                <i class="fa-regular fa-user"></i> Profile
            </a>
            <a href="dashboard.php" class="active">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
            <a href="setting.php">
                <i class="fa-solid fa-gear"></i> Settings
            </a>
            <a href="logout.php" class="logout">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main">
        <!-- Topbar -->
        <div class="topbar">
            <div>
                <h1>Dashboard</h1>
                <p>Monitoring coffee bean sorting in real-time from ESP32.</p>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fa-regular fa-user"></i>
                </div>
                <div>
                    <div class="user-name"><?php echo htmlspecialchars($fullname); ?></div>
                    <div class="user-email"><?php echo htmlspecialchars($email); ?></div>
                </div>
            </div>
        </div>

        <!-- ESP32 Status -->
        <div class="esp-status">
            <div class="status-dot" id="statusDot"></div>
            <span class="status-text">
                <strong id="statusLabel">ESP32 Online</strong> 
                <span id="lastUpdate">-</span>
            </span>
            <span style="color: var(--muted); font-size: 12px; margin-left: auto;">
                <i class="fa-solid fa-wifi"></i> 
                <span id="wifiStatus">Connected</span>
            </span>
        </div>

        <!-- Sensor Cards -->
        <div class="cards">
            <div class="card card-red">
                <div class="card-header">
                    <span>🔴 Biji Merah</span>
                    <i class="fa-solid fa-circle" style="color:#ff4d4d;"></i>
                </div>
                <div class="card-value" id="totalMerah">
                    <?php echo number_format($total_merah); ?>
                    <span class="unit">biji</span>
                </div>
                <span class="card-badge badge-merah">Merah</span>
            </div>

            <div class="card card-hijau">
                <div class="card-header">
                    <span>🟢 Biji Hijau</span>
                    <i class="fa-solid fa-circle" style="color:#75ff43;"></i>
                </div>
                <div class="card-value" id="totalHijau">
                    <?php echo number_format($total_hijau); ?>
                    <span class="unit">biji</span>
                </div>
                <span class="card-badge badge-green">Hijau</span>
            </div>

            <div class="card card-blue">
                <div class="card-header">
                    <span>📦 Total Sortir</span>
                    <i class="fa-solid fa-boxes"></i>
                </div>
                <div class="card-value" id="totalSortir">
                    <?php echo number_format($total_sortir); ?>
                    <span class="unit">biji</span>
                </div>
                <span class="card-badge badge-blue">Total</span>
            </div>
        </div>

        <!-- History -->
        <div class="history-section">
            <div class="history-header">
                <h3><i class="fa-solid fa-clock-rotate-left"></i> Sorting History</h3>
                <span class="badge-count" id="historyBadge">
                    <i class="fa-regular fa-circle-dot"></i> Live from ESP32
                </span>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Warna</th>
                            <th>R</th>
                            <th>G</th>
                            <th>B</th>
                            <th>Berat</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="historyTable">
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                    Memuat data...
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrap">
                <div class="pagination-info" id="paginationInfo">-</div>
                <div class="pagination" id="paginationControls"></div>
            </div>
        </div>
    </div>
</div>

<!-- ===== JAVASCRIPT ===== -->
<script>
    const LIMIT = 10;
    let currentPage = 1;
    let totalPages  = 1;
    let isOnPage1   = true;

    // ===== FETCH SENSOR STATS =====
    function fetchSensorData() {
        fetch('../api/get_sensor_data.php')
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('totalMerah').innerHTML  = data.total_merah  + ' <span class="unit">biji</span>';
                    document.getElementById('totalHijau').innerHTML  = data.total_hijau  + ' <span class="unit">biji</span>';
                    document.getElementById('totalSortir').innerHTML = data.total_sortir + ' <span class="unit">biji</span>';
                    document.getElementById('lastUpdate').textContent = 'Last update: ' + data.last_update;
                    document.getElementById('statusDot').className   = 'status-dot';
                    document.getElementById('statusLabel').textContent = 'ESP32 Online';
                }
            })
            .catch(() => {
                document.getElementById('statusDot').className    = 'status-dot offline';
                document.getElementById('statusLabel').textContent = 'ESP32 Offline';
            });
    }

    // ===== FETCH HISTORY =====
    function fetchHistory(page, silent = false) {
        if (!silent) {
            document.getElementById('historyTable').innerHTML = `
                <tr><td colspan="7">
                    <div class="empty-state">
                        <i class="fa-solid fa-spinner fa-spin"></i> Memuat data...
                    </div>
                </td></tr>`;
        }

        fetch(`../api/get_history.php?page=${page}&limit=${LIMIT}`)
            .then(r => r.json())
            .then(data => {
                if (data.status !== 'success') return;

                const p     = data.pagination;
                currentPage = p.page;
                totalPages  = p.total_pages;
                isOnPage1   = currentPage === 1;

                const tbody = document.getElementById('historyTable');
                const start = (p.page - 1) * p.limit;

                if (data.data.length === 0) {
                    tbody.innerHTML = `
                        <tr><td colspan="7">
                            <div class="empty-state">
                                <i class="fa-regular fa-clock"></i>
                                Belum ada data dari ESP32
                            </div>
                        </td></tr>`;
                } else {
                    tbody.innerHTML = data.data.map((row, i) => {
                        const isMerah      = row.warna.toUpperCase() === 'MERAH';
                        const colorClass   = isMerah ? 'dot-merah'  : 'dot-hijau';
                        const textClass    = isMerah ? 'text-merah' : 'text-hijau';
                        const warnaDisplay = isMerah ? '🔴 Merah'   : '🟢 Hijau';
                        return `
                            <tr>
                                <td>${start + i + 1}</td>
                                <td>
                                    <span class="color-dot ${colorClass}"></span>
                                    <span class="${textClass}">${warnaDisplay}</span>
                                </td>
                                <td>${row.r}</td>
                                <td>${row.g}</td>
                                <td>${row.b}</td>
                                <td>${Number(row.berat).toFixed(2)} g</td>
                                <td>${row.created_at}</td>
                            </tr>`;
                    }).join('');
                }

                // Update info
                const from = p.total_data === 0 ? 0 : start + 1;
                const to   = Math.min(start + p.limit, p.total_data);
                document.getElementById('paginationInfo').textContent =
                    `Menampilkan ${from}–${to} dari ${p.total_data} data`;

                document.getElementById('historyBadge').innerHTML =
                    `<i class="fa-regular fa-circle-dot"></i> ${p.total_data} data total`;

                renderPagination(p.page, p.total_pages);
            })
            .catch(() => {
                document.getElementById('historyTable').innerHTML = `
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Gagal memuat data
                        </div>
                    </td></tr>`;
            });
    }

    // ===== RENDER PAGINATION BUTTONS =====
    function renderPagination(page, total) {
        const wrap = document.getElementById('paginationControls');
        if (total <= 1) { wrap.innerHTML = ''; return; }

        let html = '';

        // Prev
        html += `<button onclick="goPage(${page - 1})" ${page === 1 ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-left"></i>
                 </button>`;

        // Page numbers dengan dots
        const pages = pageRange(page, total);
        pages.forEach(p => {
            if (p === '...') {
                html += `<span class="dots">…</span>`;
            } else {
                html += `<button onclick="goPage(${p})" class="${p === page ? 'active' : ''}">${p}</button>`;
            }
        });

        // Next
        html += `<button onclick="goPage(${page + 1})" ${page === total ? 'disabled' : ''}>
                    <i class="fa-solid fa-chevron-right"></i>
                 </button>`;

        wrap.innerHTML = html;
    }

    // Buat array nomor halaman dengan dots (misal: 1 2 3 ... 8 9 10)
    function pageRange(current, total) {
        if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);

        const pages = [];
        if (current <= 4) {
            for (let i = 1; i <= 5; i++) pages.push(i);
            pages.push('...');
            pages.push(total);
        } else if (current >= total - 3) {
            pages.push(1);
            pages.push('...');
            for (let i = total - 4; i <= total; i++) pages.push(i);
        } else {
            pages.push(1);
            pages.push('...');
            for (let i = current - 1; i <= current + 1; i++) pages.push(i);
            pages.push('...');
            pages.push(total);
        }
        return pages;
    }

    function goPage(page) {
        if (page < 1 || page > totalPages) return;
        fetchHistory(page);
    }

    // ===== AUTO REFRESH =====
    // Stats refresh tiap 5 detik
    setInterval(fetchSensorData, 5000);

    // History: jika user di halaman 1, refresh otomatis tiap 10 detik (silent)
    setInterval(() => {
        if (isOnPage1) fetchHistory(1, true);
    }, 10000);

    // Initial load
    fetchSensorData();
    fetchHistory(1);
</script>

</body>
</html>