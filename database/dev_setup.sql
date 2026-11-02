CREATE DATABASE IF NOT EXISTS spk_fuzzy_dev
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE spk_fuzzy_dev;

CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY users_username_unique (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT NOT NULL AUTO_INCREMENT,
    nim VARCHAR(20) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    ipk DECIMAL(4,2) NOT NULL,
    nilai_mk DECIMAL(5,2) NOT NULL,
    sks DECIMAL(5,2) NOT NULL,
    minat DECIMAL(5,2) NOT NULL DEFAULT 0,
    komitmen DECIMAL(5,2) NOT NULL DEFAULT 0,
    tanggal_input TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY mahasiswa_nim_unique (nim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS konsentrasi (
    id INT NOT NULL AUTO_INCREMENT,
    kode_konsentrasi VARCHAR(10) NOT NULL,
    nama_konsentrasi VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY konsentrasi_kode_unique (kode_konsentrasi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS aturan_fuzzy (
    id INT NOT NULL AUTO_INCREMENT,
    kode_aturan VARCHAR(10) NOT NULL,
    ipk_kategori VARCHAR(20) NOT NULL,
    nilai_mk_kategori VARCHAR(20) NOT NULL,
    persen_sks_kategori VARCHAR(20) NOT NULL,
    minat_kategori VARCHAR(20) NOT NULL,
    komitmen_kategori VARCHAR(20) NOT NULL,
    konsentrasi_id INT NOT NULL,
    rule_text VARCHAR(500) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY aturan_kode_unique (kode_aturan),
    CONSTRAINT aturan_konsentrasi_fk
        FOREIGN KEY (konsentrasi_id) REFERENCES konsentrasi (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS hasil_rekomendasi (
    id INT NOT NULL AUTO_INCREMENT,
    id_mahasiswa INT NOT NULL,
    skor_rpl DECIMAL(5,2) NOT NULL,
    skor_jaringan DECIMAL(5,2) NOT NULL,
    skor_multimedia DECIMAL(5,2) NOT NULL,
    rekomendasi_utama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY hasil_mahasiswa_unique (id_mahasiswa),
    CONSTRAINT hasil_mahasiswa_fk
        FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO users (id, username, password, nama_lengkap)
VALUES (1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator Demo');

INSERT IGNORE INTO konsentrasi (id, kode_konsentrasi, nama_konsentrasi) VALUES
(1, 'KA', 'Kecerdasan Buatan'),
(2, 'SI', 'Sistem Informasi'),
(3, 'MM', 'Multimedia');

INSERT IGNORE INTO mahasiswa (id, nim, nama, ipk, nilai_mk, sks, minat, komitmen, tanggal_input) VALUES
(1, '2203010001', 'Demo Mahasiswa 1', 3.80, 88, 85, 2.80, 8.00, CURRENT_TIMESTAMP - INTERVAL 1 DAY),
(2, '2203010002', 'Demo Mahasiswa 2', 3.45, 80, 75, 2.30, 7.00, CURRENT_TIMESTAMP - INTERVAL 2 DAY),
(3, '2203010003', 'Demo Mahasiswa 3', 3.10, 72, 65, 1.80, 6.00, CURRENT_TIMESTAMP - INTERVAL 3 DAY),
(4, '2203010004', 'Demo Mahasiswa 4', 3.65, 91, 90, 2.90, 8.50, CURRENT_TIMESTAMP - INTERVAL 4 DAY),
(5, '2203010005', 'Demo Mahasiswa 5', 2.95, 68, 55, 1.40, 4.50, CURRENT_TIMESTAMP - INTERVAL 5 DAY);

INSERT IGNORE INTO hasil_rekomendasi
    (id_mahasiswa, skor_rpl, skor_jaringan, skor_multimedia, rekomendasi_utama) VALUES
(1, 62.50, 88.00, 54.25, 'Kecerdasan Buatan'),
(2, 78.25, 70.50, 58.00, 'Sistem Informasi'),
(3, 64.75, 56.00, 73.50, 'Multimedia'),
(4, 59.00, 92.25, 61.50, 'Kecerdasan Buatan'),
(5, 68.50, 61.25, 72.00, 'Multimedia');

INSERT IGNORE INTO aturan_fuzzy
    (kode_aturan, ipk_kategori, nilai_mk_kategori, persen_sks_kategori, minat_kategori, komitmen_kategori, konsentrasi_id, rule_text)
SELECT
    CONCAT('R', LPAD(
        ipk.level * 81 + nilai.level * 27 + sks.level * 9 + minat.level * 3 + komitmen.level,
        3,
        '0'
    )),
    ipk.label,
    nilai.label,
    sks.label,
    minat.label,
    komitmen.label,
    CASE
        WHEN minat.level = 2 AND komitmen.level = 2 THEN 1
        WHEN nilai.level = 2 AND ipk.level = 2 THEN 1
        WHEN minat.level = 0 AND komitmen.level = 0 THEN 3
        WHEN nilai.level = 0 AND ipk.level = 0 THEN 3
        ELSE 2
    END,
    CONCAT(
        'IF IPK=', ipk.label,
        ' AND NILAI_MK=', nilai.label,
        ' AND %SKS=', sks.label,
        ' AND MINAT=', minat.label,
        ' AND KOMITMEN=', komitmen.label,
        ' THEN ',
        CASE
            WHEN minat.level = 2 AND komitmen.level = 2 THEN 'KECERDASAN BUATAN'
            WHEN nilai.level = 2 AND ipk.level = 2 THEN 'KECERDASAN BUATAN'
            WHEN minat.level = 0 AND komitmen.level = 0 THEN 'MULTIMEDIA'
            WHEN nilai.level = 0 AND ipk.level = 0 THEN 'MULTIMEDIA'
            ELSE 'SISTEM INFORMASI'
        END
    )
FROM
    (SELECT 0 AS level, 'rendah' AS label UNION ALL SELECT 1, 'sedang' UNION ALL SELECT 2, 'tinggi') AS ipk
CROSS JOIN
    (SELECT 0 AS level, 'lemah' AS label UNION ALL SELECT 1, 'cukup' UNION ALL SELECT 2, 'kuat') AS nilai
CROSS JOIN
    (SELECT 0 AS level, 'rendah' AS label UNION ALL SELECT 1, 'sedang' UNION ALL SELECT 2, 'tinggi') AS sks
CROSS JOIN
    (SELECT 0 AS level, 'rendah' AS label UNION ALL SELECT 1, 'sedang' UNION ALL SELECT 2, 'tinggi') AS minat
CROSS JOIN
    (SELECT 0 AS level, 'lemah' AS label UNION ALL SELECT 1, 'cukup' UNION ALL SELECT 2, 'kuat') AS komitmen;
