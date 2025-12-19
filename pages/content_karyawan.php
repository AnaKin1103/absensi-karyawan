<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include('../config/koneksi.php');

$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';


$query = "SELECT u.*, k.nama_lengkap, k.nip, k.email, k.no_tlp 
          FROM user u 
          LEFT JOIN karyawan k ON u.id_user = k.id_user 
          WHERE u.role='karyawan'";

if (!empty($search)) {
    $query .= " AND (u.nama LIKE '%$search%' OR u.username LIKE '%$search%' OR k.nama_lengkap LIKE '%$search%' OR k.nip LIKE '%$search%')";
}

$query .= " ORDER BY u.nama ASC";
$karyawan = mysqli_query($koneksi, $query);
$total_karyawan = mysqli_num_rows($karyawan);
?>
<div class="page-header">
    <div class="header-content">
        <h3>Data Karyawan</h3>
        <p class="subtitle">Total: <?= $total_karyawan ?> Karyawan</p>
    </div>
    <div class="header-actions">
        <a href="tambah_karyawan.php" class="btn btn-primary">
            <span>➕</span> Tambah Karyawan
        </a>
    </div>
</div>

<!-- Search Bar -->
<div class="search-container">
    <form method="GET" action="" class="search-form">
        <input type="hidden" name="page" value="karyawan">
        <div class="search-input-group">
            <input 
                type="text" 
                name="search" 
                class="search-input" 
                placeholder="🔍 Cari nama, username, atau NIP karyawan..." 
                value="<?= htmlspecialchars($search) ?>"
            >
            <button type="submit" class="btn btn-search">Cari</button>
            <?php if(!empty($search)): ?>
                <a href="?page=karyawan" class="btn btn-reset">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Tabel Karyawan -->
<div class="card table-card">
    <div class="card-body">
        <?php if($total_karyawan > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Kontak</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($row = mysqli_fetch_assoc($karyawan)): 
                        ?>
                        <tr>
                            <td data-label="No"><?= $no++ ?></td>
                            <td data-label="NIP">
                                <span class="badge badge-secondary"><?= htmlspecialchars($row['nip'] ?? '-') ?></span>
                            </td>
                            <td data-label="Nama Lengkap">
                                <div class="employee-info">
                                    <span class="employee-avatar">
                                        <?= strtoupper(substr($row['nama_lengkap'] ?? $row['nama'], 0, 2)) ?>
                                    </span>
                                    <strong><?= htmlspecialchars($row['nama_lengkap'] ?? $row['nama']) ?></strong>
                                </div>
                            </td>
                            <td data-label="Username">
                                <span class="badge badge-info">@<?= htmlspecialchars($row['username']) ?></span>
                            </td>
                            <td data-label="Kontak">
                                <div style="font-size: 0.9em;">
                                    <?php if(!empty($row['email'])): ?>
                                        <div><?= htmlspecialchars($row['email']) ?></div>
                                    <?php endif; ?>
                                    <?php if(!empty($row['no_tlp'])): ?>
                                        <div><?= htmlspecialchars($row['no_tlp']) ?></div>
                                    <?php endif; ?>
                                    <?php if(empty($row['email']) && empty($row['no_tlp'])): ?>
                                        <span style="color: #999;">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td data-label="Aksi">
                                <div class="action-buttons">
                                    <a href="edit_karyawan.php?id_user=<?= $row['id_user'] ?>" 
                                       class="btn btn-warning btn-sm" 
                                       title="Edit Karyawan">Edit
                                    </a>
                                    <a href="hapus_karyawan.php?id_user=<?= $row['id_user'] ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Yakin ingin menghapus karyawan <?= htmlspecialchars($row['nama_lengkap'] ?? $row['nama']) ?>?')"
                                       title="Hapus Karyawan">Hapus
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
                <p><?= !empty($search) ? "Tidak ada hasil untuk pencarian \"$search\"" : "Belum ada data karyawan yang terdaftar" ?></p>
                <?php if(empty($search)): ?>
                    <a href="tambah_karyawan.php" class="btn btn-primary">Tambah Karyawan Pertama</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.85em;
    font-weight: 600;
}

.badge-info {
    background: #3498db;
    color: white;
}

.badge-secondary {
    background: #95a5a6;
    color: white;
}

.employee-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.employee-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.85em;
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
    }
    
    .table td::before {
        content: attr(data-label);
        font-weight: bold;
        margin-right: 10px;
    }
    
    .action-buttons {
        flex-direction: column;
        width: 100%;
    }
}
</style>