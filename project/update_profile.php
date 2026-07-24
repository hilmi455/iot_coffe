<?php
session_start();

require_once __DIR__ . '/../config/config.php';

// Cek login
if(!isset($_SESSION['email'])){
    header("Location: login.php");
    exit;
}

// Cek apakah form disubmit
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    $email = $_SESSION['email'];
    
    // Ambil data dari form
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    
    // Cek apakah kolom username ada di database
    // Jika tidak ada, gunakan email atau fullname sebagai username
    $username = isset($_POST['username']) ? mysqli_real_escape_string($conn, $_POST['username']) : $fullname;
    
    // Cek struktur tabel terlebih dahulu
    $check_query = "SHOW COLUMNS FROM users LIKE 'username'";
    $check_result = mysqli_query($conn, $check_query);
    $has_username = mysqli_num_rows($check_result) > 0;
    
    if($has_username){
        // Jika kolom username ada
        $query = "UPDATE users SET 
                  fullname = '$fullname',
                  username = '$username'
                  WHERE email = '$email'";
    } else {
        // Jika kolom username tidak ada, update hanya fullname
        $query = "UPDATE users SET 
                  fullname = '$fullname'
                  WHERE email = '$email'";
    }
    
    if(mysqli_query($conn, $query)){
        // Update session jika diperlukan
        $_SESSION['fullname'] = $fullname;
        
        // Redirect dengan pesan sukses
        header("Location: profile.php?success=1");
        exit;
    } else {
        // Jika error
        $error = mysqli_error($conn);
        header("Location: profile.php?error=" . urlencode($error));
        exit;
    }
    
} else {
    // Jika bukan POST request
    header("Location: profile.php");
    exit;
}
?>