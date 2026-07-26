<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';

$page  = max(1, (int)($_GET['page']  ?? 1));
$limit = max(1, min(100, (int)($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

// Hitung total data
$count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM sensor_logs");
$count_row    = mysqli_fetch_assoc($count_result);
$total_data   = (int)$count_row['total'];
$total_pages  = (int)ceil($total_data / $limit);

// Ambil data sesuai halaman
$query = mysqli_query($conn, "
    SELECT id, warna, r, g, b, berat, created_at
    FROM sensor_logs
    ORDER BY created_at DESC
    LIMIT $limit OFFSET $offset
");

$data = [];
while ($row = mysqli_fetch_assoc($query)) {
    $data[] = [
        'id'         => (int)$row['id'],
        'warna'      => $row['warna'],
        'r'          => (int)$row['r'],
        'g'          => (int)$row['g'],
        'b'          => (int)$row['b'],
        'berat'      => (float)$row['berat'],
        'created_at' => date('H:i:s d/m/Y', strtotime($row['created_at']))
    ];
}

echo json_encode([
    'status'      => 'success',
    'data'        => $data,
    'pagination'  => [
        'page'        => $page,
        'limit'       => $limit,
        'total_data'  => $total_data,
        'total_pages' => $total_pages
    ]
]);
?>
