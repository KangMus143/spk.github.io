<?php
require_once '../../includes/database.php';
require_once '../../includes/auth.php';

requireLogin();

function validStudentInput($nim, $nama, $ipk, $nilai_mk, $sks) {
    return preg_match('/^\d{10}$/', $nim) &&
        in_array(substr($nim, 0, 2), ['19', '20', '21', '22', '23', '24', '25', '26'], true) &&
        substr($nim, 2, 4) === '0301' &&
        mb_strlen($nama) >= 3 && mb_strlen($nama) <= 100 &&
        is_numeric($ipk) && $ipk >= 0 && $ipk <= 4 &&
        is_numeric($nilai_mk) && $nilai_mk >= 0 && $nilai_mk <= 100 &&
        is_numeric($sks) && $sks >= 0 && $sks <= 100;
}

if (isset($_GET['logout'])) {
    logout();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_mahasiswa') {
    $mahasiswa_id = filter_var($_POST['mahasiswa_id'] ?? null, FILTER_VALIDATE_INT);
    if ($mahasiswa_id) {
        $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id = ?");
        $stmt->execute([$mahasiswa_id]);
        header("Location: dashboard.php");
        exit;
    }

    $dashboard_error = 'Data mahasiswa yang akan dihapus tidak valid.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_mahasiswa') {
    $mahasiswa_id = filter_var($_POST['mahasiswa_id'] ?? null, FILTER_VALIDATE_INT);
    $nim = trim($_POST['nim'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $ipk = $_POST['ipk'] ?? '';
    $nilai_mk = $_POST['nilai_mk'] ?? '';
    $sks = $_POST['sks'] ?? '';

    if (!$mahasiswa_id || !validStudentInput($nim, $nama, $ipk, $nilai_mk, $sks)) {
        $dashboard_error = 'Periksa kembali NIM, nama, IPK, nilai mata kuliah, dan persentase SKS.';
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE mahasiswa SET nim = ?, nama = ?, ipk = ?, nilai_mk = ?, sks = ? WHERE id = ?");
            $stmt->execute([$nim, $nama, $ipk, $nilai_mk, $sks, $mahasiswa_id]);
            $stmt = $pdo->prepare("DELETE FROM hasil_rekomendasi WHERE id_mahasiswa = ?");
            $stmt->execute([$mahasiswa_id]);
            $pdo->commit();
            header("Location: dashboard.php?updated=1");
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $dashboard_error = 'Data gagal diubah. Pastikan NIM belum digunakan mahasiswa lain.';
        }
    }
}

// Handle Add Mahasiswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_mahasiswa') {
    $nim = trim($_POST['nim'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $ipk = $_POST['ipk'] ?? '';
    $nilai_mk = $_POST['nilai_mk'] ?? '';
    $sks = $_POST['sks'] ?? '';

    if (!validStudentInput($nim, $nama, $ipk, $nilai_mk, $sks)) {
        $dashboard_error = 'Periksa kembali NIM, nama, IPK, nilai mata kuliah, dan persentase SKS.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ?");
            $stmt->execute([$nim]);
            if ($stmt->fetch()) {
                $dashboard_error = 'NIM tersebut sudah terdaftar. Silakan gunakan NIM yang lain.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO mahasiswa (nim, nama, ipk, nilai_mk, sks, minat, komitmen) VALUES (?, ?, ?, ?, ?, 0, 0)");
                $stmt->execute([$nim, $nama, $ipk, $nilai_mk, $sks]);
                header("Location: dashboard.php?added=1");
                exit;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $dashboard_error = 'Data mahasiswa gagal ditambahkan. Periksa kembali isian dan coba lagi.';
        }
    }
}

// Fetch Stats
$total_mhs = $pdo->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();

// Hitung statistik berdasarkan kolom yang benar (Dinamis menghitung ties)
$stmt_stats = $pdo->query("SELECT skor_jaringan, skor_rpl, skor_multimedia FROM hasil_rekomendasi");
$results_stats = $stmt_stats->fetchAll(PDO::FETCH_ASSOC);

$total_ka = 0;
$total_si = 0;
$total_mm = 0;
$epsilon = 0.001;

foreach ($results_stats as $row) {
    $s_ka = floatval($row['skor_jaringan']); // Jaringan = KA
    $s_si = floatval($row['skor_rpl']);      // RPL = SI
    $s_mm = floatval($row['skor_multimedia']); // Multimedia = MM
    
    $max_score = max($s_ka, $s_si, $s_mm);
    
    if ($max_score > 0) {
        if (abs($s_ka - $max_score) < $epsilon) $total_ka++;
        if (abs($s_si - $max_score) < $epsilon) $total_si++;
        if (abs($s_mm - $max_score) < $epsilon) $total_mm++;
    }
}

// Fetch Data for Table
$query = "
    SELECT m.id as mahasiswa_id, m.nim, m.nama, m.ipk, m.nilai_mk, m.sks, m.tanggal_input, 
           h.skor_jaringan, h.skor_rpl, h.skor_multimedia, h.rekomendasi_utama
    FROM mahasiswa m
    LEFT JOIN hasil_rekomendasi h ON m.id = h.id_mahasiswa
    ORDER BY m.tanggal_input DESC
";
$stmt = $pdo->query($query);
$mahasiswa = $stmt->fetchAll();

?>
<!-- NIM: 2203010233, nama: Ihsan Musyaffa, topik: Sistem Pendukung Keputusan Pemilihan Konsentrasi -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SPK Konsentrasi</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body class="admin-dashboard-page">
    <header class="site-header">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <i class="fas fa-cube me-2"></i> Admin Panel SPK
            </a>
            <button class="navbar-toggler" type="button" data-nav-toggle="#adminNavigation" aria-controls="adminNavigation" aria-expanded="false" aria-label="Buka menu navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-collapse" id="adminNavigation">
                <div class="navbar-nav ms-auto align-items-lg-center">
                    <a class="nav-link aktif" href="dashboard.php" aria-current="page"><i class="fas fa-users me-1"></i> Data Mahasiswa</a>
                    <a class="nav-link" href="aturan.php"><i class="fas fa-cogs me-1"></i> Aturan Fuzzy</a>
                    <a class="btn btn-logout ms-lg-3 mt-2 mt-lg-0" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>
    </header>

    <main class="site-main">
    <div class="container-fluid px-4 mt-4 mb-5">
        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success" role="status">Data mahasiswa berhasil ditambahkan. Silakan proses analisis melalui halaman Input Data.</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success" role="status">Data diperbarui. Jalankan ulang analisis untuk memperbarui rekomendasi.</div>
        <?php endif; ?>
        <?php if (isset($dashboard_error)): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($dashboard_error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <h4 class="fw-bold mb-3"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Statistik</h4>
        
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-card-blue shadow-sm">
                    <div class="card-body position-relative">
                        <h6>Total Mahasiswa</h6>
                        <h2><?= $total_mhs ?></h2>
                        <i class="fas fa-users icon-bg"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-card-cyan shadow-sm">
                    <div class="card-body position-relative">
                        <h6>Rekomendasi AI</h6>
                        <h2><?= $total_ka ?></h2>
                        <i class="fas fa-brain icon-bg"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-card-red shadow-sm">
                    <div class="card-body position-relative">
                        <h6>Rekomendasi SI</h6>
                        <h2><?= $total_si ?></h2>
                        <i class="fas fa-network-wired icon-bg"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-card-yellow shadow-sm">
                    <div class="card-body position-relative">
                        <h6 class="text-dark">Rekomendasi Multimedia</h6>
                        <h2 class="text-dark"><?= $total_mm ?></h2>
                        <i class="fas fa-photo-video icon-bg"></i>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="fw-bold mb-3 mt-4"><i class="fas fa-history me-2"></i>Riwayat Analisis</h5>
        
        <div class="card card-table">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div></div>
                <button type="button" class="btn btn-primary btn-sm" data-modal-target="#addModal">
                    <i class="fas fa-plus me-1"></i> Tambah Mahasiswa
                </button>
            </div>
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table id="dataTable" class="table table-hover table-bordered align-middle data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Rekomendasi</th>
                                <th>Skor AI</th>
                                <th>Skor SI</th>
                                <th>Skor Multimedia</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($mahasiswa as $row): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['tanggal_input'])) ?></td>
                                    <td><?= htmlspecialchars($row['nim']) ?></td>
                                    <td><?= htmlspecialchars($row['nama']) ?></td>
                                    <td>
                                        <?php
                                        // LOGIKA BARU: Hitung Pemenang berdasarkan skor tertinggi (Sinkron dengan hasil.php)
                                        $s_ka = floatval($row['skor_jaringan'] ?? 0);
                                        $s_si = floatval($row['skor_rpl'] ?? 0);
                                        $s_mm = floatval($row['skor_multimedia'] ?? 0);
                                        
                                        $max_score = max($s_ka, $s_si, $s_mm);
                                        $winners = [];
                                        $epsilon = 0.001;
                                        
                                        if ($max_score > 0) {
                                            if (abs($s_ka - $max_score) < $epsilon) $winners[] = 'Kecerdasan Buatan';
                                            if (abs($s_si - $max_score) < $epsilon) $winners[] = 'Sistem Informasi';
                                            if (abs($s_mm - $max_score) < $epsilon) $winners[] = 'Multimedia';
                                        }
                                        
                                        $rek = empty($winners) ? 'Belum diproses' : implode(' atau ', $winners);
                                        
                                        $badgeClass = 'bg-secondary'; 
                                        if (!empty($winners)) {
                                            if (count($winners) > 1) {
                                                $badgeClass = 'bg-success'; // Hijau jika tie
                                            } elseif ($winners[0] == 'Kecerdasan Buatan') {
                                                $badgeClass = 'bg-primary'; 
                                            } elseif ($winners[0] == 'Sistem Informasi') {
                                                $badgeClass = 'bg-danger'; 
                                            } elseif ($winners[0] == 'Multimedia') {
                                                $badgeClass = 'bg-warning text-dark'; 
                                            }
                                        }
                                        ?>
                                        <span class="badge badge-rekomendasi <?= $badgeClass ?>">
                                            <?= htmlspecialchars($rek) ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($row['skor_jaringan'] ?? 0, 2) ?></td>
                                    <td><?= number_format($row['skor_rpl'] ?? 0, 2) ?></td>
                                    <td><?= number_format($row['skor_multimedia'] ?? 0, 2) ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-warning btn-sm text-dark edit-student"
                                            data-modal-target="#editModal"
                                            data-student-id="<?= (int)$row['mahasiswa_id'] ?>"
                                            data-nim="<?= htmlspecialchars($row['nim'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-name="<?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-ipk="<?= htmlspecialchars($row['ipk'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            data-nilai="<?= htmlspecialchars($row['nilai_mk'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            data-sks="<?= htmlspecialchars($row['sks'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            aria-label="Ubah data <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="fas fa-edit"></i> Ubah
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm delete-student"
                                            data-modal-target="#deleteModal"
                                            data-student-id="<?= (int)$row['mahasiswa_id'] ?>"
                                            data-student-name="<?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                        <?php if($row['rekomendasi_utama']): ?>
                                            <a href="../hasil.php?nim=<?= urlencode($row['nim']) ?>" target="_blank" class="btn btn-info btn-sm text-white">
                                                <i class="fas fa-search"></i> Detail
                                            </a>
                                        <?php else: ?>
                                            <a href="../input.php?nim=<?= urlencode($row['nim']) ?>" class="btn btn-success btn-sm text-white" title="Proses Analisis Sekarang">
                                                <i class="fas fa-calculator"></i> Proses
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button onclick="exportTableToExcel('dataTable', 'data-rekomendasi-spk')" class="btn btn-success shadow-sm btn-sm">
                        <i class="fas fa-file-excel me-2"></i>Export ke Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Mahasiswa -->
    <div class="modal" id="addModal" role="dialog" aria-modal="true" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header modal-header-primary">
                    <h5 class="modal-title fw-bold" id="addModalLabel">Tambah Data Mahasiswa</h5>
                    <button type="button" class="btn-close btn-close-white" data-modal-close aria-label="Tutup"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="add_mahasiswa">
                        <div class="mb-3">
                            <label for="add-nim" class="form-label">NIM</label>
                            <input type="text" class="form-control" id="add-nim" name="nim" pattern="[0-9]{10}" minlength="10" maxlength="10" inputmode="numeric" title="Masukkan NIM berupa 10 digit angka." required>
                        </div>
                        <div class="mb-3">
                            <label for="add-name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="add-name" name="nama" minlength="3" maxlength="100" required>
                        </div>
                        <div class="mb-3">
                            <label for="add-ipk" class="form-label">IPK (0.00 - 4.00)</label>
                            <input type="number" class="form-control" id="add-ipk" name="ipk" step="0.01" min="0" max="4" required>
                        </div>
                        <div class="mb-3">
                            <label for="add-nilai-mk" class="form-label">Nilai MK Kunci (0-100)</label>
                            <input type="number" class="form-control" id="add-nilai-mk" name="nilai_mk" min="0" max="100" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="add-sks" class="form-label">% SKS A/B (0-100)</label>
                            <input type="number" class="form-control" id="add-sks" name="sks" min="0" max="100" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pe-4 pb-4">
                        <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="editModal" role="dialog" aria-modal="true" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header modal-header-primary">
                    <h5 class="modal-title" id="editModalLabel">Ubah Data Mahasiswa</h5>
                    <button type="button" class="btn-close btn-close-white" data-modal-close aria-label="Tutup"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_mahasiswa">
                        <input type="hidden" name="mahasiswa_id" id="edit-student-id">
                        <div class="mb-3">
                            <label for="edit-nim" class="form-label">NIM</label>
                            <input type="text" class="form-control" id="edit-nim" name="nim" pattern="[0-9]{10}" minlength="10" maxlength="10" inputmode="numeric" title="Masukkan NIM berupa 10 digit angka." required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="edit-name" name="nama" minlength="3" maxlength="100" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-ipk" class="form-label">IPK (0.00 - 4.00)</label>
                            <input type="number" class="form-control" id="edit-ipk" name="ipk" step="0.01" min="0" max="4" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-nilai" class="form-label">Nilai MK Kunci (0-100)</label>
                            <input type="number" class="form-control" id="edit-nilai" name="nilai_mk" min="0" max="100" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-sks" class="form-label">% SKS A/B (0-100)</label>
                            <input type="number" class="form-control" id="edit-sks" name="sks" min="0" max="100" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="deleteModal" role="dialog" aria-modal="true" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Hapus Data Mahasiswa</h5>
                    <button type="button" class="btn-close" data-modal-close aria-label="Tutup"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete_mahasiswa">
                        <input type="hidden" name="mahasiswa_id" id="delete-student-id">
                        <p class="mb-0">Yakin ingin menghapus data <strong id="delete-student-name"></strong>? Hasil rekomendasi terkait juga akan dihapus.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
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

    <script src="../../assets/js/validasi-form.js"></script>
    <script src="../../assets/js/ui-native.js"></script>
    
    <script>
        document.querySelectorAll('.edit-student').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('edit-student-id').value = button.dataset.studentId;
                document.getElementById('edit-nim').value = button.dataset.nim;
                document.getElementById('edit-name').value = button.dataset.name;
                document.getElementById('edit-ipk').value = button.dataset.ipk;
                document.getElementById('edit-nilai').value = button.dataset.nilai;
                document.getElementById('edit-sks').value = button.dataset.sks;
            });
        });

        document.querySelectorAll('.delete-student').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('delete-student-id').value = button.dataset.studentId;
                document.getElementById('delete-student-name').textContent = button.dataset.studentName;
            });
        });

        function exportTableToExcel(tableID, filename = ''){
            var downloadLink;
            var dataType = 'application/vnd.ms-excel';
            var tableSelect = document.getElementById(tableID);
            var exportTable = tableSelect.cloneNode(true);
            exportTable.querySelectorAll('tr[hidden]').forEach(row => row.removeAttribute('hidden'));
            var tableHTML = exportTable.outerHTML;
            
            filename = filename?filename+'.xls':'excel_data.xls';
            downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);
            
            if(navigator.msSaveOrOpenBlob){
                var blob = new Blob(['\ufeff', tableHTML], { type: dataType });
                navigator.msSaveOrOpenBlob( blob, filename);
            }else{
                downloadLink.href = 'data:' + dataType + ',' + encodeURIComponent(tableHTML);
                downloadLink.download = filename;
                downloadLink.click();
            }
        }
    </script>
</body>
</html>