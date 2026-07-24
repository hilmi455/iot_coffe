<?php

header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';

$aktivitas=trim($_POST['aktivitas']);

if($aktivitas==""){

    echo json_encode([
        "success"=>false
    ]);

    exit;

}

$aktivitas=mysqli_real_escape_string(
$conn,
$aktivitas
);

$query="INSERT INTO activity_log

(

aktivitas

)

VALUES

(

'$aktivitas'

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