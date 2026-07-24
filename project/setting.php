<?php
session_start();

// Cek apakah user sudah login
if(!isset($_SESSION['login']) || $_SESSION['login'] !== true){
    header("Location: login.php");
    exit;
}

$activePage = 'settings';

// Koneksi database untuk mengambil data user
require_once __DIR__ . '/../config/config.php';

// Ambil data user dari session
$fullname = $_SESSION['username'] ?? 'User';
$email = $_SESSION['email'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

// Proses Ganti Password
$success_message = '';
$error_message = '';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])){
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasi
    if(empty($current_password) || empty($new_password) || empty($confirm_password)){
        $error_message = "Semua field harus diisi!";
    } elseif(strlen($new_password) < 6){
        $error_message = "Password baru minimal 6 karakter!";
    } elseif($new_password !== $confirm_password){
        $error_message = "Password baru dan konfirmasi password tidak cocok!";
    } else {
        // Ambil password dari database
        $query = mysqli_query($conn, "SELECT password FROM users WHERE id='$user_id'");
        $data = mysqli_fetch_assoc($query);
        
        if($data){
            // Verifikasi password saat ini
            if(password_verify($current_password, $data['password'])){
                // Hash password baru
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password di database
                $update_query = "UPDATE users SET password='$hashed_password' WHERE id='$user_id'";
                
                if(mysqli_query($conn, $update_query)){
                    $success_message = "Password berhasil diubah! Silakan gunakan password baru untuk login berikutnya.";
                    
                    // OPTIONAL: Update session jika ingin tetap login dengan password baru
                    // Tidak perlu logout, session tetap aktif
                } else {
                    $error_message = "Gagal mengubah password: " . mysqli_error($conn);
                }
            } else {
                $error_message = "Password saat ini salah!";
            }
        } else {
            $error_message = "User tidak ditemukan!";
        }
    }
}

// Tampilkan pesan dari session (jika ada)
if(isset($_SESSION['success_message'])){
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if(isset($_SESSION['error_message'])){
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Security Settings</title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
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
            --sidebar: #081808;
            --card: #0d1d0e;
            --primary: #75ff43;
            --text: #ffffff;
            --muted: #8fa08f;
            --border: rgba(255,255,255,0.05);
        }

        body {
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 260px;
            background: var(--sidebar);
            border-right: 1px solid var(--border);
            padding: 24px 16px;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 40px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border);
        }

        .logo-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(117,255,67,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 20px;
        }

        .logo-text h2 {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
        }

        .logo-text span {
            color: var(--muted);
            font-size: 12px;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            border-radius: 12px;
            color: var(--muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: 0.3s;
        }

        .menu a i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .menu a:hover {
            background: rgba(117,255,67,0.06);
            color: var(--text);
        }

        .menu a.active {
            background: rgba(117,255,67,0.12);
            color: var(--primary);
        }

        .menu a.active i {
            color: var(--primary);
        }

        .menu .logout {
            margin-top: 20px;
            border-top: 1px solid var(--border);
            padding-top: 20px;
            color: #ff6b6b;
        }

        .menu .logout:hover {
            background: rgba(255,68,68,0.08);
            color: #ff6b6b;
        }

        /* ===== MAIN CONTENT ===== */
        .main {
            flex: 1;
            min-width: 0;
            padding: 24px 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .topbar h1 {
            font-size: 28px;
            font-weight: 700;
        }

        .topbar p {
            color: var(--muted);
            margin-top: 4px;
            font-size: 14px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--card);
            padding: 8px 16px 8px 20px;
            border-radius: 30px;
            border: 1px solid var(--border);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(117,255,67,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 16px;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-name {
            font-size: 14px;
            font-weight: 500;
        }

        .user-email {
            font-size: 11px;
            color: var(--muted);
        }

        /* ===== ALERT MESSAGES ===== */
        .alert {
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .alert-success {
            background: rgba(117,255,67,0.12);
            border: 1px solid rgba(117,255,67,0.2);
            color: var(--primary);
        }

        .alert-error {
            background: rgba(255,68,68,0.12);
            border: 1px solid rgba(255,68,68,0.2);
            color: #ff4d4d;
        }

        .alert .alert-close {
            margin-left: auto;
            cursor: pointer;
            opacity: 0.7;
            transition: 0.3s;
        }

        .alert .alert-close:hover {
            opacity: 1;
        }

        /* ===== SECURITY CARD ===== */
        .security-card {
            width: 100%;
            max-width: 420px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
        }

        .card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .card-title i {
            color: var(--primary);
        }

        .input-group {
            margin-bottom: 16px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #97a397;
            font-size: 11px;
        }

        .input-group input {
            width: 100%;
            height: 42px;
            border-radius: 10px;
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.04);
            padding: 0 14px;
            color: white;
            font-size: 12px;
            outline: none;
            transition: 0.3s;
        }

        .input-group input:focus {
            border-color: rgba(117,255,67,0.18);
        }

        .input-group .input-wrapper {
            position: relative;
        }

        .input-group .input-wrapper input {
            padding-right: 40px;
        }

        .input-group .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8b7f;
            cursor: pointer;
            font-size: 14px;
            transition: 0.3s;
        }

        .input-group .toggle-password:hover {
            color: var(--primary);
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

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 22px;
        }

        .btn {
            height: 40px;
            border: none;
            border-radius: 10px;
            padding: 0 16px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-save {
            background: var(--primary);
            color: black;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(117,255,67,0.2);
        }

        .btn-cancel {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.05);
            color: #98a398;
        }

        .btn-cancel:hover {
            background: rgba(255,255,255,0.05);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 16px;
            }

            .main {
                padding: 16px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .security-card {
                max-width: 100%;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .sidebar .logo {
                flex-direction: column;
                text-align: center;
            }

            .menu a {
                font-size: 13px;
                padding: 10px 14px;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <!-- ===== SIDEBAR ===== -->
        <div class="sidebar">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fa-solid fa-mug-hot"></i>
                </div>
                <div class="logo-text">
                    <h2>IoT Coffee</h2>
                    <span>Control Center</span>
                </div>
            </div>

            <div class="menu">
                <a href="profile.php">
                    <i class="fa-regular fa-user"></i> Profile
                </a>
                <a href="dashboard.php">
                    <i class="fa-solid fa-house"></i> Dashboard
                </a>
                <a href="setting.php" class="active">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>
                <a href="logout.php" class="logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>

        <!-- ===== MAIN CONTENT ===== -->
        <div class="main">
            <!-- Topbar -->
            <div class="topbar">
                <div>
                    <h1>Security Settings</h1>
                    <p>Protect your IoT sorting network by maintaining secure credentials.</p>
                </div>
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <div>
                        <div class="user-name"><?php echo htmlspecialchars($fullname); ?></div>
                        <div class="user-email"><?php echo htmlspecialchars($email); ?></div>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if($success_message): ?>
                <div class="alert alert-success" id="successAlert">
                    <i class="fa-solid fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                    <span class="alert-close" onclick="this.parentElement.style.display='none'">
                        <i class="fa-solid fa-xmark"></i>
                    </span>
                </div>
            <?php endif; ?>

            <?php if($error_message): ?>
                <div class="alert alert-error" id="errorAlert">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                    <span class="alert-close" onclick="this.parentElement.style.display='none'">
                        <i class="fa-solid fa-xmark"></i>
                    </span>
                </div>
            <?php endif; ?>

            <!-- Security Card -->
            <div class="security-card">
                <div class="card-title">
                    <i class="fa-solid fa-shield-halved"></i>
                    Change Password
                </div>

                <form action="" method="POST" id="passwordForm">
                    <div class="input-group">
                        <label>Current Password</label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                name="current_password" 
                                id="currentPassword" 
                                placeholder="Enter current password"
                                required
                            >
                            <i class="fa-regular fa-eye toggle-password" onclick="togglePassword('currentPassword', this)"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>New Password</label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                name="new_password" 
                                id="newPassword" 
                                placeholder="Enter new password (min 6 chars)"
                                required
                                oninput="checkPasswordStrength(this.value)"
                            >
                            <i class="fa-regular fa-eye toggle-password" onclick="togglePassword('newPassword', this)"></i>
                        </div>
                        <div class="strength">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text">Password strength: <span id="strengthLabel">Weak</span></div>
                    </div>

                    <div class="input-group">
                        <label>Confirm New Password</label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                name="confirm_password" 
                                id="confirmPassword" 
                                placeholder="Confirm new password"
                                required
                                oninput="checkPasswordMatch()"
                            >
                            <i class="fa-regular fa-eye toggle-password" onclick="togglePassword('confirmPassword', this)"></i>
                        </div>
                        <div style="font-size: 10px; color: var(--muted); margin-top: 4px;" id="matchStatus"></div>
                    </div>

                    <div class="button-group">
                        <button type="submit" name="change_password" class="btn btn-save" id="submitBtn">
                            <i class="fa-solid fa-key"></i> Update Password
                        </button>
                        <button type="reset" class="btn btn-cancel" onclick="resetForm()">
                            <i class="fa-solid fa-rotate-left"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
            
            // Length check
            if (password.length >= 6) strength += 1;
            if (password.length >= 10) strength += 1;
            
            // Character type checks
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
            
            // Check match
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

        // Reset form
        function resetForm() {
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
            document.getElementById('strengthFill').style.width = '0%';
            document.getElementById('strengthLabel').textContent = 'Weak';
            document.getElementById('strengthLabel').style.color = '#ff4d4d';
            document.getElementById('matchStatus').textContent = '';
            document.getElementById('submitBtn').disabled = false;
            
            // Reset input types to password
            document.querySelectorAll('.input-wrapper input').forEach(input => {
                input.type = 'password';
            });
            document.querySelectorAll('.toggle-password').forEach(icon => {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            });
        }

        // Form validation before submit
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const currentPass = document.getElementById('currentPassword').value;
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmPassword').value;
            
            if (currentPass.length < 1) {
                e.preventDefault();
                alert('Please enter your current password!');
                return false;
            }
            
            if (newPass.length < 6) {
                e.preventDefault();
                alert('New password must be at least 6 characters!');
                return false;
            }
            
            if (newPass !== confirmPass) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            return true;
        });

        // Auto hide alert after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500);
                }, 5000);
            });
        }, 1000);
    </script>
</body>
</html>