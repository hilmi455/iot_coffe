<?php

header("Content-Type: application/json");
session_start();

require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

    echo json_encode([
        "success" => false,
        "message" => "Method tidak diizinkan"
    ]);

    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username == '' || $password == '') {

    echo json_encode([
        "success" => false,
        "message" => "Username dan password wajib diisi"
    ]);

    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id,nama,username,password,role
     FROM users
     WHERE username=?"
);

mysqli_stmt_bind_param($stmt,"s",$username);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){

    echo json_encode([
        "success"=>false,
        "message"=>"Username tidak ditemukan"
    ]);

    exit;

}

$user=mysqli_fetch_assoc($result);

if(!password_verify($password,$user['password'])){

    echo json_encode([
        "success"=>false,
        "message"=>"Password salah"
    ]);

    exit;

}

$_SESSION['login']=true;
$_SESSION['id']=$user['id'];
$_SESSION['nama']=$user['nama'];
$_SESSION['username']=$user['username'];
$_SESSION['role']=$user['role'];

echo json_encode([
    "success"=>true,
    "message"=>"Login berhasil"
]);