<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $warna = $_POST['warna'] ?? '';
    $r     = $_POST['r']     ?? 0;
    $g     = $_POST['g']     ?? 0;
    $b     = $_POST['b']     ?? 0;
    $berat = $_POST['berat'] ?? 0;

    if (empty($warna)) {
        echo json_encode(['status' => 'error', 'message' => 'Data warna tidak lengkap']);
        exit;
    }

    $warna = mysqli_real_escape_string($conn, $warna);
    $r     = intval($r);
    $g     = intval($g);
    $b     = intval($b);
    $berat = floatval($berat);

    // INSERT ke sensor_logs (history per biji)
    $query = "INSERT INTO sensor_logs (warna, r, g, b, berat, created_at)
              VALUES ('$warna', $r, $g, $b, $berat, NOW())";

    $result = mysqli_query($conn, $query);

    if ($result) {
        echo json_encode([
            'status'  => 'success',
            'message' => 'Data berhasil disimpan',
            'data'    => [
                'warna' => $warna,
                'r'     => $r,
                'g'     => $g,
                'b'     => $b,
                'berat' => $berat
            ]
        ]);
    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal menyimpan: ' . mysqli_error($conn)
        ]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
}
?>
