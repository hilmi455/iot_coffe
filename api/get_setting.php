<?php

header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';

$query=mysqli_query($conn,"
SELECT *
FROM konfigurasi_iot
LIMIT 1
");

echo json_encode(

mysqli_fetch_assoc($query),

JSON_PRETTY_PRINT

);

?>