<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>IoT Coffee Sorter</title>

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
    background:#071207;
    color:white;
    display:flex;
    justify-content:center;
    align-items:center;
}

.container{
    text-align:center;
}

h1{
    font-size:58px;
    margin-bottom:20px;
}

p{
    color:#a8b5a8;
    margin-bottom:35px;
}

.btn{
    display:inline-block;
    padding:14px 30px;
    background:#75ff43;
    color:black;
    text-decoration:none;
    border-radius:12px;
    font-weight:600;
}

</style>
</head>

<body>

<div class="container">

    <h1>IoT Coffee Sorter</h1>

    <p>
        Smart coffee bean classification monitoring system.
    </p>

    <a href="login.php" class="btn">
        Login Dashboard
    </a>

</div>

</body>
</html>