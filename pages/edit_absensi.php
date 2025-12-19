<?php
session_start();
include('../config/koneksi.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = '';
$success = '';
$id_absensi = isset($_GET['id_absensi']) ? intval($_GET['id_absensi']) : 0;

// PERBAIKAN: Get data absensi dengan JOIN untuk menampilkan nama lengkap
$query = mysqli_query($koneksi, "SELECT a.*, 
                                         u.nama, 
                                         u.username, 
                                         k.nama_lengkap, 
                                         k.nip 
                                  FROM absensi a 
                                  LEFT JOIN user u ON a.id_user = u.id_user 
                                  LEFT JOIN karyawan k ON u.id_user = k.id_user 
                                  WHERE a.id_absensi='$id_absensi'");

if (mysqli_num_rows($query) == 0) {
    echo "<script>alert('Data tidak ditemukan'); window.location='dashboard.php?page=absensi';</script>";
    exit;
}

$data = mysqli_fetch_assoc($query);

// PERBAIKAN: Get list karyawan dengan JOIN
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
        // PERBAIKAN: Update TANPA menyimpan nama (nama diambil dari relasi)
        $jam_keluar_value = $jam_keluar ? "'$jam_keluar'" : "NULL";
        
        $update_query = "UPDATE absensi SET 
                         id_user='$id_user', 
                         tanggal='$tanggal', 
                         jam_masuk='$jam_masuk', 
                         jam_keluar=$jam_keluar_value,
                         keterangan='$keterangan' 
                         WHERE id_absensi='$id_absensi'";
        
        if (mysqli_query($koneksi, $update_query)) {
            $success = "Data absensi berhasil diupdate!";
            header("refresh:2;url=dashboard.php?page=absensi");
        } else {
            $error = "Gagal mengupdate data absensi: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Absensi</title>
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
            margin-bottom: 15px;
        }
        
        .info-box {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #2196f3;
        }
        
        .info-box strong {
            display: block;
            margin-bottom: 5px;
            color: #1976d2;
            font-size: 0.9em;
        }
        
        .info-box span {
            color: #333;
            font-size: 1.1em;
            font-weight: 600;
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
            border-color: #667eea;
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
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn-warning:hover {
            background: #e67e22;
            transform: translateY(-2px);
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
            <h2>Edit Data Absensi</h2>
            
            <div class="info-box">
                <strong>Data Karyawan Saat Ini:</strong>
                <span>
                    <?= htmlspecialchars($data['nama_lengkap'] ?? $data['nama']) ?> 
                    (<?= htmlspecialchars($data['nip'] ?? $data['username']) ?>)
                </span>
            </div>
            
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
                            <option value="<?= $karyawan['id_user'] ?>" 
                                    <?= $karyawan['id_user'] == $data['id_user'] ? 'selected' : '' ?>>
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
                           value="<?= $data['tanggal'] ?>"
                           max="<?= date('Y-m-d') ?>">
                    <p class="info-text">* Tanggal tidak boleh lebih dari hari ini</p>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="jam_masuk">Jam Masuk <span class="required">*</span></label>
                        <input type="time" 
                               id="jam_masuk" 
                               name="jam_masuk" 
                               class="form-control" 
                               required 
                               value="<?= $data['jam_masuk'] ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="jam_keluar">Jam Keluar</label>
                        <input type="time" 
                               id="jam_keluar" 
                               name="jam_keluar" 
                               class="form-control" 
                               value="<?= $data['jam_keluar'] ?>">
                        <p class="info-text">* Kosongkan jika belum pulang</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea id="keterangan" 
                              name="keterangan" 
                              class="form-control" 
                              placeholder="Tambahkan keterangan jika diperlukan (opsional)"><?= htmlspecialchars($data['keterangan'] ?? '') ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-warning">Update</button>
                    <a href="dashboard.php?page=absensi" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>