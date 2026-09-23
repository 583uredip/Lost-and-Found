<?php
require_once __DIR__ . '/auth.php';
if (admin_is_logged_in()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if ($user === ADMIN_USERNAME && $pass === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php'); exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — AIUB Lost & Found</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#0a0a0f;min-height:100vh;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden}
body::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 20% 50%,rgba(122,12,46,0.3) 0%,transparent 60%),radial-gradient(ellipse at 80% 20%,rgba(185,28,28,0.2) 0%,transparent 50%);pointer-events:none}
.card{background:rgba(255,255,255,0.04);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:48px 40px;width:100%;max-width:420px;position:relative;z-index:1}
.logo{text-align:center;margin-bottom:32px}
.logo-icon{width:64px;height:64px;background:linear-gradient(135deg,#7a0c2e,#b91c1c);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;font-size:28px;color:#fff;margin-bottom:16px;box-shadow:0 8px 32px rgba(122,12,46,0.4)}
.logo h1{font-size:22px;font-weight:800;color:#fff;margin-bottom:4px}
.logo p{font-size:13px;color:rgba(255,255,255,0.45);letter-spacing:2px;text-transform:uppercase}
.form-group{margin-bottom:20px}
label{display:block;font-size:13px;font-weight:600;color:rgba(255,255,255,0.6);margin-bottom:8px;letter-spacing:0.5px}
.input-wrap{position:relative}
.input-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,0.3);font-size:14px}
input{width:100%;padding:13px 14px 13px 42px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:12px;color:#fff;font-size:15px;font-family:'Inter',sans-serif;transition:all .2s}
input:focus{outline:none;border-color:#7a0c2e;background:rgba(122,12,46,0.1);box-shadow:0 0 0 3px rgba(122,12,46,0.2)}
input::placeholder{color:rgba(255,255,255,0.25)}
.btn{width:100%;padding:14px;background:linear-gradient(135deg,#7a0c2e,#b91c1c);border:none;border-radius:12px;color:#fff;font-size:15px;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;transition:all .2s;margin-top:8px}
.btn:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(122,12,46,0.5)}
.error{background:rgba(220,38,38,0.15);border:1px solid rgba(220,38,38,0.3);border-radius:10px;padding:12px 16px;color:#f87171;font-size:13px;margin-bottom:20px;display:flex;align-items:center;gap:8px}
.badge{text-align:center;margin-top:24px;font-size:12px;color:rgba(255,255,255,0.25)}
.floating{position:absolute;border-radius:50%;filter:blur(60px);pointer-events:none}
.f1{width:300px;height:300px;background:rgba(122,12,46,0.15);top:-100px;right:-100px}
.f2{width:200px;height:200px;background:rgba(185,28,28,0.1);bottom:-80px;left:-80px}
</style>
</head>
<body>
<div class="floating f1"></div>
<div class="floating f2"></div>
<div class="card">
  <div class="logo">
    <div class="logo-icon"><i class="fa-solid fa-shield-halved"></i></div>
    <h1>Admin Panel</h1>
    <p>AIUB Lost &amp; Found</p>
  </div>

  <?php if ($error): ?>
  <div class="error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>USERNAME</label>
      <div class="input-wrap">
        <i class="fa-solid fa-user"></i>
        <input type="text" name="username" placeholder="Admin username" required autocomplete="off">
      </div>
    </div>
    <div class="form-group">
      <label>PASSWORD</label>
      <div class="input-wrap">
        <i class="fa-solid fa-lock"></i>
        <input type="password" name="password" placeholder="Admin password" required>
      </div>
    </div>
    <button type="submit" class="btn"><i class="fa-solid fa-right-to-bracket"></i> &nbsp;Sign In to Admin</button>
  </form>

  <div class="badge"><i class="fa-solid fa-lock"></i> &nbsp;Restricted Access — Authorized Personnel Only</div>
</div>
</body>
</html>
