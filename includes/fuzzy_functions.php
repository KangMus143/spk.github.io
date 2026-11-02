<?php

function processFuzzyMamdani($data, $pdo) {
    // 1. Ambil 245 Rule dari database
    $stmt = $pdo->query("SELECT * FROM aturan_fuzzy");
    $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Siapkan variabel penampung (Agregasi MAX)
    $outputs = [
        'Kecerdasan Buatan' => 0,
        'Sistem Informasi'  => 0,
        'Multimedia'        => 0
    ];

    // 3. Looping semua rule
    foreach ($rules as $rule) {
        $ipk_kat = strtolower(trim($rule['ipk_kategori']));
        $mk_kat  = strtolower(trim($rule['nilai_mk_kategori']));
        $sks_kat = strtolower(trim($rule['persen_sks_kategori']));
        $mnt_kat = strtolower(trim($rule['minat_kategori']));
        $kmt_kat = strtolower(trim($rule['komitmen_kategori']));
        
        // Fungsi pembantu mengecek kecocokan (Linear Up/Down)
        // Jika input mahasiswa sesuai kategori rule, nilainya mendekati 1 (100%)
        $val_ipk = 0;
        if ($ipk_kat == 'tinggi') $val_ipk = max(0, min(1, ($data['ipk'] - 2.75) / (4.0 - 2.75)));
        elseif ($ipk_kat == 'sedang') $val_ipk = max(0, min(1, 1 - abs(($data['ipk'] - 2.75) / 1.25)));
        elseif ($ipk_kat == 'rendah') $val_ipk = max(0, min(1, (3.0 - $data['ipk']) / 1.0));

        $val_mk = 0;
        if ($mk_kat == 'kuat') $val_mk = max(0, min(1, ($data['nilai_mk_kunci'] - 65) / 35));
        elseif ($mk_kat == 'cukup') $val_mk = max(0, min(1, 1 - abs(($data['nilai_mk_kunci'] - 70) / 30)));
        elseif ($mk_kat == 'lemah') $val_mk = max(0, min(1, (80 - $data['nilai_mk_kunci']) / 30));

        $val_sks = 0;
        if ($sks_kat == 'tinggi') $val_sks = max(0, min(1, ($data['persen_sks_ab'] - 60) / 40));
        elseif ($sks_kat == 'sedang') $val_sks = max(0, min(1, 1 - abs(($data['persen_sks_ab'] - 60) / 40)));
        elseif ($sks_kat == 'rendah') $val_sks = max(0, min(1, (60 - $data['persen_sks_ab']) / 40));

        $val_mnt = 0;
        if ($mnt_kat == 'tinggi') $val_mnt = max(0, min(1, ($data['minat_pribadi'] - 50) / 50));
        elseif ($mnt_kat == 'sedang') $val_mnt = max(0, min(1, 1 - abs(($data['minat_pribadi'] - 50) / 50)));
        elseif ($mnt_kat == 'rendah') $val_mnt = max(0, min(1, (50 - $data['minat_pribadi']) / 50));

        $val_kmt = 0;
        if ($kmt_kat == 'kuat') $val_kmt = max(0, min(1, ($data['komitmen_belajar'] - 50) / 50));
        elseif ($kmt_kat == 'cukup') $val_kmt = max(0, min(1, 1 - abs(($data['komitmen_belajar'] - 50) / 50)));
        elseif ($kmt_kat == 'lemah') $val_kmt = max(0, min(1, (50 - $data['komitmen_belajar']) / 50));

        // Ambil nilai MIN dari semua kriteria (Alpha Predikat)
        $alpha = min($val_ipk, $val_mk, $val_sks, $val_mnt, $val_kmt);
        
        // Simpan nilai tertinggi (MAX) ke konsentrasi yang sesuai
        if ($rule['konsentrasi_id'] == 1) $outputs['Kecerdasan Buatan'] = max($outputs['Kecerdasan Buatan'], $alpha);
        if ($rule['konsentrasi_id'] == 2) $outputs['Sistem Informasi'] = max($outputs['Sistem Informasi'], $alpha);
        if ($rule['konsentrasi_id'] == 3) $outputs['Multimedia'] = max($outputs['Multimedia'], $alpha);
    }

    // 4. Defuzzifikasi Sederhana (Konversi Alpha 0-1 ke Skor 0-100)
    // Kita beri bobot dasar 30, ditambah 70 * nilai alpha
    $hasil = [];
    foreach ($outputs as $konsentrasi => $alpha) {
        $hasil[$konsentrasi] = round(30 + ($alpha * 70), 2); 
    }

    return $hasil;
}
?>