<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';

if($conn){

    echo "Database Connected";

}else{

    echo "Database Failed";

}