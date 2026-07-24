<?php

header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';

$response=[];

/* IoT */

$status=mysqli_query($conn,"
SELECT status,last_update
FROM status_iot
LIMIT 1
");

$response['iot']=mysqli_fetch_assoc($status);

/* Sensor terakhir */

$sensor=mysqli_query($conn,"
SELECT warna,berat,timestamp
FROM sensor_data
ORDER BY id DESC
LIMIT 1
");

$response['sensor']=mysqli_fetch_assoc($sensor);

echo json_encode(

$response,

JSON_PRETTY_PRINT

);