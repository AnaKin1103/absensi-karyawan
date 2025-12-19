<?php
session_start();
include('../config/koneksi.php');

// Proteksi karyawan
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'karyawan') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil data lengkap user dari database
$user_query = mysqli_query($koneksi, "SELECT u.nama, u.username, k.nama_lengkap 
                                       FROM user u 
                                       LEFT JOIN karyawan k ON u.id_user = k.id_user 
                                       WHERE u.id_user='$id_user'");
$user_data = mysqli_fetch_assoc($user_query);

// Gunakan nama_lengkap dari tabel karyawan jika ada, kalau tidak pakai nama dari user
$display_name = !empty($user_data['nama_lengkap']) ? $user_data['nama_lengkap'] : $user_data['nama'];

// Check apakah sudah check-in hari ini
$today = date('Y-m-d');
$check = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_user='$id_user' AND tanggal='$today'");

$can_checkout = false;
$message = "";
$message_type = "";
$absen_data = null;

if(mysqli_num_rows($check) == 0) {
    $message = "Anda belum melakukan check-in hari ini!";
    $message_type = "error";
} else {
    $absen_data = mysqli_fetch_assoc($check);
    
    if(!empty($absen_data['jam_keluar'])) {
        $message = "Anda sudah melakukan check-out hari ini pada pukul " . $absen_data['jam_keluar'];
        $message_type = "warning";
    } else {
        $can_checkout = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-Out - Absensi Pulang</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/checkout.css">
</head>
<body>
    <div class="layout">
        <!-- Hamburger Menu -->
        <button class="hamburger" id="hamburgerBtn" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Overlay -->
        <div class="overlay" id="overlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <h3 class="sidebar-title">Dashboard Karyawan</h3>
            <nav>
                <a href="karyawan_dashboard.php" class="sidebar-link">
                    <span class="icon">🏠</span> Dashboard
                </a>
                <a href="karyawan_dashboard.php?page=riwayat" class="sidebar-link">
                    <span class="icon">📋</span> Riwayat Absensi
                </a>
                <a href="../logout.php" class="sidebar-link logout">
                    <span class="icon">⬅️</span> Keluar
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="content" id="mainContent">
            <div class="checkout-container">
                <!-- Page Header -->
                <div class="page-header">
                    <h3>Check-Out Absensi</h3>
                    <p class="subtitle">Absensi Pulang Kerja</p>
                </div>

                <!-- Info Box -->
                <div class="info-box">
                    <h3>Sampai Jumpa, <?= htmlspecialchars($display_name) ?>! 👋</h3>
                    <div class="time" id="currentTime">00:00:00</div>
                    <div class="date" id="currentDate">Loading...</div>
                </div>

                <!-- Alert Messages -->
                <?php if(!empty($message)): ?>
                    <div class="alert alert-<?= $message_type ?>">
                        <span class="icon">
                            <?php 
                            if($message_type == 'error') echo '❌';
                            elseif($message_type == 'warning') echo '⚠️';
                            else echo 'ℹ️';
                            ?>
                        </span>
                        <div>
                            <?= $message ?>
                            <br><br>
                            <a href="karyawan_dashboard.php" class="btn btn-secondary" style="text-decoration: none;">
                                ← Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($can_checkout): ?>
                
                <!-- Summary Check-In -->
                <div class="summary-box">
                    <h5>Ringkasan Hari Ini</h5>
                    <div class="summary-item">
                        <label>Waktu Check-In:</label>
                        <span class="value"><?= date('H:i:s', strtotime($absen_data['jam_masuk'])) ?></span>
                    </div>
                    <div class="summary-item">
                        <label>Durasi Kerja:</label>
                        <span class="value live" id="durasi">Menghitung...</span>
                    </div>
                    <?php if(!empty($absen_data['keterangan'])): ?>
                    <div class="summary-item">
                        <label>Keterangan Masuk:</label>
                        <span class="value" style="font-size: 1rem; color: #2c3e50;">
                            <?= htmlspecialchars($absen_data['keterangan']) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Location Info -->
                <div class="location-info" id="locationInfo">
                    <strong>Lokasi GPS:</strong> <span id="locationStatus">Mengambil lokasi...</span>
                </div>

                <!-- Form Check-Out -->
                <form id="checkoutForm" action="proses_checkout.php" method="POST" enctype="multipart/form-data">
                    <!-- Camera Section -->
                    <div class="card" style="margin-bottom: 25px;">
                        <div class="card-header">
                            <h5>Foto Absensi Pulang</h5>
                        </div>
                        <div class="card-body">
                            <div class="camera-container">
                                <video id="camera" autoplay playsinline></video>
                                <canvas id="canvas"></canvas>
                                <img id="preview" alt="Preview">
                                <div class="camera-placeholder" id="cameraPlaceholder">
                                    <h4>📷</h4>
                                    <p>Klik "Aktifkan Kamera" untuk mengambil foto</p>
                                </div>
                            </div>
                            
                            <div class="camera-controls">
                                <button type="button" class="btn btn-primary" id="startCamera">
                                    Aktifkan Kamera
                                </button>
                                <button type="button" class="btn-capture" id="captureBtn" style="display: none;">
                                    Ambil Foto
                                </button>
                                <button type="button" class="btn-retake" id="retakeBtn" style="display: none;">
                                    Foto Ulang
                                </button>
                            </div>
                            
                            <input type="hidden" name="foto" id="fotoInput">
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div class="form-group">
                        <label for="keterangan">Keterangan Check-Out (Opsional)</label>
                        <textarea 
                            name="keterangan" 
                            id="keterangan" 
                            class="form-control" 
                            placeholder="Contoh: Lembur, Pulang Tepat Waktu, Ada Kendala, dll..."
                        ></textarea>
                        <small style="color: #7f8c8d; margin-top: 5px; display: block;">
                            * Keterangan akan ditambahkan ke catatan absensi hari ini
                        </small>
                    </div>

                    <!-- Hidden Inputs -->
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="id_absensi" value="<?= $absen_data['id_absensi'] ?>">

                    <!-- Submit Button -->
                    <button type="submit" class="btn-checkout" id="submitBtn" disabled>
                        Submit Check-Out
                    </button>
                    
                    <a href="karyawan_dashboard.php" class="btn btn-secondary" style="width: 100%; margin-top: 15px; text-decoration: none; display: block; text-align: center; padding: 14px;">
                        ← Kembali ke Dashboard
                    </a>
                </form>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/sidebar.js"></script>
    <script>
    // Real-time clock
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
        const dateString = now.toLocaleDateString('id-ID', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        document.getElementById('currentTime').textContent = timeString;
        document.getElementById('currentDate').textContent = dateString;
    }

    updateTime();
    setInterval(updateTime, 1000);

            <?php if($can_checkout): ?>
            // Calculate work duration (Live Counter)
            function updateDuration() {
                const checkinTime = new Date('<?= date('Y-m-d') ?> <?= $absen_data['jam_masuk'] ?>').getTime();
                const now = new Date().getTime();
                const diff = Math.floor((now - checkinTime) / 1000);
                
                const hours = Math.floor(diff / 3600);
                const minutes = Math.floor((diff % 3600) / 60);
                const seconds = diff % 60;
                
                document.getElementById('durasi').textContent = 
                    String(hours).padStart(2, '0') + ':' + 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(seconds).padStart(2, '0');
            }
            
            updateDuration();
            setInterval(updateDuration, 1000);

        // Get GPS Location
        const locationInfo = document.getElementById('locationInfo');
        const locationStatus = document.getElementById('locationStatus');
        const latInput = document.getElementById('latitude');
        const lonInput = document.getElementById('longitude');
        
        if (navigator.geolocation) {
            locationStatus.innerHTML = 'Mengambil lokasi...';
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    const accuracy = position.coords.accuracy;
                    
                    latInput.value = lat;
                    lonInput.value = lon;
                    
                    locationStatus.innerHTML = `Berhasil (${lat.toFixed(6)}, ${lon.toFixed(6)}) - Akurasi: ${Math.round(accuracy)}m`;
                    locationInfo.classList.remove('error');
                    locationInfo.classList.add('success');
                    
                    checkFormReady();
                },
                function(error) {
                    let errorMsg = '';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg = '❌ Izin lokasi ditolak. Mohon izinkan akses lokasi!';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg = '❌ Informasi lokasi tidak tersedia.';
                            break;
                        case error.TIMEOUT:
                            errorMsg = '❌ Waktu permintaan lokasi habis.';
                            break;
                        default:
                            errorMsg = '❌ Gagal mendapatkan lokasi.';
                    }
                    
                    locationStatus.innerHTML = errorMsg + ' <strong>GPS diperlukan untuk check-out!</strong>';
                    locationInfo.classList.add('error');
                    alert('GPS diperlukan untuk absensi check-out. Mohon aktifkan lokasi pada browser Anda dan refresh halaman.');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            locationStatus.innerHTML = '❌ Browser tidak mendukung GPS';
            locationInfo.classList.add('error');
            alert('Browser Anda tidak mendukung GPS. Gunakan browser modern seperti Chrome atau Firefox.');
        }

        // Camera functionality
        const camera = document.getElementById('camera');
        const canvas = document.getElementById('canvas');
        const preview = document.getElementById('preview');
        const placeholder = document.getElementById('cameraPlaceholder');
        const startBtn = document.getElementById('startCamera');
        const captureBtn = document.getElementById('captureBtn');
        const retakeBtn = document.getElementById('retakeBtn');
        const fotoInput = document.getElementById('fotoInput');
        
        let stream = null;
        
        // Start Camera
        startBtn.addEventListener('click', async function() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false 
                });
                
                camera.srcObject = stream;
                camera.style.display = 'block';
                placeholder.style.display = 'none';
                
                startBtn.style.display = 'none';
                captureBtn.style.display = 'inline-block';
                
            } catch(err) {
                console.error('Camera error:', err);
                alert('Tidak dapat mengakses kamera: ' + err.message + '\n\nPastikan:\n1. Browser memiliki izin akses kamera\n2. Kamera tidak digunakan aplikasi lain\n3. Gunakan HTTPS atau localhost');
            }
        });
        
        // Capture Photo
        captureBtn.addEventListener('click', function() {
            canvas.width = camera.videoWidth;
            canvas.height = camera.videoHeight;
            
            const context = canvas.getContext('2d');
            context.drawImage(camera, 0, 0);
            
            // Convert to JPEG with quality
            const imageData = canvas.toDataURL('image/jpeg', 0.8);
            fotoInput.value = imageData;
            
            preview.src = imageData;
            preview.style.display = 'block';
            camera.style.display = 'none';
            
            captureBtn.style.display = 'none';
            retakeBtn.style.display = 'inline-block';
            
            // Stop camera stream
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            
            checkFormReady();
        });
        
        // Retake Photo
        retakeBtn.addEventListener('click', function() {
            preview.style.display = 'none';
            fotoInput.value = '';
            
            retakeBtn.style.display = 'none';
            startBtn.style.display = 'inline-block';
            placeholder.style.display = 'block';
            
            document.getElementById('submitBtn').disabled = true;
        });
        
        // Check if form is ready to submit
        function checkFormReady() {
            const hasLocation = latInput.value && lonInput.value;
            const hasPhoto = fotoInput.value;
            
            const submitBtn = document.getElementById('submitBtn');
            
            if (hasLocation && hasPhoto) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            } else {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
            }
        }
        
        // Form validation and submission
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate location
            if (!latInput.value || !lonInput.value) {
                alert('❌ Lokasi GPS diperlukan untuk check-out!');
                return false;
            }
            
            // Validate photo
            if (!fotoInput.value) {
                alert('❌ Foto diperlukan untuk check-out!');
                return false;
            }
            
            // Confirm submission
            if (!confirm('Apakah Anda yakin ingin melakukan check-out sekarang?')) {
                return false;
            }
            
            // Disable button and show loading
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Memproses Check-Out...';
            
            // Submit form
            this.submit();
        });
        <?php endif; ?>
        
        // Prevent accidental page leave
        <?php if($can_checkout): ?>
        window.addEventListener('beforeunload', function(e) {
            const hasPhoto = document.getElementById('fotoInput').value;
            if (hasPhoto && !document.getElementById('submitBtn').disabled) {
                e.preventDefault();
                e.returnValue = 'Anda belum submit check-out. Yakin ingin meninggalkan halaman?';
                return e.returnValue;
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>