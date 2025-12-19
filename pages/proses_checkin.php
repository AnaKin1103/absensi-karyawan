<?php
session_start();
include('../config/koneksi.php');

// Proteksi karyawan
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'karyawan') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
$keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
$latitude = mysqli_real_escape_string($koneksi, $_POST['latitude']);
$longitude = mysqli_real_escape_string($koneksi, $_POST['longitude']);

// Check apakah sudah check-in hari ini
$today = date('Y-m-d');
$check = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_user='$id_user' AND tanggal='$today'");

if(mysqli_num_rows($check) > 0) {
    $_SESSION['error'] = "Anda sudah melakukan check-in hari ini!";
    header("Location: checkin.php");
    exit;
}

// Proses upload foto
$foto_name = '';
if (!empty($_POST['foto'])) {
    $foto_data = $_POST['foto'];
    
    // Remove header dari base64
    $foto_data = str_replace('data:image/jpeg;base64,', '', $foto_data);
    $foto_data = str_replace(' ', '+', $foto_data);
    $foto_decoded = base64_decode($foto_data);
    
    // Generate nama file unik
    $foto_name = 'checkin_' . $id_user . '_' . time() . '.jpg';
    $foto_path = '../uploads/' . $foto_name;
    
    // Simpan file
    if (!file_put_contents($foto_path, $foto_decoded)) {
        $_SESSION['error'] = "Gagal menyimpan foto!";
        header("Location: checkin.php");
        exit;
    }
}

// Get waktu sekarang
$jam_masuk = date('H:i:s');
$tanggal = date('Y-m-d');

// Insert ke database
$query = "INSERT INTO absensi (id_user, nama, tanggal, jam_masuk, foto, latitude, longitude, keterangan) 
          VALUES ('$id_user', '$nama', '$tanggal', '$jam_masuk', '$foto_name', '$latitude', '$longitude', '$keterangan')";

if (mysqli_query($koneksi, $query)) {
    $_SESSION['success'] = "Check-in berhasil pada " . date('H:i:s');
    header("Location: karyawan_dashboard.php");
} else {
    $_SESSION['error'] = "Gagal melakukan check-in: " . mysqli_error($koneksi);
    header("Location: checkin.php");
}
exit;
?>