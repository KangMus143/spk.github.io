<?php
require_once '../../includes/database.php';
require_once '../../includes/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (login($username, $password)) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!-- NIM: 2203010233, nama: Ihsan Musyaffa, topik: Sistem Pendukung Keputusan Pemilihan Konsentrasi -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SPK Konsentrasi</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body class="admin-login-page">
    <header class="site-header">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="../../index.php">
                    <img src="../../assets/images/logo_unper.png" alt="Logo UNPER" class="me-2">
                    Universitas Perjuangan Tasikmalaya
                </a>
                <button class="navbar-toggler" type="button" data-nav-toggle="#loginNavigation" aria-controls="loginNavigation" aria-expanded="false" aria-label="Buka menu navigasi">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="navbar-collapse" id="loginNavigation">
                    <div class="navbar-nav ms-auto">
                        <a class="nav-link" href="../../index.php">Beranda</a>
                        <a class="nav-link aktif" href="login.php" aria-current="page">Login Admin</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="site-main">
    <div class="container login-container">
        <div class="card login-card">
            <!-- Header dengan Background Biru -->
            <div class="card-header">
                <h1>Login Admin</h1>
            </div>
            
            <div class="card-body">
                <!-- Pesan Error -->
                <?php if ($error): ?>
                    <div class="alert alert-danger mb-3">
                        <i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <!-- Form Login -->
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-login">Login</button>
                </form>
            </div>
            
            <!-- Footer dengan Link Beranda -->
            <div class="card-footer">
                <a href="../../index.php">Kembali ke Beranda</a>
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
</body>
</html>