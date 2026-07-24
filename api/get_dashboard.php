<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';

echo "STEP 1<br>";

$querySensor = mysqli_query($conn,"
SELECT *
FROM sensor_data
ORDER BY id DESC
LIMIT 1
");

if(!$querySensor){

    die(mysqli_error($conn));

}

echo "STEP 2<br>";

$sensor = mysqli_fetch_assoc($querySensor);

echo "<pre>";
print_r($sensor);
echo "</pre>";