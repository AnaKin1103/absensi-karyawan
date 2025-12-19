<?php
session_start();
include('config/koneksi.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    
    $query = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        
        if ($password === $data['password']) {
            $_SESSION['id_user']  = $data['id_user'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['nama']     = $data['nama'];
            $_SESSION['role']     = $data['role'];
            
            // Redirect berdasarkan role
            if ($data['role'] == 'admin') {
                header("Location: pages/dashboard.php");
            } else {
                header("Location: pages/karyawan_dashboard.php");
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a3a52 0%, #2c5f7f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-wrapper {
            width: 140px;
            height: 140px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #1a3a52 0%, #2c5f7f 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        
        .login-logo {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .login-header h2 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.8rem;
        }
        
        .login-header p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #1a3a52;
            box-shadow: 0 0 0 3px rgba(26, 58, 82, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1a3a52 0%, #2c5f7f 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 58, 82, 0.4);
        }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .logo-wrapper {
                width: 120px;
                height: 120px;
            }
            
            .login-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-wrapper">
                <img src="uploads/logoIDS.png" alt="Logo IDS" class="login-logo">
            </div>
            <h2>Login Sistem Absensi</h2>
            <p>Masukkan username dan password Anda</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert">
                <span style="font-size: 1.2rem;">⚠️</span>
                <span><?= $error ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn-login">Masuk</button>
        </form>
    </div>
</body>
</html>