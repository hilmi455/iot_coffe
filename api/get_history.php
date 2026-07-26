<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';

$query = mysqli_query($conn, "SELECT id, warna, r, g, b, berat, created_at FROM sensor_logs ORDER BY created_at DESC LIMIT 20");
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
    'status' => 'success',
    'data'   => $data
]);
?>
