<?php

header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth_api.php';

$status=$_POST['status'];

$ip=$_POST['ip'];

$status=mysqli_real_escape_string(
$conn,
$status
);

$ip=mysqli_real_escape_string(
$conn,
$ip
);

$query="UPDATE status_iot

SET

status='$status',

ip_address='$ip',

last_update=NOW()

WHERE id=1";

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