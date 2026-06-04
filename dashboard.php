<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Fotobooth Live</title>

<style>

    :root {
        --bg-color: #0f172a;
        --card-bg: #1e293b;
        --primary: #4f46e5;
        --primary-hover: #3730a3;
        --danger: #ef4444;
        --success: #22c55e;
        --text-color: #f8fafc;
    }

    body{
        margin:0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background:var(--bg-color);
        color:var(--text-color);
        overflow-x: hidden;
    }

    /* Navbar */
    .navbar{
        background:var(--card-bg);
        padding:15px 30px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .brand h2 {
        margin: 0;
        font-size: 1.5rem;
    }

    .logout{
        text-decoration:none;
        color:white;
        background:var(--danger);
        padding:10px 20px;
        border-radius:8px;
        font-weight: bold;
        transition: background 0.3s;
    }

    .logout:hover{
        background:#dc2626;
    }

    /* Container Utama */
    .container{
        padding:40px 20px;
        max-width: 1000px;
        margin: auto;
        text-align: center;
    }

    .header-title h1 {
        margin-bottom: 5px;
    }

    .header-title p {
        color: #94a3b8;
        margin-top: 0;
    }

    /* Area Kamera / Preview */
    .camera-wrapper {
        position: relative;
        width: 100%;
        max-width: 640px;
        margin: 0 auto 30px auto;
        border-radius: 20px;
        overflow: hidden;
        background: #000;
        box-shadow: 0 0 30px rgba(79, 70, 229, 0.2);
        border: 4px solid var(--card-bg);
        aspect-ratio: 4/3;
    }

    video, canvas {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* 1. Video LIVE di-mirror agar user mudah memposisikan diri */
    .camera-wrapper video {
        transform: scaleX(-1);
    }

    /* 2. Canvas HASIL FOTO tidak di-mirror (Normal/Tidak Mirror) */
    .camera-wrapper canvas {
        display: none;
        /* Tidak ada transform: scaleX(-1) di sini */
    }

    /* Efek Flash */
    .flash-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: white;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.1s ease-out;
        z-index: 10;
    }

    /* Tombol Kontrol */
    .controls-area {
        background: var(--card-bg);
        padding: 20px;
        border-radius: 15px;
        display: inline-block;
        margin-bottom: 30px;
    }

    .action-btn {
        border: none;
        padding: 15px 30px;
        border-radius: 50px;
        font-size: 1rem;
        cursor: pointer;
        color: white;
        font-weight: bold;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: transform 0.2s, background 0.3s;
        margin: 0 5px;
    }

    .action-btn:active {
        transform: scale(0.95);
    }

    .btn-capture {
        background: var(--primary);
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
    }

    .btn-capture:hover {
        background: var(--primary-hover);
    }

    .btn-download {
        background: var(--success);
        display: none;
    }

    .btn-download:hover {
        background: #16a34a;
    }

    .btn-retake {
        background: #64748b;
        display: none;
    }

    .btn-retake:hover {
        background: #475569;
    }

    /* Filter Group */
    .filter-section {
        margin-top: 20px;
    }

    .filter-label {
        display: block;
        margin-bottom: 15px;
        font-size: 1.1rem;
        color: #cbd5e1;
    }

    .filter-group{
        display:flex;
        justify-content:center;
        gap:10px;
        flex-wrap:wrap;
    }

    .filter-btn{
        border:none;
        padding:10px 20px;
        border-radius:8px;
        cursor:pointer;
        background:#334155;
        color:white;
        transition: all 0.3s;
    }

    .filter-btn:hover, .filter-btn.active {
        background: var(--primary);
        transform: translateY(-2px);
    }

    /* Toast Notification */
    #toast {
        visibility: hidden;
        min-width: 250px;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 8px;
        padding: 16px;
        position: fixed;
        z-index: 999;
        left: 50%;
        bottom: 30px;
        transform: translateX(-50%);
        font-size: 17px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    #toast.show {
        visibility: visible;
        animation: fadein 0.5s, fadeout 0.5s 2.5s;
    }

    @keyframes fadein {
        from {bottom: 0; opacity: 0;}
        to {bottom: 30px; opacity: 1;}
    }

    @keyframes fadeout {
        from {bottom: 30px; opacity: 1;}
        to {bottom: 0; opacity: 0;}
    }

    /* Responsiveness */
    @media (max-width: 600px) {
        .navbar {
            flex-direction: column;
            gap: 10px;
        }
        .filter-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .action-btn {
            width: 100%;
            justify-content: center;
            margin-bottom: 10px;
        }
    }
</style>
</head>
<body>

<div class="navbar">
    <div class="brand">
        <h2>📸 FOTOBOOTH SEKOLAH</h2>
    </div>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="container">
    
    <div class="header-title">
        <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['nama']); ?> 👋</h1>
        <p>Siap untuk bersenang-senang? Pilih filter dan ambil fotomu!</p>
    </div>

    <!-- Area Kamera -->
    <div class="camera-wrapper">
        <video id="video" autoplay playsinline></video>
        <canvas id="canvas"></canvas>
        <div class="flash-overlay" id="flash"></div>
    </div>

    <!-- Tombol Aksi -->
    <div class="controls-area">
        <button id="btnCapture" class="action-btn btn-capture">
            📷 Ambil Foto
        </button>
        
        <button id="btnRetake" class="action-btn btn-retake">
            🔄 Foto Ulang
        </button>

        <a id="btnDownload" class="action-btn btn-download" download="fotobooth.png">
            💾 Simpan Foto
        </a>
    </div>

    <!-- Pilihan Filter -->
    <div class="filter-section">
        <span class="filter-label">Pilih Efek:</span>
        <div class="filter-group">
            <button class="filter-btn active" onclick="setFilter('none', this)">Normal</button>
            <button class="filter-btn" onclick="setFilter('grayscale(100%)', this)">Hitam Putih</button>
            <button class="filter-btn" onclick="setFilter('sepia(100%)', this)">Vintage</button>
            <button class="filter-btn" onclick="setFilter('blur(3px)', this)">Blur</button>
            <button class="filter-btn" onclick="setFilter('brightness(130%)', this)">Terang</button>
            <button class="filter-btn" onclick="setFilter('contrast(150%)', this)">Kontras</button>
            <button class="filter-btn" onclick="setFilter('hue-rotate(90deg)', this)">Hijau</button>
            <button class="filter-btn" onclick="setFilter('invert(100%)', this)">Negatif</button>
        </div>
    </div>

</div>

<!-- Toast Notification Element -->
<div id="toast">Foto berhasil disimpan! 🎉</div>

<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const btnCapture = document.getElementById('btnCapture');
    const btnRetake = document.getElementById('btnRetake');
    const btnDownload = document.getElementById('btnDownload');
    const flash = document.getElementById('flash');
    const toast = document.getElementById('toast');
    
    let currentFilter = 'none';
    let stream = null;

    // 1. Mengakses Kamera Web
    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: "user"
                }, 
                audio: false 
            });
            video.srcObject = stream;
        } catch (err) {
            console.error("Error akses kamera:", err);
            alert("Tidak dapat mengakses kamera. Pastikan izin diberikan.");
        }
    }

    startCamera();

    // 2. Fungsi Filter CSS
    function setFilter(filterValue, btnElement) {
        currentFilter = filterValue;
        video.style.filter = filterValue;
        canvas.style.filter = filterValue;

        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        btnElement.classList.add('active');
    }

    // 3. Fungsi Capture
    btnCapture.addEventListener('click', () => {
        flash.style.opacity = 1;
        setTimeout(() => { flash.style.opacity = 0; }, 100);

        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        // --- PERUBAHAN PENTING DISINI ---
        // Karena video di-mirror oleh CSS, gambar yang diambil oleh canvas
        // sebenarnya adalah gambar ASLI (kiri/kanan benar).
        // Jadi, kita TIDAK perlu melakukan flip manual di sini.
        // Hasil canvas akan normal (tidak mirror).
        
        // Flip manual agar hasil sesuai tampilan video yang sudah di mirror
        context.translate(canvas.width, 0);
        context.scale(-1, 1);

        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        // -------------------------------

        video.style.display = 'none';
        canvas.style.display = 'block';

        btnCapture.style.display = 'none';
        btnRetake.style.display = 'inline-flex';
        btnDownload.style.display = 'inline-flex';
        
        showToast("Foto diambil! ✨");
    });

    // 4. Fungsi Retake
    btnRetake.addEventListener('click', () => {
        canvas.style.display = 'none';
        video.style.display = 'block';

        btnRetake.style.display = 'none';
        btnDownload.style.display = 'none';
        btnCapture.style.display = 'inline-flex';
    });

    // 5. Fungsi Download
    btnDownload.addEventListener('click', (e) => {
        const context = canvas.getContext('2d');
        const tempCanvas = document.createElement('canvas');
        const tempCtx = tempCanvas.getContext('2d');
        
        tempCanvas.width = canvas.width;
        tempCanvas.height = canvas.height;

        // Terapkan Filter
        tempCtx.filter = currentFilter;
        
        // --- PERUBAHAN PENTING DISINI ---
        // Agar hasil download TIDAK MIRROR, kita JANGAN lakukan translate/scale(-1).
        // Gambar yang ada di canvas saat ini sudah benar secara visual (normal),
        // tapi isinya pixelnya masih terbalik karena kita melakukan scale(-1) saat capture.
        // Jadi, saat download, kita perlu membaliknya lagi ke aslinya.
        
        tempCtx.translate(tempCanvas.width, 0);
        tempCtx.scale(-1, 1);
        // -------------------------------

        tempCtx.drawImage(canvas, 0, 0);

        const dataURL = tempCanvas.toDataURL('image/png');
        e.target.href = dataURL;
        const timestamp = new Date().getTime();
        e.target.download = `foto_${timestamp}.png`;
        
        showToast("Foto sedang didownload... 💾");
    });

    function showToast(message) {
        toast.textContent = message;
        toast.className = "show";
        setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
    }

</script>

</body>
</html>
```