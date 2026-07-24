<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';

$query = mysqli_query($conn, "SELECT * FROM sensor_logs ORDER BY created_at DESC LIMIT 20");
$data = [];

while($row = mysqli_fetch_assoc($query)){
    $data[] = [
        'warna' => $row['warna'],
        'r' => (int)$row['r'],
        'g' => (int)$row['g'],
        'b' => (int)$row['b'],
        'total_merah' => (int)$row['total_merah'],
        'total_hijau' => (int)$row['total_hijau'],
        'total_sortir' => (int)$row['total_sortir'],
        'berat' => (float)$row['berat'],
        'created_at' => date('H:i:s', strtotime($row['created_at']))
    ];
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);
?>