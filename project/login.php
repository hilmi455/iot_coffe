<?php
session_start();

// Jika sudah login, redirect ke dashboard
if(isset($_SESSION['login']) && $_SESSION['login'] === true){
    header("Location: dashboard.php");
    exit;
}

// Cek apakah ada pesan password berubah
$password_changed = isset($_SESSION['password_changed']);
if($password_changed){
    unset($_SESSION['password_changed']);
}

// Proses Login
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    require_once __DIR__ . '/../config/config.php';
    
    if (!$conn) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }
    
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if(empty($username) || empty($password)){
        $error = "Email dan Password wajib diisi";
    } else {
        // Query dengan prepared statement
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) > 0){
            $data = mysqli_fetch_assoc($result);
            
            if(password_verify($password, $data['password'])){
                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $data['id'];
                $_SESSION['username'] = $data['fullname'];
                $_SESSION['email'] = $data['email'];
                $_SESSION['role'] = $data['role'];
                
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Password salah";
            }
        } else {
            $error = "Email tidak ditemukan";
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login | Coffee Bean IoT</title>

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
            --primary:#75ff43;
            --text:#ffffff;
            --error:#ff4444;
            --success:#75ff43;
        }

        body{
            min-height:100vh;
            background:var(--bg);
            color:var(--text);
            display:flex;
            flex-direction:column;
            position:relative;
            overflow-x:hidden;
        }

        body::before{
            content:"";
            position:absolute;
            width:850px;
            height:850px;
            background:radial-gradient(circle, rgba(117,255,67,0.05) 0%, transparent 70%);
            top:50%;
            left:50%;
            transform:translate(-50%,-50%);
            z-index:0;
            pointer-events:none;
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

        .main-wrapper{
            flex:1;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:40px 20px;
            position:relative;
            z-index:2;
        }

        .login-box{
            width:100%;
            max-width:420px;
            padding:40px;
            border-radius:22px;
            background:var(--card);
            border:1px solid rgba(255,255,255,0.05);
            backdrop-filter:blur(14px);
            box-shadow:0 0 40px rgba(0,0,0,0.4);
        }

        .login-box h2{
            font-size:32px;
            font-weight:700;
            margin-bottom:30px;
            text-align:center;
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
            background:rgba(117,255,67,0.15);
            border:1px solid rgba(117,255,67,0.3);
            color:var(--primary);
            padding:12px 16px;
            border-radius:10px;
            margin-bottom:20px;
            font-size:14px;
            display:<?php echo isset($password_changed) && $password_changed ? 'block' : 'none'; ?>;
        }

        .input-group{
            margin-bottom:20px;
        }

        .input-group label{
            display:block;
            margin-bottom:8px;
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

        .input-box i{
            position:absolute;
            top:50%;
            right:16px;
            transform:translateY(-50%);
            color:#7f8b7f;
            font-size:14px;
            cursor:pointer;
        }

        .options{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-top:-10px;
            margin-bottom:24px;
            font-size:13px;
        }

        .remember{
            display:flex;
            align-items:center;
            gap:8px;
            color:#92a092;
        }

        .remember input{
            accent-color:var(--primary);
        }

        .options a{
            color:var(--primary);
            text-decoration:none;
            transition:0.3s;
        }

        .options a:hover{
            color:#a8ff8a;
            text-decoration:underline;
        }

        .btn{
            width:100%;
            height:54px;
            border:none;
            border-radius:12px;
            background:var(--primary);
            color:#000;
            font-size:16px;
            font-weight:700;
            cursor:pointer;
            transition:0.3s;
            box-shadow:0 0 20px rgba(117,255,67,0.15);
        }

        .btn:hover{
            transform:translateY(-2px);
            box-shadow:0 0 30px rgba(117,255,67,0.25);
        }

        .signup{
            margin-top:24px;
            text-align:center;
            font-size:13px;
            color:#98a498;
        }

        .signup a{
            color:var(--primary);
            text-decoration:none;
            font-weight:600;
            margin-left:5px;
            transition:0.3s;
        }

        .signup a:hover{
            color:#a8ff8a;
            text-decoration:underline;
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
</nav>

<main class="main-wrapper">
    <div class="login-box">
        <h2>Sign In</h2>

        <!-- Password Changed Success Message -->
        <?php if(isset($password_changed) && $password_changed): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> Password berhasil diubah! Silakan login dengan password baru.
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if(isset($error)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <label>Email or Username</label>
                <div class="input-box">
                    <input
                        type="text"
                        name="username"
                        placeholder="Enter your credentials"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                        required>
                    <i class="fa-regular fa-user"></i>
                </div>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="input-box">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="••••••••"
                        required>
                    <i class="fa-regular fa-eye toggle-password"></i>
                </div>
            </div>

            <!-- ===== OPTIONS: Remember Me & Forgot Password ===== -->
            <div class="options">
                <div class="remember">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Remember me</label>
                </div>
                <a href="forgot_password.php">
                    <i class="fa-regular fa-question-circle"></i> Forgot password?
                </a>
            </div>

            <button type="submit" class="btn">
                Sign In to Dashboard
            </button>
        </form>

        <div class="signup">
            Don't have an account?
            <a href="register.php">Sign Up</a>
        </div>
    </div>
</main>

<script>
    document.querySelector(".toggle-password").addEventListener("click", function(){
        const passwordInput = document.querySelector("#password");
        const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
        passwordInput.setAttribute("type", type);
        this.classList.toggle("fa-eye");
        this.classList.toggle("fa-eye-slash");
    });
</script>

</body>
</html>