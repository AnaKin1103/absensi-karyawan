<?php
session_start();
include('../config/koneksi.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Data user
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    
    // Data detail karyawan
    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $no_tlp = mysqli_real_escape_string($koneksi, $_POST['no_tlp']);
    $tgl_lahir = mysqli_real_escape_string($koneksi, $_POST['tgl_lahir']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    
    // Foto profil default
    $default_foto = 'default.png';
    
    // Check username exists
    $check_username = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username'");
    
    // Check NIP exists
    $check_nip = mysqli_query($koneksi, "SELECT * FROM karyawan WHERE nip='$nip'");
    
    // Check email exists (jika diisi)
    $check_email = null;
    if (!empty($email)) {
        $check_email = mysqli_query($koneksi, "SELECT * FROM karyawan WHERE email='$email'");
    }
    
    if (mysqli_num_rows($check_username) > 0) {
        $error = "Username sudah digunakan!";
    } elseif (mysqli_num_rows($check_nip) > 0) {
        $error = "NIP sudah digunakan!";
    } elseif (!empty($email) && mysqli_num_rows($check_email) > 0) {
        $error = "Email sudah digunakan!";
    } else {
        // Start transaction
        mysqli_begin_transaction($koneksi);
        
        try {
            // Insert ke tabel user
            $insert_user = "INSERT INTO user (nama, username, password, role, foto_profil) 
                           VALUES ('$nama', '$username', '$password', 'karyawan', '$default_foto')";
            
            if (!mysqli_query($koneksi, $insert_user)) {
                throw new Exception("Gagal menambahkan data user");
            }
            
            // Get ID user yang baru ditambahkan
            $id_user = mysqli_insert_id($koneksi);
            
            // Insert ke tabel karyawan
            $insert_karyawan = "INSERT INTO karyawan 
                (nip, id_user, nama_lengkap, jenis_kelamin, email, no_tlp, tgl_lahir, alamat) 
                VALUES 
                ('$nip', '$id_user', '$nama_lengkap', '$jenis_kelamin', '$email', '$no_tlp', '$tgl_lahir', '$alamat')";
            
            if (!mysqli_query($koneksi, $insert_karyawan)) {
                throw new Exception("Gagal menambahkan data detail karyawan");
            }
            
            // Commit transaction
            mysqli_commit($koneksi);
            $success = "Karyawan berhasil ditambahkan!";
            header("refresh:2;url=dashboard.php?page=karyawan");
            
        } catch (Exception $e) {
            // Rollback on error
            mysqli_rollback($koneksi);
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Karyawan</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a3a52 0%, #2c5f7f 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .form-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .form-card {
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .form-header {
            border-bottom: 3px solid #1a3a52;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 1.8rem;
        }
        
        .form-header p {
            color: #7f8c8d;
            margin: 0;
        }
        
        .section-title {
            color: #1a3a52;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 25px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .required {
            color: #e74c3c;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #1a3a52;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        select.form-control {
            cursor: pointer;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }
        
        .alert-danger {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 35px;
            padding-top: 25px;
            border-top: 2px solid #e0e0e0;
        }
        
        .form-actions .btn {
            flex: 1;
            padding: 14px;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1a3a52 0%, #2c5f7f 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        small {
            color: #7f8c8d;
            font-size: 0.85rem;
            display: block;
            margin-top: 5px;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            color: #1976d2;
        }
        
        @media (max-width: 768px) {
            .form-card {
                padding: 25px 20px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <h2>Tambah Karyawan Baru</h2>
                <p>Lengkapi informasi lengkap karyawan</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <span>❌</span>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <span>✅</span>
                    <span><?= $success ?></span>
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <strong>Info:</strong> Field yang bertanda <span class="required">*</span> wajib diisi
            </div>
            
            <form method="POST" action="">
                <!-- Data Login -->
                <div class="section-title">
                    <span>Data Login</span>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" class="form-control" required placeholder="Masukkan username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" class="form-control" required placeholder="Masukkan password" minlength="6">
                        <small>Minimal 6 karakter</small>
                    </div>
                </div>
                
                <!-- Data Pribadi -->
                <div class="section-title">
                    <span>Data Pribadi</span>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nip">NIP <span class="required">*</span></label>
                        <input type="text" id="nip" name="nip" class="form-control" required placeholder="Nomor Induk Pegawai">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="nama_lengkap">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" required placeholder="Nama lengkap sesuai KTP">
                    </div>
                    
                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin <span class="required">*</span></label>
                        <select id="jenis_kelamin" name="jenis_kelamin" class="form-control" required>
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="tgl_lahir">Tanggal Lahir</label>
                        <input type="date" id="tgl_lahir" name="tgl_lahir" class="form-control">
                    </div>
                </div>
                
                <!-- Kontak -->
                <div class="section-title">
                    <span>Informasi Kontak</span>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="no_tlp">No. Telepon</label>
                        <input type="tel" id="no_tlp" name="no_tlp" class="form-control">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="alamat">Alamat Lengkap</label>
                        <textarea id="alamat" name="alamat" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                
                <input type="hidden" id="nama" name="nama" value="">
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
                    <a href="dashboard.php?page=karyawan" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('nama').value = document.getElementById('nama_lengkap').value;
        });
    </script>
</body>
</html>