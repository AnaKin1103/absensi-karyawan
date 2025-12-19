<?php
// Konfigurasi Database
$host = "localhost";
$user = "root";
$pass = ""; // Kosongkan jika tidak ada password
$db   = "absensi_karyawan";

// Koneksi ke Database
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek Koneksi
if (!$koneksi) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}

// Set charset UTF-8
mysqli_set_charset($koneksi, "utf8mb4");

// Set timezone Indonesia
date_default_timezone_set('Asia/Jakarta');
?>