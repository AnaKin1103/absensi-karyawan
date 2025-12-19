<?php
session_start();
include('../config/koneksi.php');

// Proteksi karyawan
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'karyawan') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id_absensi = mysqli_real_escape_string($koneksi, $_POST['id_absensi']);
$keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
$latitude = mysqli_real_escape_string($koneksi, $_POST['latitude']);
$longitude = mysqli_real_escape_string($koneksi, $_POST['longitude']);

// Validasi id_absensi
$check = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_absensi='$id_absensi' AND id_user='$id_user'");

if(mysqli_num_rows($check) == 0) {
    $_SESSION['error'] = "Data absensi tidak ditemukan!";
    header("Location: checkout.php");
    exit;
}

$data = mysqli_fetch_assoc($check);

// Check apakah sudah checkout
if(!empty($data['jam_keluar'])) {
    $_SESSION['error'] = "Anda sudah melakukan check-out!";
    header("Location: checkout.php");
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
    $foto_name = 'checkout_' . $id_user . '_' . time() . '.jpg';
    $foto_path = '../uploads/' . $foto_name;
    
    // Simpan file
    if (!file_put_contents($foto_path, $foto_decoded)) {
        $_SESSION['error'] = "Gagal menyimpan foto!";
        header("Location: checkout.php");
        exit;
    }
}

// Get waktu sekarang
$jam_keluar = date('H:i:s');

// Update keterangan jika ada
$keterangan_update = $data['keterangan'];
if(!empty($keterangan)) {
    $keterangan_update = $data['keterangan'] . " | Keluar: " . $keterangan;
}

// Update database
$query = "UPDATE absensi SET 
          jam_keluar = '$jam_keluar',
          foto_keluar = '$foto_name',
          lat_keluar = '$latitude',
          lon_keluar = '$longitude',
          keterangan = '$keterangan_update'
          WHERE id_absensi = '$id_absensi' AND id_user = '$id_user'";

if (mysqli_query($koneksi, $query)) {
    $_SESSION['success'] = "Check-out berhasil pada " . date('H:i:s');
    header("Location: karyawan_dashboard.php");
} else {
    $_SESSION['error'] = "Gagal melakukan check-out: " . mysqli_error($koneksi);
    header("Location: checkout.php");
}
exit;
?>