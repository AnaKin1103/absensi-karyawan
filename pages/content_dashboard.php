<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include('../config/koneksi.php');

// Hitung total karyawan
$total_karyawan = mysqli_fetch_array(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM user WHERE role='karyawan'")
)['total'];

// Hitung total absen hari ini
$total_absen_hari_ini = mysqli_fetch_array(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM absensi WHERE tanggal = CURDATE()")
)['total'];

// Ambil data absensi hari ini dengan JOIN ke tabel user dan karyawan
$absensi = mysqli_query(
    $koneksi, 
    "SELECT a.*, 
            u.nama, 
            u.username, 
            k.nama_lengkap, 
            k.nip 
     FROM absensi a 
     LEFT JOIN user u ON a.id_user = u.id_user 
     LEFT JOIN karyawan k ON u.id_user = k.id_user 
     WHERE a.tanggal = CURDATE() 
     ORDER BY a.jam_masuk DESC"
);
?>

<div class="header-section">
    <div class="welcome-text">
        <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?></h2>
        <p class="current-date"><?= date('d F Y') ?></p>
    </div>
</div>

<!-- Ringkasan -->
<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-icon">👥</div>
        <div class="summary-content">
            <h6>Total Karyawan</h6>
            <h3><?= $total_karyawan ?></h3>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon">✅</div>
        <div class="summary-content">
            <h6>Absensi Hari Ini</h6>
            <h3><?= $total_absen_hari_ini ?></h3>
        </div>
    </div>
    <div class="summary-card clock-card">
        <div class="summary-icon">🕗</div>
        <div class="summary-content">
            <h6>Waktu</h6>
            <h3 id="clock">--:--:--</h3>
        </div>
    </div>
</div>

<!-- Tabel Absensi -->
<div class="card table-card">
    <div class="card-header">
        <h5>Absensi Hari Ini</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Foto Masuk</th>
                        <th>Foto Keluar</th>
                        <th>Lokasi Masuk</th>
                        <th>Lokasi Keluar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($absensi) > 0): ?>
                        <?php $no = 1; while($data = mysqli_fetch_assoc($absensi)): ?>
                        <tr>
                            <td data-label="No"><?= $no++ ?></td>
                            <td data-label="NIP">
                                <span class="badge badge-secondary">
                                    <?= htmlspecialchars($data['nip'] ?? '-') ?>
                                </span>
                            </td>
                            <td data-label="Nama">
                                <div class="employee-info">
                                    <span class="employee-avatar">
                                        <?= strtoupper(substr($data['nama_lengkap'] ?? $data['nama'], 0, 2)) ?>
                                    </span>
                                    <div>
                                        <strong><?= htmlspecialchars($data['nama_lengkap'] ?? $data['nama']) ?></strong>
                                        <br>
                                        <small style="color: #666;">@<?= htmlspecialchars($data['username']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Jam Masuk">
                                <span class="time-badge time-in">
                                    <?= $data['jam_masuk'] ?: '-' ?>
                                </span>
                            </td>
                            <td data-label="Jam Keluar">
                                <span class="time-badge time-out">
                                    <?= $data['jam_keluar'] ?: '-' ?>
                                </span>
                            </td>
                            <td data-label="Foto Masuk">
                                <?php if(!empty($data['foto'])): ?>
                                    <img src="../uploads/<?= htmlspecialchars($data['foto']) ?>" 
                                         class="attendance-img" 
                                         alt="Foto Masuk"
                                         onclick="showImageModal(this.src)">
                                <?php else: ?> 
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Foto Keluar">
                                <?php if(!empty($data['foto_keluar'])): ?>
                                    <img src="../uploads/<?= htmlspecialchars($data['foto_keluar']) ?>" 
                                         class="attendance-img" 
                                         alt="Foto Keluar"
                                         onclick="showImageModal(this.src)">
                                <?php else: ?> 
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Lokasi Masuk">
                                <?php if(!empty($data['latitude']) && !empty($data['longitude'])): ?>
                                    <a href="https://maps.google.com/?q=<?= $data['latitude'] ?>,<?= $data['longitude'] ?>" 
                                       target="_blank" 
                                       class="btn btn-primary btn-sm">
                                        📍 Lihat
                                    </a>
                                <?php else: ?> 
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Lokasi Keluar">
                                <?php if(!empty($data['lat_keluar']) && !empty($data['lon_keluar'])): ?>
                                    <a href="https://maps.google.com/?q=<?= $data['lat_keluar'] ?>,<?= $data['lon_keluar'] ?>" 
                                       target="_blank" 
                                       class="btn btn-primary btn-sm">
                                        📍 Lihat
                                    </a>
                                <?php else: ?> 
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Aksi">
                                <div class="action-buttons">
                                    <a href="detail_absensi.php?id_absensi=<?= $data['id_absensi'] ?>" 
                                       class="btn btn-info btn-sm"
                                       title="Lihat Detail">
                                        Detail
                                    </a>
                                    <a href="edit_absensi.php?id_absensi=<?= $data['id_absensi'] ?>" 
                                       class="btn btn-warning btn-sm"
                                       title="Edit Absensi">
                                        Edit
                                    </a>
                                    <a href="hapus_absensi.php?id_absensi=<?= $data['id_absensi'] ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Yakin ingin menghapus data absensi <?= htmlspecialchars($data['nama_lengkap'] ?? $data['nama']) ?>?')"
                                       title="Hapus Absensi">
                                        Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">
                                <div class="empty-state">
                                    <div class="empty-icon">📭</div>
                                    <h4>Tidak ada data absensi hari ini</h4>
                                    <p>Belum ada karyawan yang melakukan absensi</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal untuk melihat foto lebih besar -->
<div id="imageModal" class="modal" onclick="closeImageModal()">
    <span class="close">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

<style>
.employee-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.employee-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9em;
    flex-shrink: 0;
}

.time-badge {
    padding: 5px 10px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9em;
    display: inline-block;
}

.time-in {
    background: #d4edda;
    color: #155724;
}

.time-out {
    background: #fff3cd;
    color: #856404;
}

.attendance-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s;
    border: 2px solid #ddd;
}

.attendance-img:hover {
    transform: scale(1.1);
    border-color: #667eea;
}

.badge-secondary {
    background: #95a5a6;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.85em;
    font-weight: 600;
}

/* Modal untuk foto */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    padding-top: 50px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.9);
}

.modal-content {
    margin: auto;
    display: block;
    max-width: 80%;
    max-height: 80%;
    border-radius: 8px;
}

.close {
    position: absolute;
    top: 15px;
    right: 35px;
    color: #f1f1f1;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: #bbb;
}

.empty-state {
    padding: 40px 20px;
    text-align: center;
}

.empty-icon {
    font-size: 4em;
    margin-bottom: 15px;
}

.empty-state h4 {
    color: #333;
    margin-bottom: 10px;
}

.empty-state p {
    color: #666;
}

@media (max-width: 768px) {
    .table thead {
        display: none;
    }
    
    .table tr {
        display: block;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        background: #fff;
    }
    
    .table td {
        display: flex;
        justify-content: space-between;
        padding: 8px;
        border: none;
        align-items: center;
    }
    
    .table td::before {
        content: attr(data-label);
        font-weight: bold;
        margin-right: 10px;
        flex-shrink: 0;
    }
    
    .action-buttons {
        flex-direction: column;
        width: 100%;
    }
    
    .action-buttons .btn {
        width: 100%;
        margin-bottom: 5px;
    }
}
</style>

<script>
// Fungsi untuk menampilkan modal foto
function showImageModal(src) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    modal.style.display = "block";
    modalImg.src = src;
}

// Fungsi untuk menutup modal
function closeImageModal() {
    document.getElementById('imageModal').style.display = "none";
}

// Tutup modal dengan ESC
document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
        closeImageModal();
    }
});

// Update clock
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;
}

setInterval(updateClock, 1000);
updateClock();
</script>