<?php
/**
 * ============================================
 * BeanShort IoT Coffee
 * Database Connection
 * ============================================
 */

date_default_timezone_set('Asia/Jakarta');

$DB_HOST = "localhost";
$DB_NAME = "iot_coffee";
$DB_USER = "root";          // Ganti sesuai hosting
$DB_PASS = "";              // Ganti sesuai hosting

$conn = mysqli_connect(
    $DB_HOST,
    $DB_USER,
    $DB_PASS,
    $DB_NAME
);

if (!$conn) {

    http_response_code(500);

    die(json_encode([
        "success" => false,
        "message" => "Koneksi database gagal."
    ]));

}

mysqli_set_charset($conn,"utf8");