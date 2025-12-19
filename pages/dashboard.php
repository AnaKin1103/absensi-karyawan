<?php
session_start();
include('../config/koneksi.php');

// Proteksi admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Absensi</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
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
                <h3>Dashboard Admin</h3>
            </div>
            <nav>
                <a href="?page=dashboard" class="sidebar-link <?= !isset($_GET['page']) || $_GET['page'] == 'dashboard' ? 'active' : '' ?>">
                    Dashboard
                </a>
                <a href="?page=karyawan" class="sidebar-link <?= isset($_GET['page']) && $_GET['page'] == 'karyawan' ? 'active' : '' ?>">
                    Data Karyawan
                </a>
                <a href="?page=absensi" class="sidebar-link <?= isset($_GET['page']) && $_GET['page'] == 'absensi' ? 'active' : '' ?>">
                    Data Absensi
                </a>
                <a href="../logout.php" class="sidebar-link logout">
                    Keluar
                </a>
            </nav>
        </aside>

        <!-- Main content -->
        <main class="content" id="mainContent">
            <?php
            $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
            
            switch($page) {
                case 'karyawan':
                    include('content_karyawan.php');
                    break;
                case 'absensi':
                    include('content_absensi.php');
                    break;
                default:
                    include('content_dashboard.php');
                    break;
            }
            ?>
        </main>
    </div>

    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/clock.js"></script>
</body>
</html>