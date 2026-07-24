<?php

header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';

$query=mysqli_query($conn,"
SELECT *
FROM status_iot
WHERE id=1
");

echo json_encode(

mysqli_fetch_assoc($query),

JSON_PRETTY_PRINT

);

?>