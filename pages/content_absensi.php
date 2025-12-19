<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include('../config/koneksi.php');

$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$tanggal_dari = isset($_GET['tanggal_dari']) ? mysqli_real_escape_string($koneksi, $_GET['tanggal_dari']) : '';
$tanggal_sampai = isset($_GET['tanggal_sampai']) ? mysqli_real_escape_string($koneksi, $_GET['tanggal_sampai']) : '';

// PERBAIKAN: Query dengan JOIN untuk mendapatkan nama dari tabel user dan karyawan
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

if (!empty($search)) {
    // PERBAIKAN: Cari di nama user, nama_lengkap, username, dan NIP
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

$absensi = mysqli_query($koneksi, $query);
$total_absensi = mysqli_num_rows($absensi);
?>
<div class="page-header">
    <div class="header-content">
        <h3>Data Absensi</h3>
        <p class="subtitle">Total: <?= $total_absensi ?> Record</p>
    </div>
    <div class="header-actions">
        <a href="tambah_absensi.php" class="btn btn-primary">
            <span>➕</span> Tambah Data
        </a>
        <?php
        // Build URL untuk export dengan filter yang sama
        $export_url = "export_excel.php?";
        $params = [];
        if(!empty($search)) {
            $params[] = "search=" . urlencode($search);
        }
        if(!empty($tanggal_dari)) {
            $params[] = "tanggal_dari=" . urlencode($tanggal_dari);
        }
        if(!empty($tanggal_sampai)) {
            $params[] = "tanggal_sampai=" . urlencode($tanggal_sampai);
        }
        $export_url .= implode("&", $params);
        ?>
        <a href="<?= $export_url ?>" class="btn btn-success">
            <span>📊</span> Export Excel
        </a>
    </div>
</div>

<!-- Filter & Search -->
<div class="filter-container">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="page" value="absensi">
        <div class="filter-group">
            <div class="filter-item">
                <label>🔍 Cari Nama:</label>
                <input 
                    type="text" 
                    name="search" 
                    class="filter-input" 
                    placeholder="Nama karyawan, NIP, atau username..." 
                    value="<?= htmlspecialchars($search) ?>"
                >
            </div>
            <div class="filter-item">
                <label>Dari Tanggal:</label>
                <input 
                    type="date" 
                    name="tanggal_dari" 
                    class="filter-input" 
                    value="<?= htmlspecialchars($tanggal_dari) ?>"
                >
            </div>
            <div class="filter-item">
                <label>Sampai Tanggal:</label>
                <input 
                    type="date" 
                    name="tanggal_sampai" 
                    class="filter-input" 
                    value="<?= htmlspecialchars($tanggal_sampai) ?>"
                >
            </div>
            <div class="filter-item filter-buttons">
                <button type="submit" class="btn btn-search">Cari</button>
                <?php if(!empty($search) || !empty($tanggal_dari) || !empty($tanggal_sampai)): ?>
                    <a href="?page=absensi" class="btn btn-reset">Reset</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- Info Filter Aktif -->
<?php if(!empty($tanggal_dari) || !empty($tanggal_sampai)): ?>
<div style="background: #e3f2fd; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2196f3;">
    <strong>Filter Aktif:</strong> 
    <?php 
    if(!empty($tanggal_dari) && !empty($tanggal_sampai)) {
        echo "Periode " . date('d M Y', strtotime($tanggal_dari)) . " - " . date('d M Y', strtotime($tanggal_sampai));
    } elseif(!empty($tanggal_dari)) {
        echo "Mulai " . date('d M Y', strtotime($tanggal_dari));
    } else {
        echo "Sampai " . date('d M Y', strtotime($tanggal_sampai));
    }
    ?>
</div>
<?php endif; ?>

<!-- Tabel Absensi -->
<div class="card table-card">
    <div class="card-body">
        <?php if($total_absensi > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>Nama</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($row = mysqli_fetch_assoc($absensi)): 
                            $status = '';
                            $status_class = '';
                            if(empty($row['jam_keluar'])) {
                                $status = 'Belum Pulang';
                                $status_class = 'badge-warning';
                            } else {
                                $status = 'Lengkap';
                                $status_class = 'badge-success';
                            }
                            
                            $tanggal_format = date('d M Y', strtotime($row['tanggal']));
                            $hari = date('l', strtotime($row['tanggal']));
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
                        <tr>
                            <td data-label="No"><?= $no++ ?></td>
                            <td data-label="NIP">
                                <span class="badge badge-secondary">
                                    <?= htmlspecialchars($row['nip'] ?? '-') ?>
                                </span>
                            </td>
                            <td data-label="Nama">
                                <div class="employee-info">
                                    <span class="employee-avatar">
                                        <?= strtoupper(substr($row['nama_lengkap'] ?? $row['nama'], 0, 2)) ?>
                                    </span>
                                    <div>
                                        <strong><?= htmlspecialchars($row['nama_lengkap'] ?? $row['nama']) ?></strong>
                                        <br>
                                        <small style="color: #666;">@<?= htmlspecialchars($row['username']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Tanggal">
                                <div class="date-info">
                                    <span class="date-badge"><?= $tanggal_format ?></span>
                                    <br>
                                    <small style="color: #666;"><?= $hari_indo[$hari] ?></small>
                                </div>
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
                            <td data-label="Status">
                                <span class="badge <?= $status_class ?>">
                                    <?= $status ?>
                                </span>
                            </td>
                            <td data-label="Aksi">
                                <div class="action-buttons">
                                    <a href="detail_absensi.php?id_absensi=<?= $row['id_absensi'] ?>" 
                                       class="btn btn-info btn-sm" 
                                       title="Lihat Detail">
                                        Detail
                                    </a>
                                    <a href="edit_absensi.php?id_absensi=<?= $row['id_absensi'] ?>" 
                                       class="btn btn-warning btn-sm" 
                                       title="Edit Absensi">
                                        Edit
                                    </a>
                                    <a href="hapus_absensi.php?id_absensi=<?= $row['id_absensi'] ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Yakin ingin menghapus data absensi <?= htmlspecialchars($row['nama_lengkap'] ?? $row['nama']) ?>?')"
                                       title="Hapus Absensi">
                                        Hapus
                                    </a>
                                </div>
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
                    if(!empty($search) || !empty($tanggal_dari) || !empty($tanggal_sampai)) {
                        echo "Tidak ada hasil untuk filter yang dipilih";
                    } else {
                        echo "Belum ada data absensi yang tercatat";
                    }
                    ?>
                </p>
                <?php if(empty($search) && empty($tanggal_dari) && empty($tanggal_sampai)): ?>
                    <a href="tambah_absensi.php" class="btn btn-primary">Tambah Data Absensi</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
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

.date-badge {
    background: #f0f0f0;
    padding: 5px 10px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9em;
    display: inline-block;
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

.badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.85em;
    font-weight: 600;
    display: inline-block;
}

.badge-success {
    background: #28a745;
    color: white;
}

.badge-warning {
    background: #ffc107;
    color: #000;
}

.badge-secondary {
    background: #95a5a6;
    color: white;
}

.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.875rem;
}

.empty-state {
    padding: 60px 20px;
    text-align: center;
}

.empty-icon {
    font-size: 5em;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state h4 {
    color: #333;
    margin-bottom: 10px;
    font-size: 1.5em;
}

.empty-state p {
    color: #666;
    margin-bottom: 20px;
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
        padding: 15px;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .table td {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border: none;
        align-items: center;
    }
    
    .table td::before {
        content: attr(data-label);
        font-weight: bold;
        margin-right: 10px;
        flex-shrink: 0;
        color: #666;
    }
    
    .action-buttons {
        flex-direction: column;
        width: 100%;
        gap: 5px;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
    
    .filter-group {
        flex-direction: column;
    }
    
    .filter-item {
        width: 100%;
    }
}
</style>