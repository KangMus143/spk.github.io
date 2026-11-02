<?php
require_once '../includes/database.php';
require_once '../includes/fuzzy_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Get Input & Sanitize
    $nim = trim($_POST['nim'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    
    function toFloat($val) {
        $val = str_replace(',', '.', (string)$val);
        return floatval($val);
    }

    $raw_ipk = $_POST['ipk'] ?? '';
    $raw_nilai_mk_kunci = $_POST['nilai_mk_kunci'] ?? '';
    $raw_persen_sks_ab = $_POST['persen_sks_ab'] ?? '';
    $raw_minat_pribadi = $_POST['minat_pribadi'] ?? '';
    $raw_komitmen_belajar = $_POST['komitmen_belajar'] ?? '';
    $ipk = toFloat($raw_ipk);
    $nilai_mk_kunci = toFloat($raw_nilai_mk_kunci);
    $persen_sks_ab = toFloat($raw_persen_sks_ab);
    $minat_pribadi = toFloat($raw_minat_pribadi);
    $komitmen_belajar = toFloat($raw_komitmen_belajar);

    // Server-side Validation
    $errors = [];
    if (!preg_match('/^\d{10}$/', $nim)) $errors[] = "NIM harus terdiri dari 10 digit angka!";
    $angkatan = substr($nim, 0, 2);
    if (!in_array($angkatan, ['19', '20', '21', '22', '23', '24', '25', '26'])) $errors[] = "Tahun angkatan tidak valid!";
    if (substr($nim, 2, 4) !== '0301') $errors[] = "Kode Prodi harus '0301'!";
    if (mb_strlen($nama) < 3 || mb_strlen($nama) > 100) $errors[] = "Nama harus terdiri dari 3 sampai 100 karakter!";
    if (!is_numeric($raw_ipk)) $errors[] = "IPK harus berupa angka!";
    if (!is_numeric($raw_nilai_mk_kunci)) $errors[] = "Nilai MK harus berupa angka!";
    if (!is_numeric($raw_persen_sks_ab)) $errors[] = "Persentase SKS harus berupa angka!";
    if (!is_numeric($raw_minat_pribadi)) $errors[] = "Minat harus berupa angka!";
    if (!is_numeric($raw_komitmen_belajar)) $errors[] = "Komitmen harus berupa angka!";
    if ($ipk < 0 || $ipk > 4.0) $errors[] = "IPK harus 0.00 - 4.00";
    if ($nilai_mk_kunci < 0 || $nilai_mk_kunci > 100) $errors[] = "Nilai MK harus 0 - 100";
    if ($persen_sks_ab < 0 || $persen_sks_ab > 100) $errors[] = "SKS harus 0 - 100";
    if ($minat_pribadi < 1 || $minat_pribadi > 100) $errors[] = "Minat harus 1 - 100";
    if ($komitmen_belajar < 1 || $komitmen_belajar > 100) $errors[] = "Komitmen harus 1 - 100";

    if (!empty($errors)) {
        echo "<script>alert('" . implode("\\n", $errors) . "'); window.history.back();</script>";
        exit;
    }

    // Konversi skala untuk database (Minat 1-3, Komitmen 3-9)
    $minat_db = ($minat_pribadi / 100) * 2 + 1;
    $komitmen_db = ($komitmen_belajar / 100) * 6 + 3;

    try {
        $pdo->beginTransaction();

        // 2. Insert or Update Mahasiswa
        $stmt_check = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ?");
        $stmt_check->execute([$nim]);
        $existing_student = $stmt_check->fetch();

        if ($existing_student) {
            $mahasiswa_id = $existing_student['id'];
            $stmt = $pdo->prepare("UPDATE mahasiswa SET nama=?, ipk=?, nilai_mk=?, sks=?, minat=?, komitmen=? WHERE id=?");
            $stmt->execute([$nama, $ipk, $nilai_mk_kunci, $persen_sks_ab, $minat_db, $komitmen_db, $mahasiswa_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO mahasiswa (nim, nama, ipk, nilai_mk, sks, minat, komitmen) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nim, $nama, $ipk, $nilai_mk_kunci, $persen_sks_ab, $minat_db, $komitmen_db]);
            $mahasiswa_id = $pdo->lastInsertId();
        }

        // 3. Process Fuzzy Logic
        $data_fuzzy = [
            'ipk' => $ipk,
            'nilai_mk_kunci' => $nilai_mk_kunci,
            'persen_sks_ab' => $persen_sks_ab,
            'minat_pribadi' => $minat_pribadi,
            'komitmen_belajar' => $komitmen_belajar
        ];
        
        $scores = processFuzzyMamdani($data_fuzzy, $pdo);
        
        // 4. Tentukan Rekomendasi Terbaik
        $best_score = -1;
        $recommendation_name = "";
        
        foreach ($scores as $konsentrasi => $score) {
            if ($score > $best_score) {
                $best_score = $score;
                $recommendation_name = $konsentrasi;
            }
        }

        // Map hasil fuzzy ke kolom tabel hasil_rekomendasi
        $skor_rpl = $scores['Sistem Informasi'];
        $skor_jaringan = $scores['Kecerdasan Buatan'];
        $skor_multimedia = $scores['Multimedia'];

        // 5. Save Results (MENGGUNAKAN NAMA KOLOM YANG BENAR: skor_rpl, skor_jaringan, skor_multimedia)
        $stmt_check_hasil = $pdo->prepare("SELECT id FROM hasil_rekomendasi WHERE id_mahasiswa = ?");
        $stmt_check_hasil->execute([$mahasiswa_id]);
        $existing_hasil = $stmt_check_hasil->fetch();

        if ($existing_hasil) {
             $stmt_hasil = $pdo->prepare("UPDATE hasil_rekomendasi SET skor_rpl=?, skor_jaringan=?, skor_multimedia=?, rekomendasi_utama=? WHERE id_mahasiswa=?");
             $stmt_hasil->execute([
                $skor_rpl, $skor_jaringan, $skor_multimedia, $recommendation_name, $mahasiswa_id
            ]);
        } else {
            $stmt_hasil = $pdo->prepare("INSERT INTO hasil_rekomendasi (id_mahasiswa, skor_rpl, skor_jaringan, skor_multimedia, rekomendasi_utama) VALUES (?, ?, ?, ?, ?)");
            $stmt_hasil->execute([
                $mahasiswa_id, $skor_rpl, $skor_jaringan, $skor_multimedia, $recommendation_name
            ]);
        }

        $pdo->commit();

        // 6. Redirect to Results Page
        header("Location: hasil.php?nim=" . urlencode($nim));
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log($e->getMessage());
        die("Proses analisis gagal. Periksa kembali data dan aturan, lalu coba lagi.");
    }
} else {
    header("Location: input.php");
    exit;
}
?>