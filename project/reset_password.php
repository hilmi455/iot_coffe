<?php
session_start();

// Jika sudah login, redirect ke dashboard
if(isset($_SESSION['login']) && $_SESSION['login'] === true){
    header("Location: dashboard.php");
    exit;
}

$error = '';
$step = 'verify'; // verify | reset | success
$fullname = '';
$email = '';

// STEP 1: Proses verifikasi nama & email
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_user'])){
    require_once __DIR__ . '/../config/config.php';
    
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    
    if(empty($fullname) || empty($email)){
        $error = "Nama dan email wajib diisi!";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Format email tidak valid!";
    } else {
        // Cek di database
        $fullname = mysqli_real_escape_string($conn, $fullname);
        $email = mysqli_real_escape_string($conn, $email);
        
        $query = mysqli_query($conn, "SELECT id, fullname, email FROM users WHERE LOWER(fullname)=LOWER('$fullname') AND email='$email'");
        
        if(mysqli_num_rows($query) > 0){
            $user = mysqli_fetch_assoc($query);
            
            // Simpan di session untuk proses reset
            $_SESSION['reset_email'] = $user['email'];
            $_SESSION['reset_fullname'] = $user['fullname'];
            
            $step = 'reset';
        } else {
            $error = "Nama dan email tidak cocok! Pastikan data yang dimasukkan benar.";
        }
    }
}

// STEP 2: Proses reset password
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])){
    require_once __DIR__ . '/../config/config.php';
    
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_SESSION['reset_email'] ?? '';
    
    if(empty($new_password) || empty($confirm_password)){
        $error = "Password baru dan konfirmasi wajib diisi!";
    } elseif(strlen($new_password) < 6){
        $error = "Password minimal 6 karakter!";
    } elseif($new_password !== $confirm_password){
        $error = "Password tidak cocok!";
    } elseif(empty($email)){
        $error = "Sesi tidak valid. Silakan verifikasi ulang.";
        $step = 'verify';
    } else {
        // Hash password baru
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $email = mysqli_real_escape_string($conn, $email);
        
        // Update password
        $update = mysqli_query($conn, "UPDATE users SET password='$hashed_password' WHERE email='$email'");
        
        if($update){
            // Hapus session reset
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_fullname']);
            
            $step = 'success';
        } else {
            $error = "Gagal mereset password. Silakan coba lagi.";
        }
    }
}

// Cek session untuk step
if(isset($_SESSION['reset_email']) && $step === 'verify'){
    $step = 'reset';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | IoT Coffee</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --bg: #071207;
            --card: #0d1d0e;
            --primary: #75ff43;
            --text: #ffffff;
            --muted: #8d9a8d;
            --error: #ff4d4d;
        }

        body {
            min-height: 100vh;
            background: var(--bg);
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(117,255,67,0.10), transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .forgot-card {
            width: 100%;
            max-width: 450px;
            background: rgba(13,29,14,0.88);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 24px;
            padding: 36px;
            position: relative;
            z-index: 2;
            backdrop-filter: blur(12px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.35);
        }

        .top-icon {
            width: 68px;
            height: 68px;
            border-radius: 18px;
            background: rgba(117,255,67,0.10);
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--primary);
            font-size: 24px;
            margin-bottom: 22px;
        }

        .forgot-card h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: white;
        }

        .subtitle {
            color: var(--muted);
            margin-bottom: 28px;
            line-height: 1.6;
        }

        .subtitle .highlight {
            color: var(--primary);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: rgba(255,68,68,0.12);
            border: 1px solid rgba(255,68,68,0.2);
            color: var(--error);
        }

        .alert-success {
            background: rgba(117,255,67,0.12);
            border: 1px solid rgba(117,255,67,0.2);
            color: var(--primary);
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 10px;
            color: #b3beb3;
            font-size: 13px;
        }

        .input-box {
            position: relative;
        }

        .input-box input {
            width: 100%;
            height: 54px;
            border: none;
            outline: none;
            border-radius: 14px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05);
            color: white;
            padding: 0 50px 0 16px;
            font-size: 14px;
            transition: 0.3s;
        }

        .input-box input:focus {
            border-color: rgba(117,255,67,0.25);
            box-shadow: 0 0 14px rgba(117,255,67,0.08);
        }

        .input-box i {
            position: absolute;
            top: 50%;
            right: 16px;
            transform: translateY(-50%);
            color: #92a092;
            cursor: pointer;
        }

        .input-box i:hover {
            color: var(--primary);
        }

        .btn {
            width: 100%;
            height: 54px;
            border: none;
            border-radius: 14px;
            background: var(--primary);
            color: black;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 25px rgba(117,255,67,0.25);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.05);
            box-shadow: none;
        }

        .back-login {
            margin-top: 22px;
            text-align: center;
        }

        .back-login a {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .back-login a:hover {
            color: #a8ff8a;
        }

        .user-info {
            background: rgba(117,255,67,0.05);
            border: 1px solid rgba(117,255,67,0.1);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .user-info .name {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
        }

        .user-info .email {
            font-size: 13px;
            color: var(--muted);
            margin-top: 4px;
        }

        .verify-note {
            font-size: 11px;
            color: var(--muted);
            margin-top: 4px;
            padding-left: 4px;
        }

        .verify-note i {
            color: var(--primary);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 20px 0;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.05);
        }

        .divider span {
            color: var(--muted);
            font-size: 12px;
        }

        .strength {
            width: 100%;
            height: 3px;
            border-radius: 20px;
            background: rgba(255,255,255,0.04);
            overflow: hidden;
            margin-top: 10px;
        }

        .strength-fill {
            width: 0%;
            height: 100%;
            transition: width 0.3s ease;
        }

        .strength-text {
            font-size: 10px;
            color: var(--muted);
            margin-top: 4px;
        }
    </style>
</head>

<body>
    <div class="forgot-card">
        <!-- STEP 1: Verifikasi Nama + Email -->
        <?php if($step === 'verify'): ?>
            <div class="top-icon">
                <i class="fa-solid fa-user-check"></i>
            </div>

            <h1>Forgot Password</h1>
            <div class="subtitle">
                Enter your <span class="highlight">full name</span> and <span class="highlight">email</span> to verify your identity.
            </div>

            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <!-- NAMA LENGKAP -->
                <div class="input-group">
                    <label>Full Name</label>
                    <div class="input-box">
                        <input
                            type="text"
                            name="fullname"
                            placeholder="Enter your full name"
                            value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>"
                            required>
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <div class="verify-note">
                        <i class="fa-regular fa-circle-check"></i> Masukkan nama lengkap sesuai dengan yang terdaftar
                    </div>
                </div>

                <!-- EMAIL -->
                <div class="input-group">
                    <label>Email Address</label>
                    <div class="input-box">
                        <input
                            type="email"
                            name="email"
                            placeholder="Enter your registered email"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            required>
                        <i class="fa-regular fa-envelope"></i>
                    </div>
                </div>

                <button type="submit" name="verify_user" class="btn">
                    <i class="fa-solid fa-check"></i> Verify Identity
                </button>
            </form>

            <div class="back-login">
                <a href="login.php">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Login
                </a>
            </div>

        <!-- STEP 2: Ganti Password -->
        <?php elseif($step === 'reset'): ?>
            <div class="top-icon">
                <i class="fa-solid fa-key"></i>
            </div>

            <h1>Reset Password</h1>
            <div class="subtitle">
                Set a new password for <span class="highlight"><?php echo htmlspecialchars($_SESSION['reset_fullname'] ?? ''); ?></span>
            </div>

            <!-- Tampilkan info user yang sedang reset -->
            <div class="user-info">
                <div class="name">
                    <i class="fa-regular fa-user" style="margin-right: 8px;"></i>
                    <?php echo htmlspecialchars($_SESSION['reset_fullname'] ?? ''); ?>
                </div>
                <div class="email">
                    <i class="fa-regular fa-envelope" style="margin-right: 6px;"></i>
                    <?php echo htmlspecialchars($_SESSION['reset_email'] ?? ''); ?>
                </div>
            </div>

            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="input-group">
                    <label>New Password</label>
                    <div class="input-box">
                        <input
                            type="password"
                            name="new_password"
                            id="newPassword"
                            placeholder="Enter new password (min 6 chars)"
                            required
                            minlength="6"
                            oninput="checkPasswordStrength(this.value)">
                        <i class="fa-regular fa-eye" onclick="togglePassword('newPassword', this)"></i>
                    </div>
                    <div class="strength">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <div class="strength-text">Password strength: <span id="strengthLabel">Weak</span></div>
                </div>

                <div class="input-group">
                    <label>Confirm Password</label>
                    <div class="input-box">
                        <input
                            type="password"
                            name="confirm_password"
                            id="confirmPassword"
                            placeholder="Confirm new password"
                            required
                            oninput="checkPasswordMatch()">
                        <i class="fa-regular fa-eye" onclick="togglePassword('confirmPassword', this)"></i>
                    </div>
                    <div style="font-size: 10px; color: var(--muted); margin-top: 4px;" id="matchStatus"></div>
                </div>

                <button type="submit" name="reset_password" class="btn" id="submitBtn">
                    <i class="fa-solid fa-check"></i> Reset Password
                </button>
            </form>

            <div class="divider">
                <span>or</span>
            </div>

            <form action="forgot_password.php" method="GET">
                <button type="submit" class="btn btn-secondary">
                    <i class="fa-solid fa-rotate-left"></i> Start Over
                </button>
            </form>

            <div class="back-login">
                <a href="login.php">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Login
                </a>
            </div>

        <!-- STEP 3: Success -->
        <?php elseif($step === 'success'): ?>
            <div class="top-icon" style="background: rgba(117,255,67,0.15);">
                <i class="fa-solid fa-check-circle"></i>
            </div>

            <h1>Password Reset Success!</h1>
            <div class="subtitle">
                Your password has been successfully reset.
                <br>You can now login with your new password.
            </div>

            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i>
                Password berhasil direset! Silakan login dengan password baru.
            </div>

            <a href="login.php" class="btn" style="text-decoration: none; text-align: center; display: block;">
                <i class="fa-solid fa-arrow-right"></i> Go to Login
            </a>

            <div class="back-login">
                <a href="login.php">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Login
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Check password strength
        function checkPasswordStrength(password) {
            const fill = document.getElementById('strengthFill');
            const label = document.getElementById('strengthLabel');
            
            let strength = 0;
            
            if (password.length >= 6) strength += 1;
            if (password.length >= 10) strength += 1;
            if (/[a-z]/.test(password)) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
            
            let percentage = 0;
            let text = 'Weak';
            let color = '#ff4d4d';
            
            if (strength <= 2) {
                percentage = 20;
                text = 'Weak';
                color = '#ff4d4d';
            } else if (strength <= 4) {
                percentage = 50;
                text = 'Medium';
                color = '#ffa500';
            } else if (strength <= 6) {
                percentage = 80;
                text = 'Strong';
                color = '#75ff43';
            } else {
                percentage = 100;
                text = 'Very Strong';
                color = '#00ff88';
            }
            
            fill.style.width = percentage + '%';
            fill.style.background = color;
            label.textContent = text;
            label.style.color = color;
            
            checkPasswordMatch();
        }

        // Check password match
        function checkPasswordMatch() {
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;
            const status = document.getElementById('matchStatus');
            const submitBtn = document.getElementById('submitBtn');
            
            if (confirmPass.length === 0) {
                status.textContent = '';
                submitBtn.disabled = false;
                return;
            }
            
            if (newPass === confirmPass) {
                status.textContent = '✅ Passwords match!';
                status.style.color = '#75ff43';
                submitBtn.disabled = false;
            } else {
                status.textContent = '❌ Passwords do not match!';
                status.style.color = '#ff4d4d';
                submitBtn.disabled = true;
            }
        }

        // Form validation before submit
        document.querySelector('form').addEventListener('submit', function(e) {
            if (this.querySelector('input[name="reset_password"]')) {
                const newPass = document.getElementById('newPassword').value;
                const confirmPass = document.getElementById('confirmPassword').value;
                
                if (newPass.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters!');
                    return false;
                }
                
                if (newPass !== confirmPass) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    return false;
                }
            }
            return true;
        });
    </script>
</body>
</html>