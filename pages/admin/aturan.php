<?php
require_once '../../includes/database.php';
require_once '../../includes/auth.php';

requireLogin();

// Handle Add Rule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_rule'])) {
    $ipk = $_POST['ipk'];
    $nilai_mk = $_POST['nilai_mk'];
    $sks = $_POST['sks'];
    $minat = $_POST['minat'];
    $komitmen = $_POST['komitmen'];
    $konsentrasi_id = $_POST['konsentrasi_id'];

    // Generate rule code (R-XXX)
    $stmt = $pdo->query("SELECT kode_aturan FROM aturan_fuzzy ORDER BY id DESC LIMIT 1");
    $last_code = $stmt->fetchColumn();
    
    if ($last_code) {
        $last_num = (int)filter_var($last_code, FILTER_SANITIZE_NUMBER_INT);
        $next_num = $last_num + 1;
    } else {
        $next_num = 1;
    }
    $kode_aturan = 'R' . str_pad($next_num, 3, '0', STR_PAD_LEFT);

    // Generate rule text
    $stmt = $pdo->prepare("SELECT nama_konsentrasi FROM konsentrasi WHERE id = ?");
    $stmt->execute([$konsentrasi_id]);
    $konsentrasi_nama = $stmt->fetchColumn();

    $rule_text = "IF IPK=" . strtoupper($ipk) . 
                 " AND NILAI_MK=" . strtoupper($nilai_mk) . 
                 " AND %SKS=" . strtoupper($sks) . 
                 " AND MINAT=" . strtoupper($minat) . 
                 " AND KOMITMEN=" . strtoupper($komitmen) . 
                 " THEN " . strtoupper($konsentrasi_nama);

    try {
        $stmt = $pdo->prepare("INSERT INTO aturan_fuzzy (kode_aturan, ipk_kategori, nilai_mk_kategori, persen_sks_kategori, minat_kategori, komitmen_kategori, konsentrasi_id, rule_text) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$kode_aturan, $ipk, $nilai_mk, $sks, $minat, $komitmen, $konsentrasi_id, $rule_text]);
        $success_msg = "Aturan baru berhasil ditambahkan!";
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error_msg = 'Aturan gagal ditambahkan. Periksa kembali isian dan coba lagi.';
    }
}

// Handle Delete Rule
if (isset($_POST['delete_id'])) {
    $delete_id = filter_var($_POST['delete_id'], FILTER_VALIDATE_INT);
    if (!$delete_id) {
        $error_msg = 'ID aturan yang akan dihapus tidak valid.';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM aturan_fuzzy WHERE id = ?");
            $stmt->execute([$delete_id]);
            header("Location: aturan.php");
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error_msg = 'Aturan gagal dihapus. Silakan coba lagi.';
        }
    }
}

// Fetch Rules
$query = "SELECT a.*, k.nama_konsentrasi FROM aturan_fuzzy a JOIN konsentrasi k ON a.konsentrasi_id = k.id ORDER BY a.id DESC";
$stmt = $pdo->query($query);
$aturan = $stmt->fetchAll();

// Fetch Konsentrasi for Dropdown
$stmt_k = $pdo->query("SELECT * FROM konsentrasi");
$konsentrasi_list = $stmt_k->fetchAll();
?>
<!-- NIM: 2203010233, nama: Ihsan Musyaffa, topik: Sistem Pendukung Keputusan Pemilihan Konsentrasi -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Aturan Fuzzy - Admin SPK</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body class="admin-rules-page">
    <header class="site-header">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <i class="fas fa-cube me-2"></i> Admin Panel SPK
            </a>
            <button class="navbar-toggler" type="button" data-nav-toggle="#adminRulesNavigation" aria-controls="adminRulesNavigation" aria-expanded="false" aria-label="Buka menu navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="navbar-collapse" id="adminRulesNavigation">
                <div class="navbar-nav ms-auto align-items-lg-center">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-users me-1"></i> Data Mahasiswa</a>
                    <a class="nav-link aktif" href="aturan.php" aria-current="page"><i class="fas fa-cogs me-1"></i> Aturan Fuzzy</a>
                    <a class="btn btn-logout ms-lg-3 mt-2 mt-lg-0" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>
    </header>

    <main class="site-main">
    <div class="container-fluid px-4 mt-4 mb-5">
        <!-- Judul & Tombol Tambah -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0"><i class="fas fa-cogs me-2"></i>Manajemen Aturan Fuzzy</h4>
            <button class="btn btn-primary btn-sm" data-modal-target="#addRuleModal"><i class="fas fa-plus me-1"></i> Tambah Aturan Baru</button>
        </div>

        <!-- Alert Info -->
        <div class="alert alert-custom shadow-sm" role="alert">
            <i class="fas fa-info-circle me-1"></i> Halaman ini menampilkan basis aturan (rule base) yang digunakan dalam sistem untuk menentukan rekomendasi konsentrasi.
        </div>
        <div class="alert alert-info py-2" role="note">Gunakan kolom pencarian untuk menemukan aturan. Form kategori Komitmen menggunakan label Lemah, Cukup, dan Kuat sesuai perhitungan fuzzy.</div>

        <!-- Pesan Sukses / Error -->
        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $success_msg ?>
                <button type="button" class="btn-close" data-alert-close aria-label="Tutup"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error_msg ?>
                <button type="button" class="btn-close" data-alert-close aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <!-- Tabel Aturan -->
        <div class="card card-table">
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table id="dataTable" class="table table-striped table-hover align-middle data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>IPK</th>
                                <th>Nilai MK</th>
                                <th>% SKS</th>
                                <th>Minat</th>
                                <th>Komitmen</th>
                                <th>Output Konsentrasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aturan as $rule): ?>
                                <tr>
                                    <td><span class="badge badge-id"><?= htmlspecialchars($rule['kode_aturan']) ?></span></td>
                                    <td class="text-uppercase"><?= htmlspecialchars($rule['ipk_kategori']) ?></td>
                                    <td class="text-uppercase"><?= htmlspecialchars($rule['nilai_mk_kategori']) ?></td>
                                    <td class="text-uppercase"><?= htmlspecialchars($rule['persen_sks_kategori']) ?></td>
                                    <td class="text-uppercase"><?= htmlspecialchars($rule['minat_kategori']) ?></td>
                                    <td class="text-uppercase"><?= htmlspecialchars($rule['komitmen_kategori']) ?></td>
                                    <td>
                                        <span class="badge-output">
                                            <?= htmlspecialchars($rule['nama_konsentrasi']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-delete btn-sm delete-rule" data-modal-target="#deleteRuleModal" data-rule-id="<?= (int)$rule['id'] ?>" data-rule-code="<?= htmlspecialchars($rule['kode_aturan'], ENT_QUOTES, 'UTF-8') ?>" title="Hapus Aturan">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="deleteRuleModal" role="dialog" aria-modal="true" tabindex="-1" aria-labelledby="deleteRuleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteRuleModalLabel">Hapus Aturan Fuzzy</h5>
                    <button type="button" class="btn-close" data-modal-close aria-label="Tutup"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="delete_id" id="delete-rule-id">
                        <p class="mb-0">Yakin ingin menghapus aturan <strong id="delete-rule-code"></strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-modal-close>Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus Aturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Aturan -->
    <div class="modal" id="addRuleModal" role="dialog" aria-modal="true" tabindex="-1" aria-labelledby="addRuleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRuleModalLabel">Tambah Aturan Baru</h5>
                    <button type="button" class="btn-close" data-modal-close aria-label="Tutup"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="add_rule" value="1">
                        
                        <div class="mb-3">
                            <label for="rule-ipk" class="form-label">Kategori IPK</label>
                            <select class="form-select" id="rule-ipk" name="ipk" required>
                                        <option value="rendah">Rendah</option>
                                        <option value="sedang">Sedang</option>
                                        <option value="tinggi">Tinggi</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="rule-nilai-mk" class="form-label">Kategori Nilai MK</label>
                            <select class="form-select" id="rule-nilai-mk" name="nilai_mk" required>
                                <option value="lemah">Lemah</option>
                                <option value="cukup">Cukup</option>
                                <option value="kuat">Kuat</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="rule-sks" class="form-label">Kategori % SKS</label>
                            <select class="form-select" id="rule-sks" name="sks" required>
                                <option value="rendah">Rendah</option>
                                <option value="sedang">Sedang</option>
                                <option value="tinggi">Tinggi</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="rule-minat" class="form-label">Kategori Minat</label>
                            <select class="form-select" id="rule-minat" name="minat" required>
                                <option value="rendah">Rendah</option>
                                <option value="sedang">Sedang</option>
                                <option value="tinggi">Tinggi</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="rule-komitmen" class="form-label">Kategori Komitmen</label>
                            <select class="form-select" id="rule-komitmen" name="komitmen" required>
                                <option value="lemah">Lemah</option>
                                <option value="cukup">Cukup</option>
                                <option value="kuat">Kuat</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="rule-konsentrasi" class="form-label">Output Konsentrasi</label>
                            <select class="form-select" id="rule-konsentrasi" name="konsentrasi_id" required>
                                <?php foreach ($konsentrasi_list as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= $k['nama_konsentrasi'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-modal-close>Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm">Simpan Aturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Universitas Perjuangan Tasikmalaya.</p>
        <small>Sistem Pendukung Keputusan - Metode Fuzzy Mamdani</small>
    </footer>

    <script src="../../assets/js/validasi-form.js"></script>
    <script src="../../assets/js/ui-native.js"></script>
    
    <script>
        document.querySelectorAll('.delete-rule').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('delete-rule-id').value = button.dataset.ruleId;
                document.getElementById('delete-rule-code').textContent = button.dataset.ruleCode;
            });
        });

    </script>
</body>
</html>