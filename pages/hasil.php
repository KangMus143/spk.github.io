<?php
require_once '../includes/database.php';

if (!isset($_GET['nim'])) {
    die("NIM Mahasiswa tidak ditemukan.");
}

$nim = $_GET['nim'];

// Fetch Data (Join berdasarkan id_mahasiswa)
$stmt = $pdo->prepare("
    SELECT m.nim, m.nama, m.tanggal_input, 
           h.skor_rpl, h.skor_jaringan, h.skor_multimedia, h.rekomendasi_utama
    FROM mahasiswa m 
    JOIN hasil_rekomendasi h ON m.id = h.id_mahasiswa 
    WHERE m.nim = ?
");
$stmt->execute([$nim]);
$data = $stmt->fetch();

if (!$data) {
    die("Data tidak ditemukan.");
}

// Sesuaikan variabel agar cocok dengan HTML & Chart di bawah
$skor_ka = $data['skor_jaringan'];   // KA = Jaringan
$skor_si = $data['skor_rpl'];        // SI = RPL
$skor_mm = $data['skor_multimedia']; // MM = Multimedia

// Tentukan Pemenang (Handle Ties)
$max_score = max($skor_ka, $skor_si, $skor_mm);
$winners = [];
$epsilon = 0.001;

// Hanya hitung pemenang jika skor tertinggi lebih dari 0
if ($max_score > 0) {
    if (abs($skor_ka - $max_score) < $epsilon) $winners[] = 'Kecerdasan Buatan';
    if (abs($skor_si - $max_score) < $epsilon) $winners[] = 'Sistem Informasi';
    if (abs($skor_mm - $max_score) < $epsilon) $winners[] = 'Multimedia';
}

// Generate Explanation & Teks Rekomendasi
$teks_rekomendasi = "";
$explanation = "";

if (empty($winners)) {
    $teks_rekomendasi = "Tidak Ada Rekomendasi";
    $explanation = "Sistem tidak dapat menemukan rekomendasi karena skor perhitungan bernilai 0. Hal ini biasanya terjadi jika belum ada aturan (rule) fuzzy yang cocok dengan profil Anda, atau proses analisis belum berhasil. Silakan coba lakukan proses analisis ulang dari halaman input.";
} elseif (count($winners) > 1) {
    $teks_rekomendasi = implode(' atau ', $winners);
    $explanation = "Berdasarkan analisis Fuzzy Mamdani, Anda memiliki potensi yang seimbang di bidang <strong>" . implode('</strong> atau <strong>', $winners) . "</strong>. Skor fuzzy menunjukkan hasil yang identik (" . number_format($max_score, 2) . "), sehingga Anda dapat memilih salah satu sesuai minat pribadi.";
} else {
    $teks_rekomendasi = $winners[0];
    switch ($winners[0]) {
        case 'Kecerdasan Buatan':
            $explanation = "Berdasarkan analisis Fuzzy Mamdani, profil Anda paling sesuai dengan konsentrasi <strong>Kecerdasan Buatan</strong>.";
            break;
        case 'Sistem Informasi':
            $explanation = "Hasil sistem menunjukkan bahwa konsentrasi <strong>Sistem Informasi</strong> adalah pilihan terbaik untuk Anda. Hal ini didukung oleh keseimbangan antara kemampuan teknis, minat pada proses bisnis, dan komitmen belajar yang baik.";
            break;
        case 'Multimedia':
            $explanation = "Berdasarkan analisis Fuzzy Mamdani, profil Anda paling sesuai dengan konsentrasi <strong>Multimedia</strong>.";
            break;
        default:
            $explanation = "Silakan konsultasikan lebih lanjut dengan dosen pembimbing akademik Anda.";
            break;
    }
}

// Format tanggal
$tanggal_tampil = isset($data['tanggal_input']) ? date('d F Y', strtotime($data['tanggal_input'])) : date('d F Y');

// Logika Ceklis (Hanya muncul jika masuk dalam array $winners)
$ceklis_ka = (in_array('Kecerdasan Buatan', $winners)) ? '<i class="fas fa-check-circle text-success fs-5"></i>' : '-';
$ceklis_si = (in_array('Sistem Informasi', $winners)) ? '<i class="fas fa-check-circle text-success fs-5"></i>' : '-';
$ceklis_mm = (in_array('Multimedia', $winners)) ? '<i class="fas fa-check-circle text-success fs-5"></i>' : '-';
?>
<!-- NIM: 2203010233, nama: Ihsan Musyaffa, topik: Sistem Pendukung Keputusan Pemilihan Konsentrasi -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Rekomendasi - SPK Konsentrasi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/hasil.css">
</head>
<body class="result-page">
    <header class="site-header">
    <nav class="navbar navbar-expand-lg navbar-dark no-print">
        <div class="container">
            <span class="navbar-brand d-flex align-items-center">
                <img src="../assets/images/logo_unper.png" alt="Logo UNPER" class="me-2">
                <span>Universitas Perjuangan Tasikmalaya</span>
            </span>
            <button class="navbar-toggler" type="button" data-nav-toggle="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Buka menu navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="input.php">Input Data</a></li>
                    <li class="nav-item"><a class="nav-link aktif" href="hasil.php?nim=<?= rawurlencode($nim) ?>" aria-current="page">Hasil</a></li>
                </ul>
            </div>
        </div>
    </nav>
    </header>

    <main class="site-main">
    <div class="container mb-5">
        <div class="card result-card shadow-sm border-0">
            <!-- Header Card -->
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-chart-pie me-2"></i>Hasil Rekomendasi Konsentrasi</h4>
                <button onclick="window.print()" class="btn btn-outline-primary btn-sm no-print">
                    <i class="fas fa-print me-1"></i> Cetak Hasil
                </button>
            </div>
            
            <div class="card-body p-4">
                <!-- Identitas Section -->
                <div class="row mb-4">
                    <div class="col-12 mb-2">
                        <h6 class="text-secondary fw-bold mb-0">Identitas Mahasiswa</h6>
                        <hr class="mt-1 mb-2">
                    </div>
                    <div class="col-12 col-md-6">
                        <table class="table table-borderless identitas-table mb-0">
                            <tr>
                                <td>NIM</td>
                                <td>: <?= htmlspecialchars($data['nim']) ?></td>
                            </tr>
                            <tr>
                                <td>Nama Lengkap</td>
                                <td>: <?= htmlspecialchars($data['nama']) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-12 col-md-6">
                        <table class="table table-borderless identitas-table mb-0">
                            <tr>
                                <td>Tanggal Input</td>
                                <td>: <?= $tanggal_tampil ?></td> 
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <!-- Score Table -->
                    <div class="col-12 col-lg-5 mb-4">
                        <div class="card score-card h-100 border-0 shadow-sm">
                            <div class="card-header">
                                <i class="fas fa-list-ol me-2"></i>Rincian Skor Konsentrasi
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Konsentrasi</th>
                                            <th class="text-end">Skor Fuzzy</th>
                                            <th class="text-center pe-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-3"><span class="badge bg-primary badge-konsentrasi">KA</span> Kecerdasan Buatan</td>
                                            <td class="text-end fw-bold"><?= number_format($skor_ka, 2) ?></td>
                                            <td class="text-center pe-3"><?= $ceklis_ka ?></td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3"><span class="badge bg-danger badge-konsentrasi">SI</span> Sistem Informasi</td>
                                            <td class="text-end fw-bold"><?= number_format($skor_si, 2) ?></td>
                                            <td class="text-center pe-3"><?= $ceklis_si ?></td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3"><span class="badge bg-warning text-dark badge-konsentrasi">MM</span> Multimedia</td>
                                            <td class="text-end fw-bold"><?= number_format($skor_mm, 2) ?></td>
                                            <td class="text-center pe-3"><?= $ceklis_mm ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recommendation Box -->
                    <div class="col-12 col-lg-7 mb-4">
                        <div class="card recom-card h-100 border-0 shadow-sm">
                            <div class="card-header">
                                <i class="fas fa-award me-2"></i>Rekomendasi Utama
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-1">Berdasarkan hasil perhitungan, Anda direkomendasikan memilih:</p>
                                <h1 class="recom-title"><?= htmlspecialchars($teks_rekomendasi, ENT_QUOTES, 'UTF-8') ?></h1>
                                <div class="recom-explanation mt-2">
                                    <p class="mb-0"><strong><i class="fas fa-info-circle me-1"></i> Penjelasan:</strong><br><?= $explanation ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="row mt-2">
                    <div class="col-12">
                        <div class="card chart-card border-0 shadow-sm">
                            <div class="card-header">
                                <i class="fas fa-chart-bar me-2"></i>Visualisasi Perbandingan
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="scoreChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer Buttons -->
            <div class="card-footer bg-white text-center py-3 border-top no-print">
                <a href="input.php" class="btn btn-primary px-4 shadow-sm btn-analysis-restart">
                    <i class="fas fa-redo me-2"></i>Analisis Ulang
                </a>
                <a href="../index.php" class="btn btn-outline-secondary px-4 ms-2 bg-white">
                    <i class="fas fa-home me-2"></i>Halaman Depan
                </a>
            </div>
        </div>
    </div>
    </main>

    <footer class="site-footer no-print">
        <p>&copy; 2026 Universitas Perjuangan Tasikmalaya.</p>
        <small>Sistem Pendukung Keputusan - Metode Fuzzy Mamdani</small>
    </footer>

    <script src="../assets/js/ui-native.js"></script>
    <script>
        const chart = document.getElementById('scoreChart');
        const context = chart.getContext('2d');
        const values = [<?= json_encode((float) $skor_ka) ?>, <?= json_encode((float) $skor_si) ?>, <?= json_encode((float) $skor_mm) ?>];
        const labels = ['Kecerdasan Buatan', 'Sistem Informasi', 'Multimedia'];
        const colors = ['#0d6efd', '#dc3545', '#ffc107'];
        function gambarGrafik() {
            const ratio = window.devicePixelRatio || 1;
            const width = chart.clientWidth;
            const height = chart.clientHeight;

            chart.width = width * ratio;
            chart.height = height * ratio;
            context.setTransform(ratio, 0, 0, ratio, 0, 0);
            context.clearRect(0, 0, width, height);
            context.font = '12px "Segoe UI", sans-serif';
            context.textBaseline = 'middle';

            const left = Math.min(175, width * 0.38);
            const right = 42;
            const graphWidth = Math.max(0, width - left - right);
            const rowHeight = height / values.length;
            context.strokeStyle = '#d9dee5';
            context.fillStyle = '#555';
            context.textAlign = 'right';
            context.lineWidth = 1;
            context.beginPath();
            context.moveTo(left, 8);
            context.lineTo(left, height - 8);
            context.stroke();

            values.forEach((value, index) => {
                const y = rowHeight * index + rowHeight / 2;
                const barWidth = graphWidth * Math.max(0, Math.min(100, value)) / 100;
                context.textAlign = 'right';
                context.fillStyle = '#555';
                context.fillText(labels[index], left - 10, y);
                context.fillStyle = colors[index];
                context.fillRect(left, y - 14, barWidth, 28);
                context.textAlign = 'left';
                context.fillStyle = '#333';
                context.fillText(Number(value).toFixed(2), left + barWidth + 7, y);
            });
        }

        gambarGrafik();
        window.addEventListener('resize', gambarGrafik);
    </script>
</body>
</html>