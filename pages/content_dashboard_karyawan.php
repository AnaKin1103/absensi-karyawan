<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include('../config/koneksi.php');

$id_user = $_SESSION['id_user'];
$nama = $_SESSION['nama'];
$username = $_SESSION['username'];

// Ambil data lengkap user dari database
$user_query = mysqli_query($koneksi, "SELECT u.nama, u.username, k.nama_lengkap 
                                       FROM user u 
                                       LEFT JOIN karyawan k ON u.id_user = k.id_user 
                                       WHERE u.id_user='$id_user'");
$user_data = mysqli_fetch_assoc($user_query);

// Gunakan nama_lengkap dari tabel karyawan jika ada, kalau tidak pakai nama dari user
$display_name = !empty($user_data['nama_lengkap']) ? $user_data['nama_lengkap'] : $user_data['nama'];

// Check absensi hari ini
$today = date('Y-m-d');
$check_today = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_user='$id_user' AND tanggal='$today'");
$absen_today = mysqli_fetch_assoc($check_today);

// Statistik bulan ini
$bulan_ini = date('Y-m');
$stats_query = mysqli_query($koneksi, "
    SELECT 
        COUNT(*) as total_hadir,
        SUM(CASE WHEN jam_keluar IS NOT NULL THEN 1 ELSE 0 END) as lengkap,
        SUM(CASE WHEN jam_keluar IS NULL THEN 1 ELSE 0 END) as belum_pulang
    FROM absensi 
    WHERE id_user='$id_user' 
    AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini'
");
$stats = mysqli_fetch_assoc($stats_query);

// Status check-in & check-out hari ini
$sudah_checkin = $absen_today ? true : false;
$sudah_checkout = ($absen_today && !empty($absen_today['jam_keluar'])) ? true : false;
?>

<!-- Alert Messages -->
<?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <span style="font-size: 1.5rem;">✅</span>
        <span><?= $_SESSION['success'] ?></span>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <span style="font-size: 1.5rem;">❌</span>
        <span><?= $_SESSION['error'] ?></span>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['warning'])): ?>
    <div class="alert alert-warning">
        <span style="font-size: 1.5rem;">⚠️</span>
        <span><?= $_SESSION['warning'] ?></span>
    </div>
    <?php unset($_SESSION['warning']); ?>
<?php endif; ?>

<!-- Welcome Banner -->
<div class="welcome-banner">
    <h2>Selamat Datang, <?= htmlspecialchars($display_name) ?>!</h2>
    <p class="subtitle" id="currentDate">Loading...</p>
    <div class="current-time" id="currentTime">00:00:00</div>
</div>

<!-- Statistik Bulan Ini -->
<div class="stats-grid">
    <div class="stat-box info">
        <div class="stat-icon"></div>
        <div class="stat-value"><?= $stats['total_hadir'] ?></div>
        <div class="stat-label">Total Hadir Bulan Ini</div>
    </div>
    
    <div class="stat-box success">
        <div class="stat-icon"></div>
        <div class="stat-value"><?= $stats['lengkap'] ?></div>
        <div class="stat-label">Absensi Lengkap</div>
    </div>
    
    <div class="stat-box warning">
        <div class="stat-icon"></div>
        <div class="stat-value"><?= $stats['belum_pulang'] ?></div>
        <div class="stat-label">Belum Check-Out</div>
    </div>
</div>

<!-- Quick Actions: Check-In & Check-Out -->
<div class="quick-actions">
    <!-- Check-In Card -->
    <div class="action-card <?= $sudah_checkin ? 'disabled' : '' ?>">
        <div class="card-body">
            <div class="action-icon">
                <?= $sudah_checkin ? '✅' : '' ?>
            </div>
            <h4 class="action-title">Check-In</h4>
            <p class="action-desc">Absensi Masuk Kerja</p>
            
            <?php if(!$sudah_checkin): ?>
                <a href="checkin.php" class="btn btn-primary" style="text-decoration: none; width: 100%;">
                    Check-In Sekarang
                </a>
            <?php else: ?>
                <div class="action-status status-active">
                    Sudah Check-In<br>
                    <strong style="font-size: 1.3rem;"><?= $absen_today['jam_masuk'] ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Check-Out Card -->
    <div class="action-card <?= (!$sudah_checkin || $sudah_checkout) ? 'disabled' : '' ?>">
        <div class="card-body">
            <div class="action-icon">
                <?= $sudah_checkout ? '✅' : '' ?>
            </div>
            <h4 class="action-title">Check-Out</h4>
            <p class="action-desc">
                <?php 
                if($sudah_checkout) {
                    echo 'Absensi Pulang Kerja';
                } elseif(!$sudah_checkin) {
                    echo 'Check-In Terlebih Dahulu';
                } else {
                    echo 'Absensi Pulang Kerja';
                }
                ?>
            </p>
            
            <?php if($sudah_checkin && !$sudah_checkout): ?>
                <a href="checkout.php" class="btn btn-warning" style="text-decoration: none; width: 100%; background: #f39c12;">
                    Check-Out Sekarang
                </a>
            <?php elseif($sudah_checkout): ?>
                <div class="action-status status-active">
                    Sudah Check-Out<br>
                    <strong style="font-size: 1.3rem;"><?= $absen_today['jam_keluar'] ?></strong>
                </div>
            <?php else: ?>
                <div class="action-status status-inactive">
                    Tidak Tersedia
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Status Absensi Hari Ini -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-header">
        <h5>Status Absensi Hari Ini</h5>
    </div>
    <div class="card-body">
        <?php if($absen_today): ?>
            <div class="detail-grid">
                <div class="detail-item detail-card">
                    <label>Jam Masuk</label>
                    <div class="value"><?= $absen_today['jam_masuk'] ?: '-' ?></div>
                </div>
                
                <div class="detail-item <?= $absen_today['jam_keluar'] ? 'detail-card' : 'warning-card' ?>">
                    <label>Jam Keluar</label>
                    <div class="value"><?= $absen_today['jam_keluar'] ?: 'Belum Check-Out' ?></div>
                </div>
                
                <div class="detail-item info-card">
                    <label>Status</label>
                    <div class="value">
                        <?php if($absen_today['jam_keluar']): ?>
                            <span class="badge badge-success">Lengkap</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Sedang Bekerja</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if($absen_today['keterangan']): ?>
                <div class="detail-item">
                    <label>Keterangan</label>
                    <div class="value" style="font-size: 0.95rem;">
                        <?= htmlspecialchars($absen_today['keterangan']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php
            // Hitung durasi kerja
            if($absen_today['jam_masuk']) {
                $masuk = strtotime($today . ' ' . $absen_today['jam_masuk']);
                
                if($absen_today['jam_keluar']) {
                    $keluar = strtotime($today . ' ' . $absen_today['jam_keluar']);
                } else {
                    $keluar = time();
                }
                
                $diff = $keluar - $masuk;
                $jam = floor($diff / 3600);
                $menit = floor(($diff % 3600) / 60);
                $detik = $diff % 60;
            ?>
            <div class="duration-box">
                <div class="duration-label">
                    <?= $absen_today['jam_keluar'] ? 'TOTAL DURASI KERJA' : 'DURASI KERJA SAAT INI' ?>
                </div>
                <div class="duration-value" id="durationDisplay">
                    <?= sprintf('%02d:%02d:%02d', $jam, $menit, $detik) ?>
                </div>
            </div>
            
            <?php if(!$absen_today['jam_keluar']): ?>
            <script>
                // Live duration counter
                let startTime = new Date('<?= $today ?> <?= $absen_today['jam_masuk'] ?>').getTime();
                
                setInterval(function() {
                    let now = new Date().getTime();
                    let diff = Math.floor((now - startTime) / 1000);
                    
                    let hours = Math.floor(diff / 3600);
                    let minutes = Math.floor((diff % 3600) / 60);
                    let seconds = diff % 60;
                    
                    document.getElementById('durationDisplay').textContent = 
                        String(hours).padStart(2, '0') + ':' + 
                        String(minutes).padStart(2, '0') + ':' + 
                        String(seconds).padStart(2, '0');
                }, 1000);
            </script>
            <?php endif; ?>
            <?php } ?>
            
        <?php else: ?>
            <div class="empty-state" style="padding: 60px 20px;">
                <div class="empty-icon" style="font-size: 4rem;"></div>
                <h4 style="margin: 15px 0 10px 0;">Belum Absen Hari Ini</h4>
                <p style="color: #7f8c8d; margin-bottom: 20px;">
                    Silakan lakukan check-in terlebih dahulu untuk memulai absensi
                </p>
                <a href="checkin.php" class="btn btn-primary" style="text-decoration: none;">
                    Check-In Sekarang
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>