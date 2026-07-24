<?php
session_start();

// Cek login - sesuai dengan login.php
if(!isset($_SESSION['login']) || $_SESSION['login'] !== true){
    header("Location: login.php");
    exit;
}

$activePage = 'profile';
require_once __DIR__ . '/../config/config.php';

// Ambil data user dari session
$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];
$fullname = $_SESSION['username']; // Ini dari session login

// Ambil data lengkap dari database
$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$data = mysqli_fetch_assoc($query);

if(!$data){
    // Jika user tidak ditemukan, logout
    session_destroy();
    header("Location: login.php");
    exit;
}

$fullname = $data['fullname'] ?? $fullname;
$email = $data['email'] ?? $email;
$profile_photo = $data['profile_photo'] ?? '';
$role = $data['role'] ?? 'user';

// Proses Update Profile (update fullname dan email)
if(isset($_POST['update_profile'])){
    $fullname = trim($_POST['fullname']);
    $new_email = trim($_POST['email']);
    
    $errors = [];
    
    // Validasi
    if(empty($fullname)){
        $errors[] = "Full name tidak boleh kosong!";
    }
    
    if(empty($new_email)){
        $errors[] = "Email tidak boleh kosong!";
    } elseif(!filter_var($new_email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Format email tidak valid!";
    } else {
        // Cek apakah email sudah digunakan oleh user lain
        $check_email = mysqli_real_escape_string($conn, $new_email);
        $check_query = "SELECT id FROM users WHERE email='$check_email' AND id != '$user_id'";
        $check_result = mysqli_query($conn, $check_query);
        
        if(mysqli_num_rows($check_result) > 0){
            $errors[] = "Email sudah digunakan oleh user lain!";
        }
    }
    
    if(empty($errors)){
        $fullname = mysqli_real_escape_string($conn, $fullname);
        $new_email = mysqli_real_escape_string($conn, $new_email);
        
        // Update database
        $update_query = "UPDATE users SET fullname='$fullname', email='$new_email' WHERE id='$user_id'";
        
        if(mysqli_query($conn, $update_query)){
            // Update session dengan data baru
            $_SESSION['username'] = $fullname;
            $_SESSION['email'] = $new_email;
            
            $_SESSION['success_message'] = "Profile berhasil diupdate!";
            header("Location: profile.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Gagal update: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_message'] = implode(" ", $errors);
    }
    header("Location: profile.php");
    exit;
}

// Proses Upload Foto
if(isset($_POST['upload_photo'])){
    if(isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0){
        $file = $_FILES['profile_photo'];
        
        // Validasi
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $max_size = 5 * 1024 * 1024;
        
        if(!in_array($file['type'], $allowed_types)){
            $_SESSION['error_message'] = "Format file tidak didukung. Gunakan JPG, PNG, atau GIF.";
        } elseif($file['size'] > $max_size){
            $_SESSION['error_message'] = "Ukuran file terlalu besar. Maksimal 5MB.";
        } else {
            // Buat folder upload
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/profile/';
            if(!file_exists($upload_dir)){
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate nama file
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = md5($email . time() . rand(1000, 9999)) . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            // Pindahkan file
            if(move_uploaded_file($file['tmp_name'], $filepath)){
                $relative_path = '/uploads/profile/' . $filename;
                $update_photo = "UPDATE users SET profile_photo='$relative_path' WHERE id='$user_id'";
                
                if(mysqli_query($conn, $update_photo)){
                    $_SESSION['profile_photo'] = $relative_path;
                    $_SESSION['success_message'] = "Foto profile berhasil diupload!";
                } else {
                    $_SESSION['error_message'] = "Gagal update database: " . mysqli_error($conn);
                }
            } else {
                $_SESSION['error_message'] = "Gagal mengupload file.";
            }
        }
    } else {
        $_SESSION['error_message'] = "Tidak ada file yang dipilih.";
    }
    header("Location: profile.php");
    exit;
}

// Tampilkan pesan
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - IoT Coffee Sorter</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        :root { --bg: #071207; --sidebar: #081808; --card: #0d1d0e; --primary: #75ff43; --text: #ffffff; --muted: #8fa08f; --border: rgba(255,255,255,0.05); }
        body { background: var(--bg); color: var(--text); min-height: 100vh; }
        .dashboard { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: var(--sidebar); border-right: 1px solid var(--border); padding: 24px 16px; position: sticky; top: 0; height: 100vh; }
        .logo { display: flex; align-items: center; gap: 14px; margin-bottom: 40px; padding-bottom: 24px; border-bottom: 1px solid var(--border); }
        .logo-icon { width: 44px; height: 44px; border-radius: 12px; background: rgba(117,255,67,0.12); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 20px; }
        .logo-text h2 { font-size: 18px; font-weight: 700; }
        .logo-text span { color: var(--muted); font-size: 12px; }
        .menu { display: flex; flex-direction: column; gap: 4px; }
        .menu a { display: flex; align-items: center; gap: 14px; padding: 12px 16px; border-radius: 12px; color: var(--muted); text-decoration: none; font-size: 14px; font-weight: 500; transition: 0.3s; }
        .menu a:hover { background: rgba(117,255,67,0.06); color: var(--text); }
        .menu a.active { background: rgba(117,255,67,0.12); color: var(--primary); }
        .menu .logout { margin-top: 20px; border-top: 1px solid var(--border); padding-top: 20px; color: #ff6b6b; }
        .main { flex: 1; padding: 24px 30px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
        .topbar h1 { font-size: 28px; font-weight: 700; }
        .topbar p { color: var(--muted); margin-top: 4px; font-size: 14px; }
        .user-info { display: flex; align-items: center; gap: 12px; background: var(--card); padding: 8px 16px 8px 20px; border-radius: 30px; border: 1px solid var(--border); }
        .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: rgba(117,255,67,0.15); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
        .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .user-name { font-size: 14px; font-weight: 500; }
        .user-email { font-size: 11px; color: var(--muted); }
        .alert { padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: rgba(117,255,67,0.12); border: 1px solid rgba(117,255,67,0.2); color: var(--primary); }
        .alert-error { background: rgba(255,68,68,0.12); border: 1px solid rgba(255,68,68,0.2); color: #ff4d4d; }
        .profile-card { background: var(--card); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; }
        .card-header { height: 58px; border-bottom: 1px solid rgba(117,255,67,0.06); display: flex; align-items: center; gap: 12px; padding: 0 24px; background: linear-gradient(90deg, rgba(117,255,67,0.05), transparent); }
        .card-header i { color: var(--primary); }
        .card-header span { font-size: 13px; font-weight: 600; letter-spacing: 1px; }
        .card-content { display: flex; gap: 30px; padding: 32px; flex-wrap: wrap; }
        .picture-box { width: 280px; border: 1px solid rgba(117,255,67,0.06); border-radius: 18px; padding: 24px; display: flex; flex-direction: column; align-items: center; }
        .picture-box h4 { font-size: 12px; letter-spacing: 1px; color: #93a193; margin-bottom: 24px; }
        .avatar-container { width: 120px; height: 120px; border-radius: 50%; overflow: hidden; border: 3px solid rgba(117,255,67,0.15); background: #2a2a2a; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .avatar-container img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-placeholder { width: 120px; height: 120px; border-radius: 50%; background: #2a2a2a; position: relative; overflow: hidden; border: 3px solid rgba(117,255,67,0.15); }
        .avatar-placeholder .head { width: 42px; height: 42px; border-radius: 50%; background: #ececec; position: absolute; top: 22px; left: 50%; transform: translateX(-50%); }
        .avatar-placeholder .body { width: 100px; height: 100px; border-radius: 50%; background: #ececec; position: absolute; bottom: -48px; left: 50%; transform: translateX(-50%); }
        .upload-form { margin-top: 22px; width: 100%; }
        .upload-form input[type="file"] { display: none; }
        .upload-btn { width: 100%; padding: 10px; background: rgba(117,255,67,0.1); border: 1px solid rgba(117,255,67,0.2); border-radius: 10px; color: var(--primary); font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .upload-btn:hover { background: rgba(117,255,67,0.2); transform: translateY(-2px); }
        .profile-form { flex: 1; min-width: 300px; }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; margin-bottom: 10px; color: #93a193; font-size: 12px; letter-spacing: 1px; }
        .form-group input { width: 100%; height: 56px; border-radius: 14px; border: 1px solid rgba(117,255,67,0.10); background: rgba(255,255,255,0.02); padding: 0 18px; color: white; font-size: 15px; outline: none; transition: 0.3s; }
        .form-group input:focus { border-color: var(--primary); box-shadow: 0 0 18px rgba(117,255,67,0.08); }
        .form-group input[readonly] { 
            opacity: 0.7; 
            cursor: not-allowed;
            background: rgba(255,255,255,0.05);
            border-color: rgba(255,255,255,0.05);
        }
        .action-btn { display: flex; justify-content: flex-end; gap: 14px; margin-top: 30px; }
        .btn-cancel { min-width: 120px; height: 48px; border: none; border-radius: 12px; background: rgba(255,255,255,0.06); color: white; font-weight: 600; cursor: pointer; }
        .btn-confirm { min-width: 140px; height: 48px; border: none; border-radius: 12px; background: var(--primary); color: #071207; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 0 24px rgba(117,255,67,0.20); }
        .role-badge {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            background: rgba(117,255,67,0.1);
            color: var(--primary);
            margin-left: 8px;
        }
        
        @media (max-width: 768px) {
            .dashboard { flex-direction: column; }
            .sidebar { width: 100%; height: auto; position: relative; }
            .main { padding: 16px; }
            .topbar { flex-direction: column; align-items: flex-start; }
            .card-content { flex-direction: column; }
            .picture-box { width: 100%; }
            .action-btn { flex-direction: column; }
            .btn-cancel, .btn-confirm { width: 100%; }
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <div class="logo-icon"><i class="fa-solid fa-mug-hot"></i></div>
                <div class="logo-text">
                    <h2>IoT Coffee</h2>
                    <span>Control Center</span>
                </div>
            </div>
            <div class="menu">
                <a href="profile.php" class="active"><i class="fa-regular fa-user"></i> Profile</a>
                <a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
                <a href="setting.php"><i class="fa-solid fa-gear"></i> Settings</a>
                <a href="logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main">
            <div class="topbar">
                <div>
                    <h1>Profile</h1>
                    <p>Manage your account information and preferences.</p>
                </div>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php if(!empty($profile_photo) && file_exists($_SERVER['DOCUMENT_ROOT'] . $profile_photo)): ?>
                            <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile">
                        <?php else: ?>
                            <i class="fa-regular fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="user-name">
                            <?php echo htmlspecialchars($fullname); ?>
                            <span class="role-badge"><?php echo htmlspecialchars($role); ?></span>
                        </div>
                        <div class="user-email"><?php echo htmlspecialchars($email); ?></div>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if($success_message): ?>
                <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if($error_message): ?>
                <div class="alert alert-error"><i class="fa-solid fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <!-- Profile Card -->
            <div class="profile-card">
                <div class="card-header">
                    <i class="fa-regular fa-user"></i>
                    <span>USER PROFILE SETTINGS</span>
                </div>

                <div class="card-content">
                    <!-- Profile Picture -->
                    <div class="picture-box">
                        <h4>PROFILE PICTURE</h4>
                        <div class="avatar-container">
                            <?php if(!empty($profile_photo) && file_exists($_SERVER['DOCUMENT_ROOT'] . $profile_photo)): ?>
                                <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Photo">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <div class="head"></div>
                                    <div class="body"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Upload Form -->
                        <form class="upload-form" action="" method="POST" enctype="multipart/form-data">
                            <input type="file" name="profile_photo" id="profilePhoto" accept="image/*">
                            <button type="button" class="upload-btn" onclick="document.getElementById('profilePhoto').click();">
                                <i class="fa-solid fa-upload"></i> Upload New Photo
                            </button>
                            <button type="submit" name="upload_photo" style="display:none;" id="submitPhoto">Submit</button>
                        </form>
                    </div>

                    <!-- Profile Form -->
                    <form class="profile-form" action="" method="POST">
                        <div class="form-group">
                            <label>FULL NAME</label>
                            <input type="text" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>EMAIL ADDRESS</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>

                        <div class="action-btn">
                            <button type="reset" class="btn-cancel"><i class="fa-solid fa-rotate-left"></i> Batal</button>
                            <button type="submit" name="update_profile" class="btn-confirm"><i class="fa-solid fa-check"></i> Konfirmasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto submit file upload
        document.getElementById('profilePhoto').addEventListener('change', function() {
            if(this.files && this.files[0]) {
                if(this.files[0].size > 5 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal 5MB.');
                    this.value = '';
                    return;
                }
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                if(!allowedTypes.includes(this.files[0].type)) {
                    alert('Format file tidak didukung! Gunakan JPG, PNG, atau GIF.');
                    this.value = '';
                    return;
                }
                document.getElementById('submitPhoto').click();
            }
        });
    </script>
</body>
</html>