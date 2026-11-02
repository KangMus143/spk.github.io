<?php
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'spk_fuzzy_dev');

// Application Configuration
define('APP_NAME', 'SPK Pemilihan Konsentrasi - Fuzzy Mamdani');
define('BASE_URL', getenv('BASE_URL') ?: 'http://127.0.0.1:8000');

// Error Reporting (Turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>