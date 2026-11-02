<?php
require_once '../includes/database.php';

$stmt = $pdo->query("SELECT nim, nama, ipk, nilai_mk AS nilai_mk_kunci, sks AS persen_sks_ab FROM mahasiswa ORDER BY nama ASC");
$mahasiswa_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- NIM: 2203010233, nama: Ihsan Musyaffa, topik: Sistem Pendukung Keputusan Pemilihan Konsentrasi -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis Minat & Bakat - SPK Konsentrasi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/spk-input.css">
</head>
<body class="input-page">
    <header class="site-header">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <img src="../assets/images/logo_unper.png" alt="Logo UNPER" class="me-2">
                Universitas Perjuangan Tasikmalaya
            </a>
            <button class="navbar-toggler" type="button" data-nav-toggle="#inputNavigation" aria-controls="inputNavigation" aria-expanded="false" aria-label="Buka menu navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-collapse" id="inputNavigation">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="../index.php">Beranda</a>
                    <a class="nav-link aktif" href="input.php" aria-current="page">Input Data</a>
                </div>
            </div>
        </div>
    </nav>
    </header>

    <main class="site-main">
    <!-- Stepper -->
    <div class="container">
        <div class="stepper-container">
            <div class="stepper-line"><div class="stepper-line-progress" id="progress-line"></div></div>
            <div class="stepper">
                <div class="step active" id="step-indicator-1">1</div>
                <div class="step" id="step-indicator-2">2</div>
                <div class="step" id="step-indicator-3">3</div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="container">
        <div class="card wizard-card">
            <div class="card-header">
                <h1><i class="fas fa-pen me-2"></i>Analisis Minat & Bakat</h1>
            </div>
            <div class="card-body">
                <form action="proses.php" method="POST" id="analysisForm">
                    
                    <!-- ================= STEP 1 ================= -->
                    <div class="step-content active" id="step-1">
                        <div class="step-title">Langkah 1: Identitas Mahasiswa</div>
                        
                        <div class="mb-3">
                            <label for="nim_search" class="form-label">Masukkan NIM</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="nim_search" name="nim" placeholder="Contoh: 2203010001" minlength="10" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" title="Masukkan NIM berupa 10 digit angka." required>
                                <button class="btn btn-check-data" type="button" id="btn-cek"><i class="fas fa-search me-1"></i> Cek Data</button>
                            </div>
                            <div class="form-text"><i class="fas fa-info-circle"></i> NIM 10 digit. Kode Angkatan: 219 (2019) s/d 226 (2026).</div>
                            <div id="nim-error" class="text-danger mt-1 nim-error is-hidden"></div>
                        </div>

                        <div class="mb-3 form-hidden" id="nama-group">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap Anda" minlength="3" maxlength="100">
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-primary" id="btn-next-1">Lanjut <i class="fas fa-arrow-right ms-1"></i></button>
                        </div>
                    </div>

                    <!-- ================= STEP 2 ================= -->
                    <div class="step-content" id="step-2">
                        <div class="step-title">Langkah 2: Data Akademik</div>
                        
                        <div class="alert alert-warning form-hidden" id="alert-not-found">
                            <i class="fas fa-exclamation-triangle me-1"></i> Data tidak ditemukan. Silakan lengkapi data di bawah ini.
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nim_display" class="form-label">NIM</label>
                                <input type="text" class="form-control bg-light" id="nim_display" readonly>
                                <div class="form-text">NIM yang divalidasi.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="nama_display" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control bg-light" id="nama_display" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="ipk" class="form-label">IPK (Indeks Prestasi Kumulatif)</label>
                            <input type="number" class="form-control" id="ipk" name="ipk" min="0" max="4" step="0.01" placeholder="Contoh: 3.50 (Gunakan titik, atau koma)" required>
                            <div class="form-text">Gunakan tanda titik (.) atau koma (,) untuk nilai desimal. Rentang 0.00 - 4.00.</div>
                        </div>

                        <div class="mb-3">
                            <label for="nilai_mk_kunci" class="form-label">Nilai Rata-rata Mata Kuliah Kunci (0-100)</label>
                            <input type="number" class="form-control" id="nilai_mk_kunci" name="nilai_mk_kunci" min="0" max="100" step="0.01" placeholder="Masukkan nilai rata-rata" required>
                            <div class="form-text mt-2">
                                <strong>Mata Kuliah Kunci per Konsentrasi:</strong><br>
                                • <strong>KA:</strong> Aljabar, Struktur Data, Matematika Diskrit<br>
                                • <strong>SI:</strong> Basis Data, APSI, RPL<br>
                                • <strong>MM:</strong> Grafika Komputer, Desain Multimedia<br>
                                <em>Hitung rata-rata dari MK tersebut (Total Nilai / Jumlah MK). Jika nilai desimal, gunakan titik (.)</em>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="persen_sks_ab" class="form-label">% SKS dengan Nilai A/B (0-100)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="persen_sks_ab" name="persen_sks_ab" min="0" max="100" step="0.01" placeholder="Masukkan persentase" required>
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">Rumus: (Total SKS Nilai A & B / Total SKS yang diambil) * 100</div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" id="btn-back-2"><i class="fas fa-arrow-left me-1"></i> Kembali</button>
                            <button type="button" class="btn btn-primary" id="btn-next-2">Lanjut <i class="fas fa-arrow-right ms-1"></i></button>
                        </div>
                    </div>

                    <!-- ================= STEP 3 ================= -->
                    <div class="step-content" id="step-3">
                        <div class="step-title">Langkah 3: Self Assessment</div>
                        <p class="text-muted mb-4">Geser slider sesuai dengan kondisi Anda saat ini.</p>

                        <div class="range-container">
                            <label for="minat_pribadi" class="form-label">Minat Pribadi</label>
                            <div class="slider-value" id="val-minat">50</div>
                            <input type="range" id="minat_pribadi" name="minat_pribadi" min="1" max="100" value="50" oninput="document.getElementById('val-minat').innerText = this.value">
                            <div class="range-label">
                                <span>Rendah</span>
                                <span>Tinggi</span>
                            </div>
                        </div>

                        <div class="range-container">
                            <label for="komitmen_belajar" class="form-label">Komitmen Belajar</label>
                            <div class="slider-value" id="val-komitmen">50</div>
                            <input type="range" id="komitmen_belajar" name="komitmen_belajar" min="1" max="100" value="50" oninput="document.getElementById('val-komitmen').innerText = this.value">
                            <div class="range-label">
                                <span>Rendah</span>
                                <span>Tinggi</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" id="btn-back-3"><i class="fas fa-arrow-left me-1"></i> Kembali</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-play me-1"></i> Proses Analisis</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Universitas Perjuangan Tasikmalaya.</p>
        <small>Sistem Pendukung Keputusan - Metode Fuzzy Mamdani</small>
    </footer>

    <script src="../assets/js/validasi-form.js"></script>
    <script src="../assets/js/ui-native.js"></script>
    <script>
        const students = <?= json_encode($mahasiswa_list, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        const initialNim = <?= json_encode($_GET['nim'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        
        // Elements
        const stepContents = document.querySelectorAll('.step-content');
        const stepIndicators = document.querySelectorAll('.step');
        const stepperContainer = document.querySelector('.stepper-container');
        
        let currentStep = 1;
        let isDataFound = false;
        let studentData = null;

        // Functions
        function updateStepUI() {
            // Update Cards
            stepContents.forEach((el, index) => {
                el.classList.toggle('active', index + 1 === currentStep);
            });
            
            // Update Indicators
            stepIndicators.forEach((el, index) => {
                const stepNum = index + 1;
                el.classList.remove('active', 'completed');
                if (stepNum === currentStep) el.classList.add('active');
                else if (stepNum < currentStep) el.classList.add('completed');
            });

            // Update Line
            const progressPercent = ((currentStep - 1) / 2) * 100;
            stepperContainer.classList.remove('progress-0', 'progress-50', 'progress-100');
            stepperContainer.classList.add(`progress-${progressPercent}`);
        }

        function goToStep(step) {
            if (step < 1 || step > 3) return;
            currentStep = step;
            updateStepUI();
        }

        // Step 1 Logic
        const nimSearch = document.getElementById('nim_search');
        const nimError = document.getElementById('nim-error');
        const namaGroup = document.getElementById('nama-group');
        const alertNotFound = document.getElementById('alert-not-found');

        function showNimError(message, isSuccess = false) {
            nimError.innerText = message;
            nimError.classList.toggle('text-success', isSuccess);
            nimError.classList.toggle('text-danger', !isSuccess);
            nimError.classList.remove('is-hidden');
        }

        function hideNimError() {
            nimError.classList.add('is-hidden');
        }

        document.getElementById('btn-cek').addEventListener('click', () => {
            const nim = nimSearch.value.trim();
            hideNimError();
            
            // Basic Validation
            if (!/^\d{10}$/.test(nim)) {
                showNimError('NIM harus terdiri dari 10 digit angka!');
                return;
            }
            const angkatan = nim.substring(0, 2);
            const kodeProdi = nim.substring(2, 6);
            if (!['19','20','21','22','23','24','25','26'].includes(angkatan)) {
                showNimError('Tahun angkatan tidak valid (19-26).');
                return;
            }
            if (kodeProdi !== '0301') {
                showNimError('Kode Prodi harus 0301 (Teknik Informatika).');
                return;
            }

            // Find Student
            studentData = students.find(s => s.nim === nim);
            
            if (studentData) {
                // Found
                isDataFound = true;
                namaGroup.classList.add('form-hidden');
                document.getElementById('nama').required = false;
                document.getElementById('nama').value = studentData.nama;
                
                // Populate Step 2
                document.getElementById('nim_display').value = studentData.nim;
                document.getElementById('nama_display').value = studentData.nama;
                document.getElementById('ipk').value = studentData.ipk;
                document.getElementById('nilai_mk_kunci').value = studentData.nilai_mk_kunci;
                document.getElementById('persen_sks_ab').value = studentData.persen_sks_ab;
                
                // Disable academic fields
                ['ipk', 'nilai_mk_kunci', 'persen_sks_ab'].forEach(id => {
                    document.getElementById(id).readOnly = true;
                });
                alertNotFound.classList.add('form-hidden');
                
                // Optional: show success message
                showNimError('Data ditemukan! Silakan lanjut.', true);

            } else {
                // Not Found
                isDataFound = false;
                namaGroup.classList.remove('form-hidden');
                document.getElementById('nama').required = true;
                document.getElementById('nama').value = '';
                
                // Populate Step 2
                document.getElementById('nim_display').value = nim;
                document.getElementById('nama_display').value = '';
                document.getElementById('ipk').value = '';
                document.getElementById('nilai_mk_kunci').value = '';
                document.getElementById('persen_sks_ab').value = '';
                
                // Enable academic fields
                ['ipk', 'nilai_mk_kunci', 'persen_sks_ab'].forEach(id => {
                    document.getElementById(id).readOnly = false;
                });
                alertNotFound.classList.remove('form-hidden');
            }
        });

        // Sync nama display when typing in Step 1 (if new student)
        document.getElementById('nama').addEventListener('input', (e) => {
            document.getElementById('nama_display').value = e.target.value;
        });

        document.getElementById('btn-next-1').addEventListener('click', () => {
            const nim = nimSearch.value.trim();
            if (!/^\d{10}$/.test(nim)) {
                showNimError('NIM harus 10 digit!');
                return;
            }
            if (!['19', '20', '21', '22', '23', '24', '25', '26'].includes(nim.substring(0, 2))) {
                showNimError('Tahun angkatan tidak valid (19-26).');
                return;
            }
            if (nim.substring(2, 6) !== '0301') {
                showNimError('Kode Prodi harus 0301 (Teknik Informatika).');
                return;
            }
            
            if (!isDataFound) {
                const nameInput = document.getElementById('nama');
                const nama = nameInput.value.trim();
                if (!nameInput.checkValidity() || nama.length < 3) {
                    nameInput.setCustomValidity('Nama harus terdiri dari 3 sampai 100 karakter.');
                    nameInput.reportValidity();
                    nameInput.addEventListener('input', () => nameInput.setCustomValidity(''), { once: true });
                    return;
                }
                document.getElementById('nama_display').value = nama;
            }
            
            hideNimError();
            goToStep(2);
        });

        // Step 2 Logic
        document.getElementById('btn-back-2').addEventListener('click', () => goToStep(1));
        
        document.getElementById('btn-next-2').addEventListener('click', () => {
            const academicInputs = [
                document.getElementById('ipk'),
                document.getElementById('nilai_mk_kunci'),
                document.getElementById('persen_sks_ab')
            ];
            const invalidInput = academicInputs.find(input => !input.checkValidity());
            if (invalidInput) {
                invalidInput.reportValidity();
                return;
            }
            goToStep(3);
        });

        // Step 3 Logic
        document.getElementById('btn-back-3').addEventListener('click', () => goToStep(2));

        // Sync hidden NIM input with actual value before submit
        document.getElementById('analysisForm').addEventListener('submit', (e) => {
            if (!/^\d{10}$/.test(nimSearch.value.trim())) {
                e.preventDefault();
                goToStep(1);
                showNimError('NIM harus terdiri dari 10 digit angka.');
                nimSearch.reportValidity();
            }
        });

        if (/^\d{10}$/.test(initialNim)) {
            nimSearch.value = initialNim;
            document.getElementById('btn-cek').click();
        }
    </script>
</body>
</html>