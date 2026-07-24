<?php

session_start();

require_once __DIR__ . '/../config/config.php';


// CEK KONEKSI DATABASE
if(!$conn){

    die("Koneksi database gagal");

}


// AMBIL DATA FORM
$fullname = trim($_POST['fullname']);
$email    = trim($_POST['email']);
$password = trim($_POST['password']);
$confirm  = trim($_POST['confirm_password']);


// VALIDASI PASSWORD
if($password != $confirm){

    echo "
    <script>

    alert('Password tidak sama');

    window.location='register.php';

    </script>
    ";

    exit;
}


// CEK EMAIL SUDAH TERDAFTAR ATAU BELUM
$cek_email = mysqli_query(
    $conn,
    "SELECT * FROM users WHERE email='$email'"
);


// CEK QUERY ERROR
if(!$cek_email){

    die("Query Error : " . mysqli_error($conn));

}


// JIKA EMAIL SUDAH ADA
if(mysqli_num_rows($cek_email) > 0){

    echo "
    <script>

    alert('Email sudah terdaftar');

    window.location='register.php';

    </script>
    ";

    exit;
}


// ENKRIPSI PASSWORD
$password_hash = $password;


// INSERT DATA USER
$sql = "
INSERT INTO users(
    fullname,
    email,
    password
)

VALUES(
    '$fullname',
    '$email',
    '$password_hash'
)
";

$query = mysqli_query($conn, $sql);


// CEK INSERT BERHASIL
if($query){

    echo "
    <script>

    alert('Register berhasil');

    window.location='login.php';

    </script>
    ";

}else{

    echo "
    <script>

    alert('Register gagal');

    window.location='register.php';

    </script>
    ";

}

?>