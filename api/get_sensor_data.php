<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';

// Hitung total langsung dari sensor_logs
$stats_query = mysqli_query($conn, "
    SELECT 
        COUNT(*)                          AS total_sortir,
        SUM(warna = 'MERAH')              AS total_merah,
        SUM(warna = 'HIJAU')              AS total_hijau,
        SUM(berat)                        AS total_berat,
        MAX(created_at)                   AS last_update,
        (SELECT warna FROM sensor_logs ORDER BY created_at DESC LIMIT 1) AS warna,
        (SELECT r     FROM sensor_logs ORDER BY created_at DESC LIMIT 1) AS nilai_red,
        (SELECT g     FROM sensor_logs ORDER BY created_at DESC LIMIT 1) AS nilai_green,
        (SELECT b     FROM sensor_logs ORDER BY created_at DESC LIMIT 1) AS nilai_blue
    FROM sensor_logs
");

$data = mysqli_fetch_assoc($stats_query);

if ($data && $data['total_sortir'] > 0) {
    echo json_encode([
        'status'       => 'success',
        'warna'        => $data['warna'] ?? '-',
        'nilai_red'    => (int)($data['nilai_red'] ?? 0),
        'nilai_green'  => (int)($data['nilai_green'] ?? 0),
        'nilai_blue'   => (int)($data['nilai_blue'] ?? 0),
        'total_merah'  => (int)$data['total_merah'],
        'total_hijau'  => (int)$data['total_hijau'],
        'total_sortir' => (int)$data['total_sortir'],
        'total_berat'  => round((float)$data['total_berat'], 2),
        'last_update'  => $data['last_update'] ? date('H:i:s', strtotime($data['last_update'])) : '-'
    ]);
} else {
    echo json_encode([
        'status'       => 'success',
        'warna'        => '-',
        'nilai_red'    => 0,
        'nilai_green'  => 0,
        'nilai_blue'   => 0,
        'total_merah'  => 0,
        'total_hijau'  => 0,
        'total_sortir' => 0,
        'total_berat'  => 0,
        'last_update'  => 'Belum ada data'
    ]);
}
?>
