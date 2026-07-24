<?php
session_start();

// Jika sudah login, redirect ke dashboard
if(isset($_SESSION['login']) && $_SESSION['login'] === true){
    header("Location: dashboard.php");
    exit;
}

// Proses Register
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    require_once __DIR__ . '/../config/config.php';
    
    if (!$conn) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }
    
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm_password']);
    
    // Validasi
    if(empty($fullname) || empty($email) || empty($password) || empty($confirm)){
        $error = "Semua field wajib diisi";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Format email tidak valid";
    } elseif($password != $confirm){
        $error = "Password tidak sama";
    } elseif(strlen($password) < 6){
        $error = "Password minimal 6 karakter";
    } else {
        // Cek email sudah terdaftar
        $cek_email = mysqli_prepare($conn, "SELECT email FROM users WHERE email = ?");
        mysqli_stmt_bind_param($cek_email, "s", $email);
        mysqli_stmt_execute($cek_email);
        mysqli_stmt_store_result($cek_email);
        
        if(mysqli_stmt_num_rows($cek_email) > 0){
            $error = "Email sudah terdaftar";
        } else {
            // Enkripsi password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert data
            $sql = "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $fullname, $email, $password_hash);
            
            if(mysqli_stmt_execute($stmt)){
                $success = "Register berhasil! Silakan login.";
            } else {
                $error = "Register gagal: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        }
        
        mysqli_stmt_close($cek_email);
        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Register | Coffee Bean IoT</title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Poppins',sans-serif;
        }

        :root{
            --bg:#071207;
            --card:rgba(15,30,15,0.72);
            --border:rgba(255,255,255,0.05);
            --primary:#75ff43;
            --text:#ffffff;
            --muted:#8d9a8d;
            --error:#ff4444;
            --success:#44ff88;
        }

        body{
            min-height:100vh;
            background:var(--bg);
            overflow-x:hidden;
            position:relative;
            color:var(--text);
        }

        body::before{
            content:"";
            position:absolute;
            width:850px;
            height:850px;
            background:radial-gradient(circle, rgba(117,255,67,0.10) 0%, transparent 70%);
            top:50%;
            left:50%;
            transform:translate(-50%,-50%);
            z-index:0;
        }

        body::after{
            content:"";
            position:absolute;
            inset:0;
            background:repeating-linear-gradient(90deg, rgba(117,255,67,0.02), rgba(117,255,67,0.02) 1px, transparent 1px, transparent 85px);
            z-index:0;
        }

        .navbar{
            width:100%;
            height:78px;
            padding:0 24px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            border-bottom:1px solid rgba(255,255,255,0.03);
            position:relative;
            z-index:5;
        }

        .brand{
            display:flex;
            align-items:center;
            gap:12px;
        }

        .brand-icon{
            width:34px;
            height:34px;
            border-radius:10px;
            background:rgba(117,255,67,0.12);
            display:flex;
            justify-content:center;
            align-items:center;
            color:var(--primary);
            font-size:14px;
        }

        .brand-text{
            font-size:18px;
            font-weight:700;
        }

        .brand-text span{
            color:var(--primary);
            font-size:14px;
            margin-left:3px;
            font-weight:500;
        }

        .nav-right{
            display:flex;
            align-items:center;
            gap:8px;
            color:#98a498;
            font-size:12px;
        }

        .nav-right a{
            color:var(--primary);
            text-decoration:none;
            font-weight:600;
        }

        .wrapper{
            width:100%;
            min-height:calc(100vh - 78px);
            display:flex;
            justify-content:center;
            align-items:center;
            padding:30px 20px;
            position:relative;
            z-index:2;
        }

        .register-card{
            width:100%;
            max-width:470px;
            padding:34px;
            border-radius:22px;
            background:rgba(15,30,15,0.72);
            border:1px solid rgba(255,255,255,0.05);
            backdrop-filter:blur(14px);
            box-shadow:0 0 40px rgba(0,0,0,0.45), 0 0 80px rgba(117,255,67,0.03);
        }

        .register-card h1{
            font-size:38px;
            font-weight:700;
            margin-bottom:28px;
        }

        .alert-error{
            background:rgba(255,68,68,0.15);
            border:1px solid rgba(255,68,68,0.3);
            color:#ff6b6b;
            padding:12px 16px;
            border-radius:10px;
            margin-bottom:20px;
            font-size:14px;
            display:<?php echo isset($error) ? 'block' : 'none'; ?>;
        }

        .alert-success{
            background:rgba(68,255,136,0.15);
            border:1px solid rgba(68,255,136,0.3);
            color:#44ff88;
            padding:12px 16px;
            border-radius:10px;
            margin-bottom:20px;
            font-size:14px;
            display:<?php echo isset($success) ? 'block' : 'none'; ?>;
        }

        .input-group{
            margin-bottom:20px;
        }

        .input-group label{
            display:block;
            margin-bottom:9px;
            color:#b3beb3;
            font-size:12px;
            font-weight:500;
        }

        .input-box{
            position:relative;
        }

        .input-box input{
            width:100%;
            height:52px;
            border-radius:12px;
            border:1px solid rgba(255,255,255,0.05);
            background:rgba(255,255,255,0.02);
            padding:0 48px 0 16px;
            color:white;
            font-size:14px;
            outline:none;
            transition:0.3s;
        }

        .input-box input:focus{
            border-color:rgba(117,255,67,0.25);
            box-shadow:0 0 14px rgba(117,255,67,0.08);
        }

        .input-box input::placeholder{
            color:#708070;
        }

        .input-box i{
            position:absolute;
            top:50%;
            right:16px;
            transform:translateY(-50%);
            color:#7f8b7f;
            font-size:14px;
            cursor:pointer;
        }

        .password-row{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:14px;
        }

        .btn{
            width:100%;
            height:54px;
            border:none;
            border-radius:12px;
            background:var(--primary);
            color:black;
            font-size:16px;
            font-weight:700;
            cursor:pointer;
            margin-top:24px;
            transition:0.3s;
            box-shadow:0 0 25px rgba(117,255,67,0.18);
        }

        .btn:hover{
            transform:translateY(-2px);
            box-shadow:0 0 35px rgba(117,255,67,0.28);
        }

        @media(max-width:768px){
            .register-card{
                padding:28px;
            }
            .register-card h1{
                font-size:32px;
            }
        }

        @media(max-width:580px){
            .navbar{
                padding:0 18px;
            }
            .wrapper{
                padding:20px 16px;
            }
            .register-card{
                padding:24px;
                border-radius:20px;
            }
            .password-row{
                grid-template-columns:1fr;
            }
            .nav-right{
                display:none;
            }
            .register-card h1{
                font-size:28px;
            }
            .btn{
                height:50px;
                font-size:15px;
            }
        }

        body::before,
        body::after{
            pointer-events:none;
        }

        a{
            position:relative;
            z-index:999;
            pointer-events:auto;
            cursor:pointer;
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="brand">
        <div class="brand-icon">
            <i class="fa-solid fa-mug-hot"></i>
        </div>
        <div class="brand-text">
            Coffee Bean <span>IoT</span>
        </div>
    </div>

    <div class="nav-right">
        <span>Already have an account?</span>
        <a href="login.php">Sign In</a>
    </div>
</nav>

<div class="wrapper">
    <div class="register-card">
        <h1>Create Account</h1>

        <?php if(isset($error)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if(isset($success)): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <script>
                    setTimeout(function(){
                        window.location.href = 'login.php';
                    }, 2000);
                </script>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <label>Full Name</label>
                <div class="input-box">
                    <input
                        type="text"
                        name="fullname"
                        placeholder="Name"
                        value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>"
                        required>
                    <i class="fa-regular fa-user"></i>
                </div>
            </div>

            <div class="input-group">
                <label>Email Address</label>
                <div class="input-box">
                    <input
                        type="email"
                        name="email"
                        placeholder="Email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required>
                    <i class="fa-regular fa-envelope"></i>
                </div>
            </div>

            <div class="password-row">
                <div class="input-group">
                    <label>Password</label>
                    <div class="input-box">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            required>
                        <i class="fa-regular fa-eye toggle-password" toggle="#password"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label>Confirm Password</label>
                    <div class="input-box">
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="••••••••"
                            required>
                        <i class="fa-regular fa-eye toggle-password" toggle="#confirm_password"></i>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn">Confirm</button>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.toggle-password').forEach(function(icon){
        icon.addEventListener('click', function(){
            const input = document.querySelector(this.getAttribute('toggle'));
            if(input.type === "password"){
                input.type = "text";
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    });
</script>

</body>
</html>