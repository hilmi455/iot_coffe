<?php

session_start();

require_once __DIR__ . '/../config/config.php';

$email = $_POST['email'];

/* Cek email */

$query = mysqli_query($conn,

"SELECT * FROM users
WHERE email='$email'"

);

if(mysqli_num_rows($query) > 0){

    $_SESSION['reset_email'] = $email;

    header("Location: reset_password.php");
    exit();

}else{

    echo "

    <script>

    alert('Email tidak ditemukan');

    window.location='forgot_password.php';

    </script>

    ";

}

?>