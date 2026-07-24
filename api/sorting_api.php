<?php

header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth_api.php';

if(
!isset($_POST['warna']) ||
!isset($_POST['berat'])
){

echo json_encode([
"success"=>false
]);

exit;

}

$warna=mysqli_real_escape_string(
$conn,
$_POST['warna']
);

$berat=floatval($_POST['berat']);

$query="INSERT INTO history_sorting
(
warna,
berat,
hasil
)

VALUES
(
'$warna',
'$berat',
'diterima'
)";

if(mysqli_query($conn,$query)){

echo json_encode([
"success"=>true
]);

}else{

echo json_encode([
"success"=>false
]);

}

?>