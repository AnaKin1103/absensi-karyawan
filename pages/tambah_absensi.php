<?php
session_start();
include('../config/koneksi.php');
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
$error = '';
$success = '';

// PERBAIKAN: Get list karyawan dengan JOIN untuk menampilkan nama lengkap
$karyawan_list = mysqli_query($koneksi, "SELECT u.id_user, u.nama, u.username, k.nama_lengkap, k.nip 
                                         FROM user u 
                                         LEFT JOIN karyawan k ON u.id_user = k.id_user 
                                         WHERE u.role='karyawan' 
                                         ORDER BY u.nama ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user = intval($_POST['id_user']);
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $jam_masuk = mysqli_real_escape_string($koneksi, $_POST['jam_masuk']);
    $jam_keluar = !empty($_POST['jam_keluar']) ? mysqli_real_escape_string($koneksi, $_POST['jam_keluar']) : NULL;
    $keterangan = isset($_POST['keterangan']) ? mysqli_real_escape_string($koneksi, $_POST['keterangan']) : '';
    
    // Validasi
    if(empty($id_user) || empty($tanggal) || empty($jam_masuk)) {
        $error = "Karyawan, tanggal, dan jam masuk wajib diisi!";
    } else {
        // Check if already exists
        $check = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_user='$id_user' AND tanggal='$tanggal'");
        
        if (mysqli_num_rows($check) > 0) {
            $error = "Absensi untuk karyawan ini pada tanggal tersebut sudah ada!";
        } else {
            // PERBAIKAN: INSERT TANPA menyimpan nama (nama diambil dari relasi)
            $jam_keluar_value = $jam_keluar ? "'$jam_keluar'" : "NULL";
            $query = "INSERT INTO absensi (id_user, tanggal, jam_masuk, jam_keluar, keterangan) 
                      VALUES ('$id_user', '$tanggal', '$jam_masuk', $jam_keluar_value, '$keterangan')";
            
            if (mysqli_query($koneksi, $query)) {
                $success = "Data absensi berhasil ditambahkan!";
                header("refresh:2;url=dashboard.php?page=absensi");
            } else {
                $error = "Gagal menambahkan data absensi: " . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Absensi</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a3a52 0%, #2c5f7f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .form-container {
            max-width: 700px;
            width: 100%;
        }
        
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .form-card h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .form-card > p {
            color: #7f8c8d;
            margin-bottom: 25px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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
        
        .required {
            color: red;
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
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        
        .info-text {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }
        
        .form-actions .btn {
            flex: 1;
            padding: 12px;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1a3a52 0%, #2c5f7f 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        @media (max-width: 768px) {
            .form-card {
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-card">
            <h2>Tambah Data Absensi</h2>
            <p>Lengkapi form di bawah untuk menambahkan data absensi</p>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="id_user">Karyawan <span class="required">*</span></label>
                    <select id="id_user" name="id_user" class="form-control" required>
                        <option value="">-- Pilih Karyawan --</option>
                        <?php while($karyawan = mysqli_fetch_assoc($karyawan_list)): ?>
                            <option value="<?= $karyawan['id_user'] ?>">
                                <?= htmlspecialchars($karyawan['nama_lengkap'] ?? $karyawan['nama']) ?> 
                                (<?= htmlspecialchars($karyawan['nip'] ?? $karyawan['username']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tanggal">Tanggal <span class="required">*</span></label>
                    <input type="date" 
                           id="tanggal" 
                           name="tanggal" 
                           class="form-control" 
                           required 
                           value="<?= date('Y-m-d') ?>"
                           max="<?= date('Y-m-d') ?>">
                    <p class="info-text">* Tanggal tidak boleh lebih dari hari ini</p>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="jam_masuk">Jam Masuk <span class="required">*</span></label>
                        <input type="time" id="jam_masuk" name="jam_masuk" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="jam_keluar">Jam Keluar</label>
                        <input type="time" id="jam_keluar" name="jam_keluar" class="form-control">
                        <p class="info-text">* Kosongkan jika belum pulang</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea id="keterangan" 
                              name="keterangan" 
                              class="form-control" 
                              placeholder="Tambahkan keterangan jika diperlukan (opsional)"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="dashboard.php?page=absensi" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>