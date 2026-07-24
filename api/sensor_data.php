<?php
// CORS untuk ESP32
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';

// Cek method
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    // Ambil data dari ESP32
    $warna = $_POST['warna'] ?? '';
    $r = $_POST['r'] ?? 0;
    $g = $_POST['g'] ?? 0;
    $b = $_POST['b'] ?? 0;
    $total_merah = $_POST['total_merah'] ?? 0;
    $total_hijau = $_POST['total_hijau'] ?? 0;
    $total_sortir = $_POST['total_sortir'] ?? 0;
    $berat = $_POST['berat'] ?? 0;
    
    // Validasi
    if(empty($warna)){
        echo json_encode([
            'status' => 'error',
            'message' => 'Data warna tidak lengkap'
        ]);
        exit;
    }
    
    // Escape string
    $warna = mysqli_real_escape_string($conn, $warna);
    $r = intval($r);
    $g = intval($g);
    $b = intval($b);
    $total_merah = intval($total_merah);
    $total_hijau = intval($total_hijau);
    $total_sortir = intval($total_sortir);
    $berat = floatval($berat);
    
    // 1. Simpan ke sensor_logs (history)
    $query1 = "INSERT INTO sensor_logs (warna, r, g, b, total_merah, total_hijau, total_sortir, berat, created_at) 
               VALUES ('$warna', $r, $g, $b, $total_merah, $total_hijau, $total_sortir, $berat, NOW())";
    
    // 2. Update sensor_data (real-time)
    $query2 = "UPDATE sensor_data SET 
               total_merah = $total_merah, 
               total_hijau = $total_hijau, 
               total_sortir = $total_sortir, 
               berat = $berat,
               updated_at = NOW() 
               WHERE id = 1";
    
    // Eksekusi query
    $result1 = mysqli_query($conn, $query1);
    $result2 = mysqli_query($conn, $query2);
    
    if($result1 && $result2){
        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan',
            'data' => [
                'warna' => $warna,
                'r' => $r,
                'g' => $g,
                'b' => $b,
                'total_merah' => $total_merah,
                'total_hijau' => $total_hijau,
                'total_sortir' => $total_sortir,
                'berat' => $berat
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan data: ' . mysqli_error($conn)
        ]);
    }
    
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan'
    ]);
}
?>