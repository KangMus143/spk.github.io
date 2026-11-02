<!-- NIM: 2203010233, nama: Ihsan Musyaffa, topik: Sistem Pendukung Keputusan Pemilihan Konsentrasi -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK Pemilihan Konsentrasi - Fuzzy Mamdani</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/home.css">
</head>
<body class="home-page">
    <header class="site-header">
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/images/logo_unper.png" alt="Logo UNPER" class="me-2">
                <span>Universitas Perjuangan Tasikmalaya</span>
            </a>
            <button class="navbar-toggler" type="button" data-nav-toggle="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Buka menu navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link aktif" href="index.php" aria-current="page">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#petunjuk">Petunjuk</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary text-white ms-3 px-4" href="pages/input.php">Mulai SPK</a></li>
                    <li class="nav-item"><a class="nav-link ms-2" href="pages/admin/login.php">Login Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>
    </header>

    <main class="site-main">
    <!-- Hero Section -->
    <section class="hero text-center">
        <div class="container">
            <div class="hero-logo mb-4 d-inline-block bg-white rounded-circle p-3 shadow">
                <img src="assets/images/logo_unper.png" alt="Logo UNPER">
            </div>
            <h1 class="display-4 fw-bold">Sistem Pendukung Keputusan<br>Pemilihan Konsentrasi</h1>
            <p class="lead mb-4">Metode Fuzzy Mamdani untuk rekomendasi Mata kuliah pilihan yang akurat dan objektif.</p>
            <a href="pages/input.php" class="btn btn-light btn-lg shadow-sm fw-bold text-primary">Mulai Analisis Sekarang</a>
        </div>
    </section>

    <!-- Petunjuk Penggunaan -->
    <section id="petunjuk" class="guide-section bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Petunjuk Penggunaan</h2>
                <p class="text-muted guide-subtitle">Ikuti langkah mudah berikut untuk mendapatkan rekomendasi</p>
            </div>
            <div class="row g-4 text-center">
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm py-4">
                        <div class="card-body">
                            <div class="feature-icon"><i class="fas fa-search"></i></div>
                            <h3>1. Cari NIM/Nama</h3>
                            <p class="text-muted small">Cari data Anda menggunakan dropdown yang tersedia.</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm py-4">
                        <div class="card-body">
                            <div class="feature-icon"><i class="fas fa-clipboard-check"></i></div>
                            <h3>2. Cek Data Akademik</h3>
                            <p class="text-muted small">Sistem otomatis mengisi IPK dan Nilai Akademik Anda.</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm py-4">
                        <div class="card-body">
                            <div class="feature-icon"><i class="fas fa-sliders-h"></i></div>
                            <h3>3. Input Preferensi</h3>
                            <p class="text-muted small">Masukkan nilai Minat dan Komitmen Belajar (1-100).</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card h-100 border-0 shadow-sm py-4">
                        <div class="card-body">
                            <div class="feature-icon"><i class="fas fa-poll"></i></div>
                            <h3>4. Lihat Hasil</h3>
                            <p class="text-muted small">Dapatkan rekomendasi konsentrasi terbaik untuk Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-5 home-features">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-12 col-md-6">
                    <h3 class="fw-bold mb-4">Mengapa Menggunakan Sistem Ini?</h3>
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> <strong>Objektif:</strong> Perhitungan matematis tanpa bias subjektif.</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> <strong>Cepat:</strong> Hasil langsung keluar dalam hitungan detik.</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> <strong>Visual:</strong> Dilengkapi grafik untuk memudahkan perbandingan.</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i> <strong>Rekomendasi Tepat:</strong> Berdasarkan aturan pakar akademik.</li>
                    </ul>
                </div>
                <div class="col-12 col-md-6 text-center">
                    <div class="feature-panel bg-light rounded shadow-sm">
                        <i class="fas fa-chart-line text-primary"></i>
                        <h4 class="text-muted mt-3">Analisis Cerdas</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

    </main>

    <footer class="site-footer">
        <div class="container">
            <p class="mb-0">&copy; 2026 Universitas Perjuangan Tasikmalaya. All rights reserved.</p>
            <small class="text-muted">Sistem Pendukung Keputusan - Metode Fuzzy Mamdani</small>
        </div>
    </footer>

    <script src="assets/js/ui-native.js"></script>
</body>
</html>