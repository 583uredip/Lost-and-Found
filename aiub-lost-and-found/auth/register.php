<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = clean($_POST['full_name'] ?? '');
    $email     = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone     = clean($_POST['phone'] ?? '');
    $studentId = clean($_POST['student_id'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$phone || !$password) {
        flash('error', 'Please fill in all required fields.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Please enter a valid email address.');
    } elseif (strlen($password) < 6) {
        flash('error', 'Password must be at least 6 characters.');
    } elseif ($password !== $confirm) {
        flash('error', 'Passwords do not match.');
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            flash('error', 'An account with this email already exists.');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                "INSERT INTO users (full_name, email, phone, student_id, password) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $email, $phone, $studentId, $hash]);
            flash('success', 'Account created! Welcome to AIUB Lost & Found. Please log in.');
            redirect('auth/login.php');
        }
    }
}

$pageTitle = 'Sign Up';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign Up | <?= SITE_NAME ?></title>
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
    <h2>Join the AIUB<br>Lost &amp; Found Community</h2>
    <p>Create a free account and never lose hope of finding your belongings again. Our smart engine does the work for you.</p>

    <div class="auth-features">
      <div class="auth-feature-item">
        <div class="auth-feature-icon"><i class="fa-solid fa-upload"></i></div>
        <span>Report lost or found items with photos in seconds</span>
      </div>
      <div class="auth-feature-item">
        <div class="auth-feature-icon"><i class="fa-solid fa-link"></i></div>
        <span>Automatic matching across 6 item attributes</span>
      </div>
      <div class="auth-feature-item">
        <div class="auth-feature-icon"><i class="fa-solid fa-envelope"></i></div>
        <span>Email &amp; in-app alerts the moment a match is found</span>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="auth-panel-right">
    <div class="auth-form-wrap">

      <?php if ($msg = flash('error')): ?>
        <div class="alert mb-4" style="border-radius:12px;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);color:#f87171;border-left:3px solid #ef4444;">
          <i class="fa-solid fa-circle-xmark me-2"></i><?= clean($msg) ?>
        </div>
      <?php endif; ?>

      <div class="form-card">
        <div class="form-card-header">
          <div class="form-card-icon"><i class="fa-solid fa-user-plus"></i></div>
          <h3>Create Your Account</h3>
          <p class="form-subtitle">Join thousands of AIUB students &amp; staff</p>
        </div>

        <form method="POST" id="registerForm" class="needs-validation" novalidate>
          <div class="mb-3">
            <label class="form-label" for="full_name">Full Name *</label>
            <div style="position:relative;">
              <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;">
                <i class="fa-regular fa-user"></i>
              </span>
              <input type="text" id="full_name" name="full_name" class="form-control"
                     style="padding-left:40px!important;"
                     placeholder="Your full name" required
                     value="<?= clean($_POST['full_name'] ?? '') ?>">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="email">Email *</label>
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
            <div class="col-md-6">
              <label class="form-label" for="phone">Phone *</label>
              <div style="position:relative;">
                <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;">
                  <i class="fa-solid fa-phone"></i>
                </span>
                <input type="text" id="phone" name="phone" class="form-control"
                       style="padding-left:40px!important;"
                       placeholder="+880 1xxx-xxxxxx" required
                       value="<?= clean($_POST['phone'] ?? '') ?>">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="student_id">
              Student / Staff ID
              <span style="color:var(--text-muted);font-weight:400;"> (optional)</span>
            </label>
            <div style="position:relative;">
              <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;">
                <i class="fa-solid fa-id-card"></i>
              </span>
              <input type="text" id="student_id" name="student_id" class="form-control"
                     style="padding-left:40px!important;"
                     placeholder="e.g. 22-12345-1"
                     value="<?= clean($_POST['student_id'] ?? '') ?>">
            </div>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label" for="password">Password *</label>
              <div style="position:relative;">
                <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;">
                  <i class="fa-solid fa-lock"></i>
                </span>
                <input type="password" id="password" name="password" class="form-control"
                       style="padding-left:40px!important;padding-right:44px!important;"
                       placeholder="Min. 6 characters" required minlength="6">
                <button type="button" class="toggle-pwd" data-target="password"
                        style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:0.85rem;">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="confirm_password">Confirm Password *</label>
              <div style="position:relative;">
                <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;">
                  <i class="fa-solid fa-lock"></i>
                </span>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                       style="padding-left:40px!important;padding-right:44px!important;"
                       placeholder="Repeat password" required minlength="6">
                <button type="button" class="toggle-pwd" data-target="confirm_password"
                        style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:0.85rem;">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
            </div>
          </div>

          <button type="submit" class="btn-primary-custom btn-submit">
            <i class="fa-solid fa-user-plus"></i> Create Account
          </button>
        </form>

        <div class="form-divider">or</div>

        <div class="form-footer-link">
          Already have an account? <a href="login.php">Sign in</a>
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
  // Password toggle
  document.querySelectorAll('.toggle-pwd').forEach(btn => {
    btn.addEventListener('click', function() {
      const target = document.getElementById(this.dataset.target);
      const icon = this.querySelector('i');
      if (target.type === 'password') {
        target.type = 'text';
        icon.className = 'fa-regular fa-eye-slash';
      } else {
        target.type = 'password';
        icon.className = 'fa-regular fa-eye';
      }
    });
  });

  // Form validation
  document.getElementById('registerForm').addEventListener('submit', e => {
    const pwd = document.getElementById('password').value;
    const conf = document.getElementById('confirm_password').value;
    if (pwd !== conf) {
      e.preventDefault();
      alert('Passwords do not match.');
    }
  });
</script>
</body>
</html>
