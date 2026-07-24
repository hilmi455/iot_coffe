<?php
session_start();

// Cek login
if(!isset($_SESSION['login']) || $_SESSION['login'] !== true){
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = $_SESSION['email'];
    
    // Validasi
    if(empty($fullname) || empty($username)){
        $_SESSION['error_message'] = "Full name dan username tidak boleh kosong!";
        header("Location: profile.php");
        exit;
    }
    
    // Escape string
    $fullname = mysqli_real_escape_string($conn, $fullname);
    $username = mysqli_real_escape_string($conn, $username);
    $email = mysqli_real_escape_string($conn, $email);
    
    // Query update
    $query = "UPDATE users SET fullname='$fullname', username='$username' WHERE email='$email'";
    
    if(mysqli_query($conn, $query)){
        // Update session
        $_SESSION['username'] = $username;
        $_SESSION['fullname'] = $fullname;
        
        $_SESSION['success_message'] = "Profile berhasil diupdate!";
        header("Location: profile.php");
        exit;
    } else {
        $_SESSION['error_message'] = "Gagal mengupdate profile: " . mysqli_error($conn);
        header("Location: profile.php");
        exit;
    }
} else {
    header("Location: profile.php");
    exit;
}
?>