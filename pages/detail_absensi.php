<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include('../config/koneksi.php');

// Cek apakah ada ID absensi
if (!isset($_GET['id_absensi'])) {
    header('Location: index.php?page=absensi');
    exit;
}

$id_absensi = mysqli_real_escape_string($koneksi, $_GET['id_absensi']);

// Ambil data absensi dengan JOIN
$query = "SELECT a.*, 
                 u.nama, 
                 u.username, 
                 k.nama_lengkap, 
                 k.nip,
                 k.email,
                 k.no_tlp,
                 k.alamat
          FROM absensi a 
          LEFT JOIN user u ON a.id_user = u.id_user 
          LEFT JOIN karyawan k ON u.id_user = k.id_user 
          WHERE a.id_absensi = '$id_absensi'";

$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Data tidak ditemukan'); window.location='index.php?page=absensi';</script>";
    exit;
}

$data = mysqli_fetch_assoc($result);

// Format tanggal
$tanggal_format = date('d F Y', strtotime($data['tanggal']));
$hari = date('l', strtotime($data['tanggal']));
$hari_indo = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];

// Hitung durasi kerja jika ada jam keluar
$durasi = '-';
if (!empty($data['jam_masuk']) && !empty($data['jam_keluar'])) {
    $jam_masuk = strtotime($data['jam_masuk']);
    $jam_keluar = strtotime($data['jam_keluar']);
    $selisih = $jam_keluar - $jam_masuk;
    
    $jam = floor($selisih / 3600);
    $menit = floor(($selisih % 3600) / 60);
    $durasi = $jam . ' jam ' . $menit . ' menit';
}

// Tentukan status
$status = '';
$status_class = '';
if (empty($data['jam_keluar'])) {
    $status = 'Belum Pulang';
    $status_class = 'badge-warning';
} else {
    $status = 'Lengkap';
    $status_class = 'badge-success';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Absensi - <?= htmlspecialchars($data['nama_lengkap'] ?? $data['nama']) ?></title>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h2>Detail Absensi</h2>
            <a href="dashboard.php?page=dashboard" class="btn btn-back">
                ← Kembali
            </a>
        </div>

        <!-- Info Karyawan -->
        <div class="employee-header">
            <div class="employee-avatar">
                <?= strtoupper(substr($data['nama_lengkap'] ?? $data['nama'], 0, 2)) ?>
            </div>
            <div class="employee-details">
                <h2><?= htmlspecialchars($data['nama_lengkap'] ?? $data['nama']) ?></h2>
                <p>@<?= htmlspecialchars($data['username']) ?> | NIP: <?= htmlspecialchars($data['nip'] ?? '-') ?></p>
                <span class="badge <?= $status_class ?>"><?= $status ?></span>
            </div>
        </div>

        <!-- Grid Content -->
        <div class="content-grid">
            <!-- Data Karyawan -->
            <div class="card">
                <h3>Data Karyawan</h3>
                <div class="info-row">
                    <div class="info-label">NIP</div>
                    <div class="info-value"><?= htmlspecialchars($data['nip'] ?? '-') ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Nama Lengkap</div>
                    <div class="info-value"><?= htmlspecialchars($data['nama_lengkap'] ?? $data['nama']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Username</div>
                    <div class="info-value">@<?= htmlspecialchars($data['username']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= htmlspecialchars($data['email'] ?? '-') ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">No. Telepon</div>
                    <div class="info-value"><?= htmlspecialchars($data['no_tlp'] ?? '-') ?></div>
                </div>
            </div>

            <!-- Data Absensi -->
            <div class="card">
                <h3>Data Absensi</h3>
                <div class="info-row">
                    <div class="info-label">Tanggal</div>
                    <div class="info-value">
                        <?= $hari_indo[$hari] ?>, <?= $tanggal_format ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Jam Masuk</div>
                    <div class="info-value">
                        <strong style="color: #28a745;"><?= $data['jam_masuk'] ?: '-' ?></strong>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Jam Keluar</div>
                    <div class="info-value">
                        <strong style="color: #ffc107;"><?= $data['jam_keluar'] ?: '-' ?></strong>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Durasi Kerja</div>
                    <div class="info-value">
                        <strong style="color: #007bff;"><?= $durasi ?></strong>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="badge <?= $status_class ?>"><?= $status ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Foto Absensi -->
        <div class="card">
            <h3>Foto Absensi</h3>
            <div class="photo-container">
                <div class="photo-box">
                    <h4>Foto Masuk</h4>
                    <?php if (!empty($data['foto'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($data['foto']) ?>" 
                             alt="Foto Masuk"
                             onclick="showModal(this.src)">
                    <?php else: ?>
                        <div class="no-photo">📷</div>
                        <p style="color: #999; margin-top: 10px;">Tidak ada foto</p>
                    <?php endif; ?>
                </div>
                <div class="photo-box">
                    <h4>Foto Keluar</h4>
                    <?php if (!empty($data['foto_keluar'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($data['foto_keluar']) ?>" 
                             alt="Foto Keluar"
                             onclick="showModal(this.src)">
                    <?php else: ?>
                        <div class="no-photo">📷</div>
                        <p style="color: #999; margin-top: 10px;">Tidak ada foto</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Lokasi Absensi -->
        <div class="card">
            <h3>Lokasi Absensi</h3>
            <div class="map-container">
                <div class="map-box">
                    <h4>Lokasi Masuk</h4>
                    <?php if (!empty($data['latitude']) && !empty($data['longitude'])): ?>
                        <a href="https://maps.google.com/?q=<?= $data['latitude'] ?>,<?= $data['longitude'] ?>" 
                           target="_blank" 
                           class="map-link">
                            Lihat di Google Maps
                        </a>
                        <p style="color: #666; margin-top: 10px; font-size: 0.9em;">
                            Koordinat: <?= $data['latitude'] ?>, <?= $data['longitude'] ?>
                        </p>
                    <?php else: ?>
                        <div class="no-location">Tidak ada data lokasi</div>
                    <?php endif; ?>
                </div>
                <div class="map-box">
                    <h4>Lokasi Keluar</h4>
                    <?php if (!empty($data['lat_keluar']) && !empty($data['lon_keluar'])): ?>
                        <a href="https://maps.google.com/?q=<?= $data['lat_keluar'] ?>,<?= $data['lon_keluar'] ?>" 
                           target="_blank" 
                           class="map-link">
                            Lihat di Google Maps
                        </a>
                        <p style="color: #666; margin-top: 10px; font-size: 0.9em;">
                            Koordinat: <?= $data['lat_keluar'] ?>, <?= $data['lon_keluar'] ?>
                        </p>
                    <?php else: ?>
                        <div class="no-location">Tidak ada data lokasi</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk foto -->
    <div id="photoModal" class="modal" onclick="closeModal()">
        <span class="close-modal">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        function showModal(src) {
            const modal = document.getElementById('photoModal');
            const modalImg = document.getElementById('modalImage');
            modal.style.display = "block";
            modalImg.src = src;
        }

        function closeModal() {
            document.getElementById('photoModal').style.display = "none";
        }

        // Tutup modal dengan tombol ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeModal();
            }
        });
    </script>
</body>
</html>