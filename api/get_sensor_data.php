<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';

$query = mysqli_query($conn, "SELECT * FROM sensor_data WHERE id = 1");
$data = mysqli_fetch_assoc($query);

if($data){
    echo json_encode([
        'status' => 'success',
        'total_merah' => (int)$data['total_merah'],
        'total_hijau' => (int)$data['total_hijau'],
        'total_sortir' => (int)$data['total_sortir'],
        'berat' => (float)$data['berat'],
        'last_update' => date('H:i:s', strtotime($data['updated_at']))
    ]);
} else {
    echo json_encode([
        'status' => 'success',
        'total_merah' => 0,
        'total_hijau' => 0,
        'total_sortir' => 0,
        'berat' => 0,
        'last_update' => 'Belum ada data'
    ]);
}
?>