<?php
if (session_status() == PHP_SESSION_NONE) session_start();

// Pastikan sudah login sebagai karyawan
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'karyawan') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Filter tanggal
$filter_tanggal = isset($_GET['tanggal']) ? mysqli_real_escape_string($koneksi, $_GET['tanggal']) : '';
$bulan = isset($_GET['bulan']) ? mysqli_real_escape_string($koneksi, $_GET['bulan']) : date('Y-m');

// Query riwayat absensi dengan filter
$query_riwayat = "SELECT * FROM absensi WHERE id_user='$id_user'";

if (!empty($filter_tanggal)) {
    $query_riwayat .= " AND tanggal = '$filter_tanggal'";
} elseif (!empty($bulan)) {
    $query_riwayat .= " AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan'";
}

$query_riwayat .= " ORDER BY tanggal DESC, jam_masuk DESC";

$riwayat = mysqli_query($koneksi, $query_riwayat);
$total_riwayat = mysqli_num_rows($riwayat);

// Statistik filter yang dipilih
if (!empty($filter_tanggal)) {
    $periode = date('d F Y', strtotime($filter_tanggal));
} elseif (!empty($bulan)) {
    $periode = date('F Y', strtotime($bulan . '-01'));
} else {
    $periode = 'Semua Data';
}
?>

<style>
    .filter-section {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    
    .filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
    }
    
    .filter-item label {
        display: block;
        margin-bottom: 8px;
        color: #2c3e50;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .filter-input {
        width: 100%;
        padding: 10px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.3s;
    }
    
    .filter-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .filter-buttons {
        display: flex;
        gap: 10px;
    }
    
    .filter-buttons .btn {
        flex: 1;
    }
    
    .info-badge {
        display: inline-block;
        padding: 8px 15px;
        background: #e3f2fd;
        color: #1976d2;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        margin-left: 10px;
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.3s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 15px;
        max-width: 700px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideIn 0.3s;
    }
    
    @keyframes slideIn {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 15px;
    }
    
    .modal-header h3 {
        color: #2c3e50;
        margin: 0;
    }
    
    .close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #e74c3c;
        transition: transform 0.2s;
    }
    
    .close-btn:hover {
        transform: scale(1.2);
    }
    
    .detail-grid-modal {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .detail-box {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #667eea;
    }
    
    .detail-box.success {
        background: #e8f5e9;
        border-left-color: #4caf50;
    }
    
    .detail-box.warning {
        background: #fff3e0;
        border-left-color: #ff9800;
    }
    
    .detail-box.info {
        background: #e3f2fd;
        border-left-color: #2196f3;
    }
    
    .detail-box label {
        display: block;
        color: #7f8c8d;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: uppercase;
    }
    
    .detail-box .value {
        color: #2c3e50;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .map-link {
        display: inline-block;
        padding: 10px 20px;
        background: #667eea;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
        margin-right: 10px;
        margin-top: 10px;
    }
    
    .map-link:hover {
        background: #5568d3;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .map-link.orange {
        background: #f39c12;
    }
    
    .map-link.orange:hover {
        background: #e67e22;
    }
    
    @media (max-width: 768px) {
        .filter-form {
            grid-template-columns: 1fr;
        }
        
        .detail-grid-modal {
            grid-template-columns: 1fr;
        }
        
        .filter-buttons {
            flex-direction: column;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="header-content">
        <h3>Riwayat Absensi</h3>
        <p class="subtitle">
            Periode: <span class="info-badge"><?= $periode ?></span>
            <span class="info-badge">Total: <?= $total_riwayat ?> Record</span>
        </p>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <h5 style="margin-bottom: 15px; color: #2c3e50;">Filter Data</h5>
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="page" value="riwayat">
        
        <div class="filter-item">
            <label>Pilih Tanggal Spesifik:</label>
            <input 
                type="date" 
                name="tanggal" 
                class="filter-input" 
                value="<?= htmlspecialchars($filter_tanggal) ?>"
            >
        </div>
        
        <div class="filter-item">
            <label>Atau Pilih Bulan:</label>
            <input 
                type="month" 
                name="bulan" 
                class="filter-input" 
                value="<?= htmlspecialchars($bulan) ?>"
            >
        </div>
        
        <div class="filter-item">
            <label>&nbsp;</label>
            <div class="filter-buttons">
                <button type="submit" class="btn btn-primary">Cari</button>
                <a href="karyawan_dashboard.php?page=riwayat" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- Tabel Riwayat -->
<div class="card table-card">
    <div class="card-body">
        <?php if($total_riwayat > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($row = mysqli_fetch_assoc($riwayat)): 
                            $status_class = empty($row['jam_keluar']) ? 'badge-warning' : 'badge-success';
                            $status_text = empty($row['jam_keluar']) ? 'Belum Pulang' : 'Lengkap';
                            $tanggal_format = date('d M Y', strtotime($row['tanggal']));
                            
                            // Hitung durasi kerja jika sudah pulang
                            $durasi = '-';
                            if($row['jam_masuk'] && $row['jam_keluar']) {
                                $masuk = strtotime($row['jam_masuk']);
                                $keluar = strtotime($row['jam_keluar']);
                                $diff = $keluar - $masuk;
                                $jam = floor($diff / 3600);
                                $menit = floor(($diff % 3600) / 60);
                                $durasi = $jam . "j " . $menit . "m";
                            }
                        ?>
                        <tr>
                            <td data-label="No"><?= $no++ ?></td>
                            <td data-label="Tanggal">
                                <span class="date-badge"><?= $tanggal_format ?></span>
                            </td>
                            <td data-label="Jam Masuk">
                                <span class="time-badge time-in">
                                    <?= $row['jam_masuk'] ?: '-' ?>
                                </span>
                            </td>
                            <td data-label="Jam Keluar">
                                <span class="time-badge time-out">
                                     <?= $row['jam_keluar'] ?: '-' ?>
                                </span>
                            </td>
                            <td data-label="Durasi">
                                <strong><?= $durasi ?></strong>
                            </td>
                            <td data-label="Status">
                                <span class="badge <?= $status_class ?>">
                                    <?= $status_text ?>
                                </span>
                            </td>
                            <td data-label="Aksi">
                                <button 
                                    class="btn btn-info btn-sm" 
                                    onclick='showDetail(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>, "<?= $durasi ?>")'>
                                    Detail
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h4>Tidak Ada Data</h4>
                <p>
                    <?php 
                    if(!empty($filter_tanggal)) {
                        echo "Tidak ada absensi pada tanggal " . date('d F Y', strtotime($filter_tanggal));
                    } elseif(!empty($bulan)) {
                        echo "Tidak ada absensi pada bulan " . date('F Y', strtotime($bulan . '-01'));
                    } else {
                        echo "Belum ada riwayat absensi";
                    }
                    ?>
                </p>
                <a href="karyawan_dashboard.php" class="btn btn-primary" style="margin-top: 15px;">
                    ← Kembali ke Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Detail -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detail Absensi</h3>
            <button onclick="closeModal()" class="close-btn">✖</button>
        </div>
        <div id="modalContent"></div>
    </div>
</div>

<script>
// Show detail modal
function showDetail(data, durasi) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('modalContent');
    
    const tanggal = new Date(data.tanggal).toLocaleDateString('id-ID', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    let html = `
        <div class="detail-grid-modal">
            <div class="detail-box">
                <label>TANGGAL</label>
                <div class="value">${tanggal}</div>
            </div>
            
            <div class="detail-box info">
                <label>DURASI KERJA</label>
                <div class="value">${durasi}</div>
            </div>
            
            <div class="detail-box success">
                <label>JAM MASUK</label>
                <div class="value" style="font-size: 1.3rem;">${data.jam_masuk || '-'}</div>
            </div>
            
            <div class="detail-box warning">
                <label>JAM KELUAR</label>
                <div class="value" style="font-size: 1.3rem;">${data.jam_keluar || 'Belum Pulang'}</div>
            </div>
        </div>
    `;
    
    if (data.keterangan) {
        html += `
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                <label style="display: block; color: #7f8c8d; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px;">KETERANGAN</label>
                <div style="color: #2c3e50;">${data.keterangan}</div>
            </div>
        `;
    }
    
    if (data.latitude && data.longitude) {
        html += `
            <div style="margin-bottom: 15px;">
                <label style="display: block; color: #7f8c8d; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px;">LOKASI</label>
                <a href="https://maps.google.com/?q=${data.latitude},${data.longitude}" target="_blank" class="map-link">
                     Lokasi Masuk
                </a>
    `;
        
        if (data.lat_keluar && data.lon_keluar) {
            html += `
                <a href="https://maps.google.com/?q=${data.lat_keluar},${data.lon_keluar}" target="_blank" class="map-link orange">
                     Lokasi Keluar
                </a>
            `;
        }
        
        html += `</div>`;
    }
    
    html += `
        <button onclick="closeModal()" 
                style="width: 100%; padding: 12px; background: #95a5a6; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: all 0.3s;">
            Tutup
        </button>
    `;
    
    content.innerHTML = html;
    modal.style.display = 'flex';
}

// Close modal
function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
}

// Close modal on outside click
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>