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

// Ambil data real-time dari sensor_data
$sensor_query = mysqli_query($conn, "SELECT * FROM sensor_data WHERE id = 1");
$sensor_data = mysqli_fetch_assoc($sensor_query);

// Jika belum ada data, set default
if(!$sensor_data){
    $sensor_data = [
        'total_merah' => 0,
        'total_hijau' => 0,
        'total_sortir' => 0,
        'berat' => 0,
        'updated_at' => date('Y-m-d H:i:s')
    ];
}

// Ambil history dari sensor_logs (10 data terakhir)
$history_query = mysqli_query($conn, "SELECT * FROM sensor_logs ORDER BY created_at DESC LIMIT 10");
$history_data = [];
while($row = mysqli_fetch_assoc($history_query)){
    $history_data[] = $row;
}

// Hitung total keseluruhan
$total_merah = $sensor_data['total_merah'] ?? 0;
$total_hijau = $sensor_data['total_hijau'] ?? 0;
$total_sortir = $sensor_data['total_sortir'] ?? 0;
$total_berat = $sensor_data['berat'] ?? 0;
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
        .card-orange .card-value { color: orange; }
        .card-green .card-value { color: var(--primary); }
        .card-blue .card-value { color: #00d2ff; }

        .card-red .card-header i { color: #ff4d4d; opacity: 0.7; }
        .card-orange .card-header i { color: orange; opacity: 0.7; }
        .card-green .card-header i { color: var(--primary); opacity: 0.7; }
        .card-blue .card-header i { color: #00d2ff; opacity: 0.7; }

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

        .dot-orange { background: orange; }
        .dot-green { background: var(--primary); }

        .text-orange { color: orange; }
        .text-green { color: var(--primary); }

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
            <div class="card card-orange">
                <div class="card-header">
                    <span>🟠 Orange Beans</span>
                    <i class="fa-solid fa-circle" style="color:orange;"></i>
                </div>
                <div class="card-value" id="totalMerah">
                    <?php echo number_format($total_merah); ?>
                    <span class="unit">biji</span>
                </div>
                <span class="card-badge badge-orange">Grade A</span>
            </div>

            <div class="card card-green">
                <div class="card-header">
                    <span>🟢 Green Beans</span>
                    <i class="fa-solid fa-circle" style="color:#75ff43;"></i>
                </div>
                <div class="card-value" id="totalHijau">
                    <?php echo number_format($total_hijau); ?>
                    <span class="unit">biji</span>
                </div>
                <span class="card-badge badge-green">Reject</span>
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
                <span class="badge-count">
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
                            <th>Total Oranye</th>
                            <th>Total Hijau</th>
                            <th>Berat</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="historyTable">
                        <?php if(empty($history_data)): ?>
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i class="fa-regular fa-clock"></i>
                                        Belum ada data dari ESP32
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach($history_data as $row): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <span class="color-dot <?php echo $row['warna'] == 'oranye' ? 'dot-orange' : 'dot-green'; ?>"></span>
                                    <span class="<?php echo $row['warna'] == 'oranye' ? 'text-orange' : 'text-green'; ?>">
                                        <?php echo $row['warna'] == 'oranye' ? '🟠 Oranye' : '🟢 Hijau'; ?>
                                    </span>
                                </td>
                                <td><?php echo $row['r']; ?></td>
                                <td><?php echo $row['g']; ?></td>
                                <td><?php echo $row['b']; ?></td>
                                <td><?php echo number_format($row['total_merah']); ?></td>
                                <td><?php echo number_format($row['total_hijau']); ?></td>
                                <td><?php echo number_format($row['berat'], 2); ?> kg</td>
                                <td><?php echo date('H:i:s', strtotime($row['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===== JAVASCRIPT ===== -->
<script>
    // Auto refresh data setiap 3 detik
    function fetchSensorData() {
        fetch('api/get_sensor_data.php')
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success'){
                    // Update cards
                    document.getElementById('totalMerah').innerHTML = data.total_merah + ' <span class="unit">biji</span>';
                    document.getElementById('totalHijau').innerHTML = data.total_hijau + ' <span class="unit">biji</span>';
                    document.getElementById('totalSortir').innerHTML = data.total_sortir + ' <span class="unit">biji</span>';
                    
                    // Update status
                    document.getElementById('lastUpdate').textContent = 'Last update: ' + data.last_update;
                    document.getElementById('statusDot').className = 'status-dot';
                    document.getElementById('statusLabel').textContent = 'ESP32 Online';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('statusDot').className = 'status-dot offline';
                document.getElementById('statusLabel').textContent = 'ESP32 Offline';
            });
    }

    // Refresh history table
    function refreshHistory() {
        fetch('api/get_history.php')
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success'){
                    const tbody = document.getElementById('historyTable');
                    
                    if(data.data.length === 0){
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i class="fa-regular fa-clock"></i>
                                        Belum ada data dari ESP32
                                    </div>
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    tbody.innerHTML = '';
                    data.data.forEach((row, index) => {
                        const colorClass = row.warna === 'oranye' ? 'dot-orange' : 'dot-green';
                        const textClass = row.warna === 'oranye' ? 'text-orange' : 'text-green';
                        const warnaDisplay = row.warna === 'oranye' ? '🟠 Oranye' : '🟢 Hijau';
                        
                        tbody.innerHTML += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>
                                    <span class="color-dot ${colorClass}"></span>
                                    <span class="${textClass}">${warnaDisplay}</span>
                                </td>
                                <td>${row.r}</td>
                                <td>${row.g}</td>
                                <td>${row.b}</td>
                                <td>${Number(row.total_merah).toLocaleString()}</td>
                                <td>${Number(row.total_hijau).toLocaleString()}</td>
                                <td>${Number(row.berat).toFixed(2)} kg</td>
                                <td>${row.created_at}</td>
                            </tr>
                        `;
                    });
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Initial fetch
    fetchSensorData();
    refreshHistory();

    // Auto refresh every 5 seconds
    setInterval(fetchSensorData, 5000);
    setInterval(refreshHistory, 10000);
</script>

</body>
</html>