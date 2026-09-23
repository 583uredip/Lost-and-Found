<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
admin_require_login();

$fn   = trim($_POST['full_name'] ?? '');
$em   = trim($_POST['email']     ?? '');
$ph   = trim($_POST['phone']     ?? '');
$sid  = trim($_POST['student_id']?? '');
$pass = trim($_POST['password']  ?? '');

if ($fn && $em && $ph && $pass) {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    try {
        $pdo->prepare("INSERT INTO users (full_name,email,phone,student_id,password) VALUES (?,?,?,?,?)")
            ->execute([$fn, $em, $ph, $sid ?: null, $hash]);
    } catch (PDOException $e) {
        // duplicate email etc.
    }
}
header('Location: dashboard.php?tab=users&added=1');
exit;
