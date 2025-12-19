<?php
session_start();
include('../config/koneksi.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id_user = isset($_GET['id_user']) ? intval($_GET['id_user']) : 0;

// Check if karyawan exists
$check = mysqli_query($koneksi, "SELECT * FROM user WHERE id_user = '$id_user' AND role='karyawan'");

if (mysqli_num_rows($check) > 0) {
    // Delete karyawan (cascade akan menghapus absensi terkait)
    $delete = mysqli_query($koneksi, "DELETE FROM user WHERE id_user='$id_user'");
    
    if ($delete) {
        echo "<script>alert('Karyawan berhasil dihapus!'); window.location='dashboard.php?page=karyawan';</script>";
    } else {
        echo "<script>alert('Gagal menghapus karyawan!'); window.location='dashboard.php?page=karyawan';</script>";
    }
} else {
    echo "<script>alert('Karyawan tidak ditemukan!'); window.location='dashboard.php?page=karyawan';</script>";
}
?>