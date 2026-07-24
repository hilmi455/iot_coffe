<?php

header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';

$servo_merah=intval($_POST['servo_merah']);
$servo_orange=intval($_POST['servo_orange']);
$servo_hijau=intval($_POST['servo_hijau']);
$delay_servo=intval($_POST['delay_servo']);

$query="UPDATE konfigurasi_iot

SET

servo_merah='$servo_merah',

servo_orange='$servo_orange',

servo_hijau='$servo_hijau',

delay_servo='$delay_servo'

WHERE id=1";

if(mysqli_query($conn,$query)){

    echo json_encode([
        "success"=>true,
        "message"=>"Konfigurasi berhasil disimpan"
    ]);

}else{

    echo json_encode([
        "success"=>false,
        "message"=>mysqli_error($conn)
    ]);

}