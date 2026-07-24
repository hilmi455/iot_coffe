<?php

session_start();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Forgot Password | IoT Coffee</title>

<link rel="preconnect" href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

:root{
    --bg:#071207;
    --card:#0d1d0e;
    --primary:#75ff43;
    --text:#ffffff;
    --muted:#8d9a8d;
}

body{

    min-height:100vh;

    background:var(--bg);

    display:flex;
    justify-content:center;
    align-items:center;

    overflow:hidden;

    position:relative;
}

/* Glow */

body::before{

    content:"";

    position:absolute;

    width:800px;
    height:800px;

    background:
    radial-gradient(
    circle,
    rgba(117,255,67,0.10),
    transparent 70%
    );

    top:50%;
    left:50%;

    transform:translate(-50%,-50%);
}

/* Card */

.forgot-card{

    width:100%;
    max-width:430px;

    background:
    rgba(13,29,14,0.88);

    border:
    1px solid rgba(255,255,255,0.05);

    border-radius:24px;

    padding:36px;

    position:relative;
    z-index:2;

    backdrop-filter:blur(12px);

    box-shadow:
    0 20px 50px rgba(0,0,0,0.35);
}

/* Icon */

.top-icon{

    width:68px;
    height:68px;

    border-radius:18px;

    background:
    rgba(117,255,67,0.10);

    display:flex;
    justify-content:center;
    align-items:center;

    color:var(--primary);

    font-size:24px;

    margin-bottom:22px;
}

/* Title */

.forgot-card h1{

    font-size:34px;
    margin-bottom:10px;

    color:white;
}

.subtitle{

    color:var(--muted);

    margin-bottom:28px;

    line-height:1.6;
}

/* Input */

.input-group{

    margin-bottom:22px;
}

.input-group label{

    display:block;

    margin-bottom:10px;

    color:#b3beb3;

    font-size:13px;
}

.input-box{

    position:relative;
}

.input-box input{

    width:100%;
    height:54px;

    border:none;
    outline:none;

    border-radius:14px;

    background:
    rgba(255,255,255,0.03);

    border:
    1px solid rgba(255,255,255,0.05);

    color:white;

    padding:0 50px 0 16px;

    font-size:14px;
}

.input-box i{

    position:absolute;

    top:50%;
    right:16px;

    transform:translateY(-50%);

    color:#92a092;
}

/* Button */

.btn{

    width:100%;
    height:54px;

    border:none;

    border-radius:14px;

    background:var(--primary);

    color:black;

    font-size:16px;
    font-weight:700;

    cursor:pointer;

    transition:0.3s;
}

.btn:hover{

    transform:translateY(-2px);

    box-shadow:
    0 0 25px rgba(117,255,67,0.25);
}

/* Back */

.back-login{

    margin-top:22px;

    text-align:center;
}

.back-login a{

    color:var(--primary);

    text-decoration:none;

    font-size:14px;
}

</style>

</head>

<body>

<div class="forgot-card">

    <!-- Icon -->

    <div class="top-icon">

        <i class="fa-solid fa-envelope"></i>

    </div>

    <!-- Title -->

    <h1>
        Forgot Password
    </h1>

    <div class="subtitle">

        Enter your registered email to reset your password.

    </div>

    <!-- Form -->

    <form
    action="proses_forgot.php"
    method="POST">

        <div class="input-group">

            <label>
                Email Address
            </label>

            <div class="input-box">

                <input
                type="email"
                name="email"
                placeholder="Enter your email"
                required>

                <i class="fa-regular fa-envelope"></i>

            </div>

        </div>

        <!-- Button -->

        <button
        type="submit"
        class="btn">

            Verify Email

        </button>

    </form>

    <!-- Back -->

    <div class="back-login">

        <a href="login.php">

            <i class="fa-solid fa-arrow-left"></i>

            Back to Login

        </a>

    </div>

</div>

</body>
</html>