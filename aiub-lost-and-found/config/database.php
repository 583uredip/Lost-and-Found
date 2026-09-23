<?php
/**
 * Database Configuration
 * Update these 4 values to match your MySQL / hosting environment.
 */
define('DB_HOST', 'sql107.infinityfree.com');
define('DB_NAME', 'if0_42962477_lostfound');
define('DB_USER', 'if0_42962477');
define('DB_PASS', 'QQLh8PcV0J9jGNy');

// Site-wide settings
define('SITE_NAME', 'AIUB Lost & Found');
define('SITE_URL', 'https://aiublostfound.infinityfreeapp.com');
define('UPLOAD_DIR_LOST', __DIR__ . '/../uploads/lost/');
define('UPLOAD_DIR_FOUND', __DIR__ . '/../uploads/found/');

// Matching threshold: minimum score (0-100) to consider two items a possible match
define('MATCH_THRESHOLD', 55);

/* ------------------------------------------------------------------ */
/* Gmail SMTP Configuration                                           */
/* Replace with your own Gmail & App Password                         */
/* HOW TO GET APP PASSWORD:                                           */
/*   Google Account → Security → 2-Step Verification → App Passwords */
/* ------------------------------------------------------------------ */
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_USERNAME', 'redipbiswas8@gmail.com');
define('SMTP_PASSWORD', 'aozm kxiq kion rapx');
define('SMTP_PORT',     587);
define('SMTP_FROM_NAME', SITE_NAME);

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
