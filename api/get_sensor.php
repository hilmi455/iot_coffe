<?php

header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';

$query=mysqli_query($conn,"
SELECT *
FROM sensor_data
ORDER BY id DESC
LIMIT 1
");

if(mysqli_num_rows($query)>0){

    echo json_encode(

        mysqli_fetch_assoc($query),

        JSON_PRETTY_PRINT

    );

}else{

    echo json_encode([

        "success"=>false,

        "message"=>"Belum ada data"

    ]);

}

?>