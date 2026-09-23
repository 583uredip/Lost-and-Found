<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/matching.php';
admin_require_login();

$type = $_POST['type'] ?? '';
$uid  = (int)($_POST['user_id']    ?? 0);
$name = trim($_POST['item_name']   ?? '');
$cat  = trim($_POST['category']    ?? '');
$loc  = trim($_POST['location']    ?? '');
$desc = trim($_POST['description'] ?? '');
$col  = trim($_POST['color']       ?? '');
$brd  = trim($_POST['brand']       ?? '');

if (!$uid || !$name || !$cat || !$loc || !$desc) {
    header('Location: dashboard.php?tab=' . ($type==='lost'?'lost':'found'));
    exit;
}

if ($type === 'lost') {
    $date = trim($_POST['date_lost'] ?? date('Y-m-d'));
    $pdo->prepare("INSERT INTO lost_items (user_id,item_name,category,description,color,brand,location,date_lost) VALUES (?,?,?,?,?,?,?,?)")
        ->execute([$uid,$name,$cat,$desc,$col,$brd,$loc,$date]);
    $newId = $pdo->lastInsertId();
    match_new_lost_item($pdo, $newId);
    header('Location: dashboard.php?tab=lost&added=1');
} else {
    $date = trim($_POST['date_found'] ?? date('Y-m-d'));
    $pdo->prepare("INSERT INTO found_items (user_id,item_name,category,description,color,brand,location,date_found) VALUES (?,?,?,?,?,?,?,?)")
        ->execute([$uid,$name,$cat,$desc,$col,$brd,$loc,$date]);
    $newId = $pdo->lastInsertId();
    match_new_found_item($pdo, $newId);
    header('Location: dashboard.php?tab=found&added=1');
}
exit;
