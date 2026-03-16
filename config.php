<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'profileai_test_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SITE_URL', 'https://yourdomain.com');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('PROCESSED_DIR', __DIR__ . '/process/');

// Create directories if not exist
if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
if (!is_dir(PROCESSED_DIR)) mkdir(PROCESSED_DIR, 0755, true);

session_start();

?>

