<?php

header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth_api.php';

/*
POST
=========================
berat
red
green
blue
warna
*/

if(
    !isset($_POST['berat']) ||
    !isset($_POST['red']) ||
    !isset($_POST['green']) ||
    !isset($_POST['blue']) ||
    !isset($_POST['warna'])
){

    echo json_encode([
        "success"=>false,
        "message"=>"Parameter tidak lengkap"
    ]);

    exit;

}

$berat=floatval($_POST['berat']);

$red=intval($_POST['red']);

$green=intval($_POST['green']);

$blue=intval($_POST['blue']);

$warna=mysqli_real_escape_string(
$conn,
$_POST['warna']
);

$query="INSERT INTO sensor_data
(
berat,
nilai_red,
nilai_green,
nilai_blue,
warna
)

VALUES
(
'$berat',
'$red',
'$green',
'$blue',
'$warna'
)";

if(mysqli_query($conn,$query)){

    echo json_encode([
        "success"=>true,
        "message"=>"Sensor berhasil disimpan"
    ]);

}else{

    echo json_encode([
        "success"=>false,
        "message"=>mysqli_error($conn)
    ]);

}

?>