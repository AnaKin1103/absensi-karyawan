<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include('../config/koneksi.php');

// Proteksi karyawan
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'karyawan') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil data user
$query = mysqli_query($koneksi, "SELECT u.*, k.nama_lengkap, k.nip, k.email, k.no_tlp 
                                  FROM user u 
                                  LEFT JOIN karyawan k ON u.id_user = k.id_user 
                                  WHERE u.id_user='$id_user'");

if (mysqli_num_rows($query) == 0) {
    header("Location: ../login.php");
    exit;
}

$data = mysqli_fetch_assoc($query);
$display_name = !empty($data['nama_lengkap']) ? $data['nama_lengkap'] : $data['nama'];

// Proses submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password_lama    = $_POST['password_lama'];
    $password_baru    = $_POST['password_baru'];
    $password_konfirm = $_POST['password_konfirm'];
    
    // Handle foto profil
    $foto_profil = $data['foto_profil'];
    
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        $allowed  = ['jpg','jpeg','png','gif','webp'];
        $filename = $_FILES['foto_profil']['name'];
        $filesize = $_FILES['foto_profil']['size'];
        $tmp_name = $_FILES['foto_profil']['tmp_name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $_SESSION['error'] = "Format foto tidak diizinkan! (hanya jpg, jpeg, png, gif, webp)";
        } elseif ($filesize > 2 * 1024 * 1024) {
            $_SESSION['error'] = "Ukuran foto maksimal 2MB!";
        } else {
            // Hapus foto lama jika ada dan bukan default
            if (!empty($foto_profil) && $foto_profil != 'default-profile.png' && file_exists("../uploads/" . $foto_profil)) {
                unlink("../uploads/" . $foto_profil);
            }
            
            // Generate nama baru
            $new_name = 'profil_' . $id_user . '_' . time() . '.' . $ext;
            $dest     = "../uploads/" . $new_name;
            
            if (move_uploaded_file($tmp_name, $dest)) {
                $foto_profil = $new_name;
            } else {
                $_SESSION['error'] = "Gagal mengupload foto profil!";
            }
        }
    }
    
    // Jika belum ada error dari foto
    if (!isset($_SESSION['error'])) {
        // Update password jika diisi
        $update_pass = '';
        
        if (!empty($password_baru)) {
            // Validasi password lama
            if ($password_lama !== $data['password']) {
                $_SESSION['error'] = "Password lama tidak sesuai!";
            } elseif ($password_baru !== $password_konfirm) {
                $_SESSION['error'] = "Konfirmasi password baru tidak sama!";
            } elseif (strlen($password_baru) < 6) {
                $_SESSION['error'] = "Password baru minimal 6 karakter!";
            } else {
                $update_pass = ", password='$password_baru'";
            }
        }
        
        if (!isset($_SESSION['error'])) {
            // HANYA update foto_profil dan password (jika ada)
            $sql = "UPDATE user SET 
                        foto_profil='$foto_profil'
                        $update_pass
                    WHERE id_user='$id_user'";
            
            if (mysqli_query($koneksi, $sql)) {
                $_SESSION['success'] = "Profil berhasil diperbarui!";
                header("Location: karyawan_dashboard.php?page=profil");
                exit;
            } else {
                $_SESSION['error'] = "Gagal mengupdate profil!";
            }
        }
    }
    
    // Redirect untuk menampilkan pesan
    header("Location: karyawan_dashboard.php?page=profil");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/profil.css">
</head>
<body>
    <!-- Alert Messages -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <span style="font-size: 1.5rem;">✅</span>
            <span><?= $_SESSION['success'] ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <span style="font-size: 1.5rem;">❌</span>
            <span><?= $_SESSION['error'] ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <!-- Header Section -->
    <div class="page-header">
        <div class="header-content">
            <h3>Profil Saya</h3>
            <p class="subtitle"><?= date('l, d F Y') ?></p>
        </div>
    </div>
    
    <!-- Profile Layout -->
    <div class="profile-layout">
        <!-- Kartu Profil Kiri -->
        <div class="profile-card">
            <div class="profile-photo">
                <img src="../uploads/<?= htmlspecialchars($data['foto_profil'] ?: 'default-profile.png') ?>" alt="Foto Profil">
                <h3><?= htmlspecialchars($display_name) ?></h3>
                <p>@<?= htmlspecialchars($data['username']) ?></p>
                <span class="profile-badge">Karyawan</span>
            </div>
            
            <div class="profile-stats">
                <div class="item">
                    <span class="label">ID Pengguna</span>
                    <span class="value">#<?= $data['id_user'] ?></span>
                </div>
                
                <?php if(!empty($data['nip'])): ?>
                <div class="item">
                    <span class="label">NIP</span>
                    <span class="value"><?= htmlspecialchars($data['nip']) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($data['email'])): ?>
                <div class="item">
                    <span class="label">Email</span>
                    <span class="value"><?= htmlspecialchars($data['email']) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($data['no_tlp'])): ?>
                <div class="item">
                    <span class="label">No. Telepon</span>
                    <span class="value"><?= htmlspecialchars($data['no_tlp']) ?></span>
                </div>
                <?php endif; ?>
                
                <div class="item">
                    <span class="label">Role</span>
                    <span class="value"><?= ucfirst($data['role']) ?></span>
                </div>
                
                <div class="item">
                    <span class="label">Terdaftar Sejak</span>
                    <span class="value">
                        <?php
                        if (!empty($data['tgl_register']) && strtotime($data['tgl_register']) !== false) {
                            echo date('d M Y', strtotime($data['tgl_register']));
                        } else {
                            echo '-';
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Form Profil Kanan -->
        <div class="profile-form-card">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-section-title">Data Akun</div>
                
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" class="form-control" 
                           value="<?= htmlspecialchars($display_name) ?>" readonly>
                    <div class="form-text">Nama tidak dapat diubah. Hubungi admin jika ada perubahan data.</div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?= htmlspecialchars($data['username']) ?>" readonly>
                    <div class="form-text">Username tidak dapat diubah.</div>
                </div>
                
                <div class="form-group">
                    <label for="foto_profil">Foto Profil</label>
                    <input type="file" id="foto_profil" name="foto_profil" class="form-control" accept="image/*">
                    <div class="form-text">
                        Format: jpg, jpeg, png, gif, webp. Maksimal 2MB.
                    </div>
                </div>
                
                <hr style="margin: 25px 0; border-top: 1px dashed #e0e0e0;">
                
                <div class="form-section-title">Ubah Password (Opsional)</div>
                
                <div class="form-group">
                    <label for="password_lama">Password Lama</label>
                    <input type="password" id="password_lama" name="password_lama" class="form-control" 
                           placeholder="Isi jika ingin mengubah password">
                </div>
                
                <div class="form-group">
                    <label for="password_baru">Password Baru</label>
                    <input type="password" id="password_baru" name="password_baru" class="form-control" minlength="6"
                           placeholder="Minimal 6 karakter">
                    <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
                </div>
                
                <div class="form-group">
                    <label for="password_konfirm">Konfirmasi Password Baru</label>
                    <input type="password" id="password_konfirm" name="password_konfirm" class="form-control" minlength="6"
                           placeholder="Ulangi password baru">
                </div>
                
                <div class="form-buttons">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="karyawan_dashboard.php" class="btn btn-outline">← Kembali ke Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>