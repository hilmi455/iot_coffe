<?php

session_start();

require_once __DIR__ . '/../config/config.php';

if (!$conn) {
    die("Koneksi database gagal");
}

// AMBIL DATA FORM
$username = trim($_POST['username']);
$password = trim($_POST['password']);


// CEK INPUT KOSONG
if(empty($username) || empty($password)){

    echo "
    <script>

    alert('Email dan Password wajib diisi');

    window.location='login.php';

    </script>
    ";

    exit;
}


// QUERY USER BERDASARKAN EMAIL
$sql = "SELECT * FROM users WHERE email='$username'";

$query = mysqli_query($conn, $sql);


// CEK QUERY BERHASIL
if(!$query){

    die("Query error : " . mysqli_error($conn));

}


// CEK USER ADA ATAU TIDAK
if(mysqli_num_rows($query) > 0){

    $data = mysqli_fetch_assoc($query);

    // VERIFIKASI PASSWORD
    if($password == $data['password']){

        $_SESSION['login'] = true;
        $_SESSION['username'] = $data['fullname'];
        $_SESSION['email'] = $data['email'];

        header("Location: dashboard.php");
        exit;

    }else{

        echo "
        <script>

        alert('Password salah');

        window.location='login.php';

        </script>
        ";

        exit;
    }

}else{

    echo "
    <script>

    alert('Email tidak ditemukan');

    window.location='login.php';

    </script>
    ";

    exit;
}

?>