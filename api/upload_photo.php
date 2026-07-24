<?php
session_start();

if(!isset($_SESSION['login']) || $_SESSION['login'] !== true){
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/config.php';

// Debug - tampilkan info
echo "<!-- DEBUG: File upload_photo.php dijalankan -->";

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_photo'])){
    $email = mysqli_real_escape_string($conn, $_SESSION['email']);
    $file = $_FILES['profile_photo'];
    
    // Debug
    echo "<!-- File: " . $file['name'] . " -->";
    echo "<!-- Size: " . $file['size'] . " -->";
    echo "<!-- Type: " . $file['type'] . " -->";
    
    // Cek error upload
    if($file['error'] !== UPLOAD_ERR_OK){
        $_SESSION['error_message'] = "Error upload file: " . $file['error'];
        header("Location: profile.php");
        exit;
    }
    
    // Validasi file
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    $max_size = 5 * 1024 * 1024;
    
    if(!in_array($file['type'], $allowed_types)){
        $_SESSION['error_message'] = "Format file tidak didukung. Gunakan JPG, PNG, atau GIF.";
        header("Location: profile.php");
        exit;
    }
    
    if($file['size'] > $max_size){
        $_SESSION['error_message'] = "Ukuran file terlalu besar. Maksimal 5MB.";
        header("Location: profile.php");
        exit;
    }
    
    // Tentukan path upload - SESUAIKAN DENGAN STRUKTUR ANDA
    // Coba beberapa kemungkinan path
    $base_path = $_SERVER['DOCUMENT_ROOT'];
    
    // Coba cari folder project
    $possible_paths = [
        $base_path . '/iot_coffee/uploads/profile/',
        $base_path . '/uploads/profile/',
        __DIR__ . '/uploads/profile/',
        __DIR__ . '/../uploads/profile/',
    ];
    
    $upload_dir = '';
    foreach($possible_paths as $path){
        if(file_exists($path) || mkdir($path, 0777, true)){
            $upload_dir = $path;
            break;
        }
    }
    
    if(empty($upload_dir)){
        $_SESSION['error_message'] = "Gagal membuat direktori upload. Coba buat manual folder 'uploads/profile' di root project.";
        header("Location: profile.php");
        exit;
    }
    
    // Generate nama file
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = md5($email . time() . rand(1000, 9999)) . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Path untuk database (relative dari root)
    $relative_path = '/uploads/profile/' . $filename;
    
    // Pindahkan file
    if(move_uploaded_file($file['tmp_name'], $filepath)){
        // Update database
        $query = "UPDATE users SET profile_photo='$relative_path' WHERE email='$email'";
        
        if(mysqli_query($conn, $query)){
            $_SESSION['profile_photo'] = $relative_path;
            $_SESSION['success_message'] = "Foto profile berhasil diupload!";
        } else {
            $_SESSION['error_message'] = "Gagal update database: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_message'] = "Gagal memindahkan file. Cek permission folder.";
    }
    
    header("Location: profile.php");
    exit;
} else {
    echo "Error: Tidak ada file yang diupload atau method bukan POST";
    echo "<br><a href='profile.php'>Kembali ke Profile</a>";
    exit;
}
?>