<?php

/**
 * ============================================
 * API AUTH
 * ============================================
 */

header("Content-Type: application/json");

include_once "token.php";

if(!isset($_POST['key'])){

    http_response_code(401);

    echo json_encode([
        "success"=>false,
        "message"=>"API KEY tidak ditemukan"
    ]);

    exit;

}

if($_POST['key'] != API_KEY){

    http_response_code(401);

    echo json_encode([
        "success"=>false,
        "message"=>"API KEY salah"
    ]);

    exit;

}