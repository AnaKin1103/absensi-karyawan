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

// Check apakah sudah absen hari ini
$today = date('Y-m-d');
$check = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_user='$id_user' AND tanggal='$today'");

if(mysqli_num_rows($check) > 0) {
    $message = "Anda sudah melakukan check-in hari ini!";
    $message_type = "warning";
} else {
    $message = "";
    $message_type = "";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-In - Absensi Masuk</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/checkin.css">
</head>
<body>
    <div class="layout">
        <button class="hamburger" id="hamburgerBtn" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div class="overlay" id="overlay"></div>
        <aside class="sidebar" id="sidebar">
            <h3 class="sidebar-title">Dashboard Karyawan</h3>
            <nav>
                <a href="karyawan_dashboard.php" class="sidebar-link">
                    <span class="icon">🏠</span> Dashboard
                </a>
                <a href="karyawan_dashboard.php?page=riwayat" class="sidebar-link">
                    <span class="icon">📋</span> Riwayat
                </a>
                <a href="../logout.php" class="sidebar-link logout">
                    <span class="icon">⬅️</span> Keluar
                </a>
            </nav>
        </aside>
        <main class="content" id="mainContent">
            <div class="checkin-container">
                <div class="page-header" style="text-align: center; margin-bottom: 30px;">
                    <h3>Check-In Absensi</h3>
                    <p class="subtitle">Absensi Masuk Kerja</p>
                </div>
                <!-- Info Box -->
                <div class="info-box">
                    <h3>Halo, <?= htmlspecialchars($display_name) ?>!</h3>
                    <div class="time" id="currentTime">00:00:00</div>
                    <div class="date" id="currentDate">Loading...</div>
                </div>
                <!-- Alert Messages -->
                <?php if(!empty($message)): ?>
                    <div class="alert alert-<?= $message_type ?>">
                        <?= $message ?>
                        <br><br>
                        <a href="karyawan_dashboard.php" class="btn btn-secondary" style="text-decoration: none;">
                            ← Kembali ke Dashboard
                        </a>
                    </div>
                <?php else: ?>
                <!-- Location Info -->
                <div class="location-info" id="locationInfo">
                    <strong>Lokasi GPS:</strong> <span id="locationStatus">Mengambil lokasi...</span>
                </div>
                <!-- Form Check-In -->
                <form id="checkinForm" action="proses_checkin.php" method="POST" enctype="multipart/form-data">
                    <!-- Camera Section -->
                    <div class="card" style="margin-bottom: 20px;">
                        <div class="card-header">
                            <h5>Foto Absensi</h5>
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
                                <button type="button" class="btn btn-capture" id="captureBtn" style="display: none;">
                                    Ambil Foto
                                </button>
                                <button type="button" class="btn btn-retake" id="retakeBtn" style="display: none;">
                                    Foto Ulang
                                </button>
                            </div>
                            
                            <input type="hidden" name="foto" id="fotoInput">
                        </div>
                    </div>
                    <!-- Keterangan -->
                    <div class="form-group">
                        <label for="keterangan">Keterangan (Opsional)</label>
                        <textarea 
                            name="keterangan" 
                            id="keterangan" 
                            class="form-control" 
                            placeholder="Contoh: WFH, Dinas Luar, dll..."
                        ></textarea>
                    </div>
                    <!-- Hidden Inputs -->
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="nama" value="<?= htmlspecialchars($display_name) ?>">
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" id="submitBtn" style="width: 100%;" disabled>
                        Submit Check-In
                    </button>
                    
                    <a href="karyawan_dashboard.php" class="btn btn-secondary" style="width: 100%; margin-top: 10px; text-decoration: none; display: block; text-align: center;">
                        ← Kembali
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
        
        // Get GPS Location
        const locationInfo = document.getElementById('locationInfo');
        const locationStatus = document.getElementById('locationStatus');
        const latInput = document.getElementById('latitude');
        const lonInput = document.getElementById('longitude');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    latInput.value = lat;
                    lonInput.value = lon;
                    
                    locationStatus.innerHTML = `(${lat.toFixed(6)}, ${lon.toFixed(6)})`;
                    locationInfo.classList.remove('error');
                    locationInfo.classList.add('success');
                    
                    checkFormReady();
                },
                function(error) {
                    locationStatus.innerHTML = '❌ Gagal mendapatkan lokasi. Aktifkan GPS Anda!';
                    locationInfo.classList.add('error');
                    alert('GPS diperlukan untuk absensi. Mohon aktifkan lokasi pada browser Anda.');
                }
            );
        } else {
            locationStatus.innerHTML = '❌ Browser tidak mendukung GPS';
            locationInfo.classList.add('error');
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
        
        startBtn.addEventListener('click', async function() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'user' },
                    audio: false 
                });
                
                camera.srcObject = stream;
                camera.style.display = 'block';
                placeholder.style.display = 'none';
                
                startBtn.style.display = 'none';
                captureBtn.style.display = 'inline-block';
                
            } catch(err) {
                alert('Tidak dapat mengakses kamera: ' + err.message);
            }
        });
        
        captureBtn.addEventListener('click', function() {
            canvas.width = camera.videoWidth;
            canvas.height = camera.videoHeight;
            canvas.getContext('2d').drawImage(camera, 0, 0);
            
            const imageData = canvas.toDataURL('image/jpeg');
            fotoInput.value = imageData;
            
            preview.src = imageData;
            preview.style.display = 'block';
            camera.style.display = 'none';
            
            captureBtn.style.display = 'none';
            retakeBtn.style.display = 'inline-block';
            
            // Stop camera stream
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            
            checkFormReady();
        });
        
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
            
            if (hasLocation && hasPhoto) {
                document.getElementById('submitBtn').disabled = false;
            }
        }
        
        // Form validation
        document.getElementById('checkinForm').addEventListener('submit', function(e) {
            if (!latInput.value || !lonInput.value) {
                e.preventDefault();
                alert('Lokasi GPS diperlukan untuk check-in!');
                return false;
            }
            
            if (!fotoInput.value) {
                e.preventDefault();
                alert('Foto diperlukan untuk check-in!');
                return false;
            }
            
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = '⏳ Memproses...';
        });
    </script>
</body>
</html>