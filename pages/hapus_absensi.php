<?php
session_start();
include('../config/koneksi.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id_absensi = isset($_GET['id_absensi']) ? intval($_GET['id_absensi']) : 0;

// Check if absensi exists
$check = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_absensi='$id_absensi'");

if (mysqli_num_rows($check) > 0) {
    $data = mysqli_fetch_assoc($check);
    
    // Delete foto if exists
    if (!empty($data['foto']) && file_exists("../uploads/" . $data['foto'])) {
        unlink("../uploads/" . $data['foto']);
    }
    if (!empty($data['foto_keluar']) && file_exists("../uploads/" . $data['foto_keluar'])) {
        unlink("../uploads/" . $data['foto_keluar']);
    }
    
    // Delete absensi
    $delete = mysqli_query($koneksi, "DELETE FROM absensi WHERE id_absensi='$id_absensi'");
    
    if ($delete) {
        echo "<script>alert('Data absensi berhasil dihapus!'); window.location='dashboard.php?page=absensi';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data absensi!'); window.location='dashboard.php?page=absensi';</script>";
    }
} else {
    echo "<script>alert('Data absensi tidak ditemukan!'); window.location='dashboard.php?page=absensi';</script>";
}
?>
