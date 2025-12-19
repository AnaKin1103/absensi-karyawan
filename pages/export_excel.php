<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include('../config/koneksi.php');

// Ambil parameter filter (sama seperti di halaman absensi)
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$tanggal_dari = isset($_GET['tanggal_dari']) ? mysqli_real_escape_string($koneksi, $_GET['tanggal_dari']) : '';
$tanggal_sampai = isset($_GET['tanggal_sampai']) ? mysqli_real_escape_string($koneksi, $_GET['tanggal_sampai']) : '';

// Query dengan JOIN - SAMA SEPERTI DI HALAMAN ABSENSI
$query = "SELECT a.*, 
                 u.nama, 
                 u.username, 
                 k.nama_lengkap, 
                 k.nip,
                 k.email,
                 k.no_tlp
          FROM absensi a 
          LEFT JOIN user u ON a.id_user = u.id_user 
          LEFT JOIN karyawan k ON u.id_user = k.id_user 
          WHERE 1=1";

// Filter pencarian
if (!empty($search)) {
    $query .= " AND (u.nama LIKE '%$search%' 
                OR u.username LIKE '%$search%' 
                OR k.nama_lengkap LIKE '%$search%' 
                OR k.nip LIKE '%$search%')";
}

// Filter rentang tanggal
if (!empty($tanggal_dari) && !empty($tanggal_sampai)) {
    $query .= " AND a.tanggal BETWEEN '$tanggal_dari' AND '$tanggal_sampai'";
} elseif (!empty($tanggal_dari)) {
    $query .= " AND a.tanggal >= '$tanggal_dari'";
} elseif (!empty($tanggal_sampai)) {
    $query .= " AND a.tanggal <= '$tanggal_sampai'";
}

$query .= " ORDER BY a.tanggal DESC, a.jam_masuk DESC";

$result = mysqli_query($koneksi, $query);

// Set header untuk download Excel
$filename = "Data_Absensi_" . date('Y-m-d_His') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Buat nama file yang deskriptif
$filter_info = "";
if (!empty($tanggal_dari) && !empty($tanggal_sampai)) {
    $filter_info = " (" . date('d-M-Y', strtotime($tanggal_dari)) . " s/d " . date('d-M-Y', strtotime($tanggal_sampai)) . ")";
} elseif (!empty($tanggal_dari)) {
    $filter_info = " (Dari " . date('d-M-Y', strtotime($tanggal_dari)) . ")";
} elseif (!empty($tanggal_sampai)) {
    $filter_info = " (Sampai " . date('d-M-Y', strtotime($tanggal_sampai)) . ")";
}

// Array hari dalam bahasa Indonesia
$hari_indo = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Absensi</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .info {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN DATA ABSENSI<?= $filter_info ?></h2>
        <p>Dicetak pada: <?= date('d F Y H:i:s') ?></p>
    </div>

    <?php if (!empty($search)): ?>
    <div class="info">
        <strong>Filter Pencarian:</strong> <?= htmlspecialchars($search) ?>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIP</th>
                <th>Nama Lengkap</th>
                <th>Username</th>
                <th>Hari</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Durasi Kerja</th>
                <th>Status</th>
                <th>Email</th>
                <th>No. Telepon</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (mysqli_num_rows($result) > 0):
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)): 
                    // Hitung durasi kerja
                    $durasi = '-';
                    if (!empty($row['jam_masuk']) && !empty($row['jam_keluar'])) {
                        $jam_masuk = strtotime($row['jam_masuk']);
                        $jam_keluar = strtotime($row['jam_keluar']);
                        $selisih = $jam_keluar - $jam_masuk;
                        
                        $jam = floor($selisih / 3600);
                        $menit = floor(($selisih % 3600) / 60);
                        $durasi = $jam . ' jam ' . $menit . ' menit';
                    }

                    // Tentukan status
                    $status = empty($row['jam_keluar']) ? 'Belum Pulang' : 'Lengkap';

                    // Format tanggal dan hari
                    $tanggal_format = date('d-m-Y', strtotime($row['tanggal']));
                    $hari = date('l', strtotime($row['tanggal']));
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['nip'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama_lengkap'] ?? $row['nama']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= $hari_indo[$hari] ?></td>
                <td><?= $tanggal_format ?></td>
                <td><?= $row['jam_masuk'] ?: '-' ?></td>
                <td><?= $row['jam_keluar'] ?: '-' ?></td>
                <td><?= $durasi ?></td>
                <td><?= $status ?></td>
                <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['no_tlp'] ?? '-') ?></td>
            </tr>
            <?php 
                endwhile;
            else:
            ?>
            <tr>
                <td colspan="12" style="text-align: center;">Tidak ada data</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br><br>
    <div class="info">
        <strong>Total Data:</strong> <?= mysqli_num_rows($result) ?> record
    </div>
</body>
</html>