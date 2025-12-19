<?php
session_start();
include('../config/koneksi.php');

// Proteksi karyawan
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'karyawan') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$nama = $_SESSION['nama'];
$username = $_SESSION['username'];

// Ambil data user untuk foto profil
$user_q = mysqli_query($koneksi, "SELECT nama, username, foto_profil FROM user WHERE id_user='$id_user'");
$user_info = mysqli_fetch_assoc($user_q);
$foto_profil = !empty($user_info['foto_profil']) ? $user_info['foto_profil'] : 'default-profile.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Karyawan - <?= htmlspecialchars($nama) ?></title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/dashboard_karyawan.css">
</head>
<body>
    <div class="layout">
        <!-- Hamburger Menu -->
        <button class="hamburger" id="hamburgerBtn" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <!-- Overlay -->
        <div class="overlay" id="overlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-title">
                <div class="logo-wrapper-admin">
                    <img src="../uploads/logoIDS.png" alt="Logo IDS">
                </div>
                <h3>Dashboard Karyawan</h3>
            </div>
            
            <div class="profile-section">
                <div class="profile-avatar">
                    <img src="../uploads/<?= htmlspecialchars($foto_profil) ?>" alt="Foto Profil">
                </div>
                <div class="profile-name">
                    <?= htmlspecialchars($user_info['nama']) ?>
                </div>
            </div>
            
            <nav>
                <a href="?page=dashboard" class="sidebar-link <?= !isset($_GET['page']) || $_GET['page'] == 'dashboard' ? 'active' : '' ?>">
                Dashboard
                </a>
                
                <a href="?page=riwayat" class="sidebar-link <?= isset($_GET['page']) && $_GET['page'] == 'riwayat' ? 'active' : '' ?>">
                Riwayat Absensi
                </a>
                
                <a href="?page=profil" class="sidebar-link <?= isset($_GET['page']) && $_GET['page'] == 'profil' ? 'active' : '' ?>">
                Profil Saya
                </a>
                
                <a href="../logout.php" class="sidebar-link logout">
                Keluar
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="content" id="mainContent">
            <?php
            $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
            
            switch($page) {
                case 'riwayat':
                    include('content_riwayat_karyawan.php');
                    break;
                case 'profil':
                    include('profil.php');
                    break;
                default:
                    include('content_dashboard_karyawan.php');
                    break;
            }
            ?>
        </main>
    </div>
    
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/clock.js"></script>
</body>
</html>