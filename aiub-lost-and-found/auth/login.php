<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        redirect('dashboard.php');
    } else {
        flash('error', 'Invalid email or password. Please try again.');
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login | <?= SITE_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="auth-wrapper">
  <!-- LEFT PANEL -->
  <div class="auth-panel-left">
    <div class="brand-logo">
      <i class="fa-solid fa-magnifying-glass"></i>
    </div>
    <h2>Welcome back to<br>AIUB Lost &amp; Found</h2>
    <p>Log in to track your reports, view matches and get notified when your lost items are found.</p>

    <div class="auth-features">
      <div class="auth-feature-item">
        <div class="auth-feature-icon"><i class="fa-solid fa-brain"></i></div>
        <span>AI-powered matching engine compares 6+ item attributes</span>
      </div>
      <div class="auth-feature-item">
        <div class="auth-feature-icon"><i class="fa-solid fa-bell"></i></div>
        <span>Instant email &amp; in-app notification on match</span>
      </div>
      <div class="auth-feature-item">
        <div class="auth-feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <span>Secure, private &mdash; only AIUB community</span>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="auth-panel-right">
    <div class="auth-form-wrap">

      <?php if ($msg = flash('error')): ?>
        <div class="alert alert-danger mb-4" style="border-radius:12px;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);color:#f87171;border-left:3px solid #ef4444;">
          <i class="fa-solid fa-circle-xmark me-2"></i><?= clean($msg) ?>
        </div>
      <?php endif; ?>

      <div class="form-card">
        <div class="form-card-header">
          <div class="form-card-icon"><i class="fa-solid fa-right-to-bracket"></i></div>
          <h3>Welcome Back</h3>
          <p class="form-subtitle">Sign in to your AIUB Lost &amp; Found account</p>
        </div>

        <form method="POST" id="loginForm" novalidate>
          <div class="mb-4">
            <label class="form-label" for="email">Email Address</label>
            <div style="position:relative;">
              <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;">
                <i class="fa-regular fa-envelope"></i>
              </span>
              <input type="email" id="email" name="email" class="form-control"
                     style="padding-left:40px!important;"
                     placeholder="your@email.com" required
                     value="<?= clean($_POST['email'] ?? '') ?>">
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label" for="password">Password</label>
            <div style="position:relative;">
              <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;">
                <i class="fa-solid fa-lock"></i>
              </span>
              <input type="password" id="password" name="password" class="form-control"
                     style="padding-left:40px!important;padding-right:44px!important;"
                     placeholder="Enter your password" required>
              <button type="button" id="togglePwd"
                      style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:0.85rem;">
                <i class="fa-regular fa-eye" id="togglePwdIcon"></i>
              </button>
            </div>
          </div>

          <button type="submit" class="btn-primary-custom btn-submit">
            <i class="fa-solid fa-right-to-bracket"></i> Sign In
          </button>
        </form>

        <div class="form-divider">or</div>

        <div class="form-footer-link">
          Don't have an account? <a href="register.php">Create one free</a>
        </div>

        <div class="text-center mt-3">
          <a href="<?= SITE_URL ?>/index.php" style="font-size:0.8rem;color:var(--text-muted);">
            <i class="fa-solid fa-arrow-left me-1"></i>Back to Home
          </a>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const toggleBtn = document.getElementById('togglePwd');
  const pwdInput  = document.getElementById('password');
  const toggleIcon = document.getElementById('togglePwdIcon');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const isPass = pwdInput.type === 'password';
      pwdInput.type = isPass ? 'text' : 'password';
      toggleIcon.className = isPass ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
    });
  }
</script>
</body>
</html>
