# spk.github.io
App SPK Logika Fuzzy

## Sistem Pendukung Keputusan Pemilihan Konsentrasi

Aplikasi PHP untuk rekomendasi konsentrasi menggunakan metode Fuzzy Mamdani.
GitHub menyimpan source code ini; GitHub Pages tidak menjalankan PHP. Untuk
menjalankan aplikasi online, gunakan hosting yang mendukung PHP dan MySQL atau
MariaDB.

### Menjalankan secara lokal dengan XAMPP

1. Pastikan MariaDB/MySQL XAMPP aktif.
2. Impor `database/dev_setup.sql` melalui phpMyAdmin.
3. Jalankan server dari folder proyek:

   ```powershell
   C:\xampp\php\php.exe -S 127.0.0.1:8000 -t .
   ```

4. Buka `http://127.0.0.1:8000`.

Database demo yang dibuat bernama `spk_fuzzy_dev`. Akun lokal demo adalah
`admin` / `password`; jangan gunakan akun ini pada layanan publik.

Konfigurasi database dapat diberikan melalui environment variables
`DB_HOST`, `DB_USER`, `DB_PASS`, dan `DB_NAME`. Jangan menyimpan kredensial
hosting di source code. Sebelum deployment, nonaktifkan tampilan error PHP
untuk pengunjung dan gunakan kredensial database yang berbeda dari akun demo.

### Struktur aplikasi

- `index.php`: halaman beranda.
- `pages/input.php`: formulir analisis SPK.
- `pages/hasil.php`: hasil rekomendasi.
- `pages/admin/`: login, dashboard, dan manajemen aturan.
- `assets/css/` dan `assets/js/`: tampilan dan interaksi antarmuka.
- `includes/`: koneksi database, autentikasi, dan fungsi fuzzy.
- `database/dev_setup.sql`: skema dan data demo lokal.
