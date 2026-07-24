<?php
session_start();

/* Jika user menekan tombol logout */

if(isset($_POST['logout'])){

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Logout Confirmation</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{

    min-height:100vh;

    display:flex;
    justify-content:center;
    align-items:center;

    overflow:hidden;

    position:relative;

     background:
    linear-gradient(
    135deg,
    #041204,
    #071b07,
    #0a220a
    );

}

/* Overlay Blur */

body::before{

    content:"";

    position:absolute;
    inset:0;

    background:
    rgba(3,12,3,0.22);

    backdrop-filter:blur(4px);

    z-index:1;
}

/* Modal */

.logout-modal{

    position:relative;
    z-index:2;

    width:380px;

    background:
    rgba(223,233,216,0.88);

    border:
    1px solid rgba(255,255,255,0.18);

    backdrop-filter:blur(12px);

    border-radius:30px;

    padding:38px 30px;

    text-align:center;

    box-shadow:
    0 20px 60px rgba(0,0,0,0.22);

    background:
    linear-gradient(
    180deg,
    rgba(228,240,223,0.92),
    rgba(214,231,213,0.86)
    );

}

/* Title */

.logout-modal h2{

    font-size:34px;
    margin-bottom:25px;

    color:#1d1d1d;

}

/* Icon */

.icon{

    width:74px;
    height:74px;

    border-radius:50%;

    border:4px solid #1f1f1f;

    display:flex;
    justify-content:center;
    align-items:center;

    margin:0 auto 24px;

    font-size:38px;
    font-weight:700;

    color:#1f1f1f;
}

/* Text */

.logout-modal p{

    font-size:20px;
    line-height:1.7;

    color:#232323;

    margin-bottom:34px;
}

/* Buttons */

.btn-group{
    display:flex;
    flex-direction:column;
    gap:18px;
}

.btn-group form{
    width:100%;
}

/* Button */

.btn{

    width:100%;

    height:58px;

    border:none;
    border-radius:50px;

    font-size:22px;
    font-weight:600;

    cursor:pointer;
    transition:0.3s;
}

/* Cancel */

.btn-cancel{

    background:
    rgba(214,231,213,0.75);

    color:#1f1f1f;

    backdrop-filter:blur(4px);
}

/* Logout */

.btn-logout{

    background:#3d742d;
    color:white;

}

.btn-logout:hover{

    background:#315f24;

}

</style>

</head>

<body>

    <div class="logout-modal">

        <h2>
            Konfirmasi Logout
        </h2>

        <div class="icon">
            !
        </div>

        <p>

            <strong>Apakah Anda</strong>
            yakin ingin keluar dari akun ini?

        </p>

        <div class="btn-group">

            <!-- Cancel -->

            <button
            class="btn btn-cancel"
            onclick="window.history.back()">

                Batal

            </button>

            <!-- Logout -->

            <form method="POST">

                <button
                type="submit"
                name="logout"
                class="btn btn-logout">

                    Logout

                </button>

            </form>

        </div>

    </div>

</body>
</html>