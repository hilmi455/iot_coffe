<?php

header("Content-Type: application/json");

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth_api.php';

$query="UPDATE status_iot

SET

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