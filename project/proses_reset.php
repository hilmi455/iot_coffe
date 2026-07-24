<?php

session_start();

$new_password = trim($_POST['new_password']);
$confirm_password = trim($_POST['confirm_password']);

/* Cek password sama atau tidak */

if($new_password != $confirm_password){

    echo "
    <script>

    alert('Konfirmasi password tidak sama');

    window.location='reset_password.php';

    </script>
    ";

    exit;
}

/* Cek minimal password */

if(strlen($new_password) < 8){

    echo "
    <script>

    alert('Password minimal 8 karakter');

    window.location='reset_password.php';

    </script>
    ";

    exit;
}

/*
Simulasi reset berhasil
*/

echo "
<script>

alert('Password berhasil direset');

window.location='login.php';

</script>
";

?>