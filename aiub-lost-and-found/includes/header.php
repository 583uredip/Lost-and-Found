<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="AIUB Lost & Found — Report lost or found items on campus. Smart matching alerts you when your item is found.">
<title><?= isset($pageTitle) ? clean($pageTitle) . ' | ' . SITE_NAME : SITE_NAME ?></title>

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome 6 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<!-- AOS Animate On Scroll -->
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

<!-- Custom CSS -->
<link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- ── Navbar ────────────────────────────────────────────────── -->
<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container">
    <a class="navbar-brand-custom" href="<?= SITE_URL ?>/index.php">
      <div class="brand-icon">
        <i class="fa-solid fa-magnifying-glass"></i>
      </div>
      <span>AIUB <span class="brand-text"><span>Lost</span> &amp; Found</span></span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/index.php">
            <i class="fa-solid fa-house me-1"></i>Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'browse-lost.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/browse-lost.php">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>Lost Items
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'browse-found.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/browse-found.php">
            <i class="fa-solid fa-hand-holding me-1"></i>Found Items
          </a>
        </li>

        <?php if (is_logged_in()): ?>
          <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'report-lost.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/report-lost.php">
              Report Lost
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'report-found.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/report-found.php">
              Report Found
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/dashboard.php">
              <i class="fa-solid fa-gauge me-1"></i>Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link position-relative <?= basename($_SERVER['PHP_SELF']) === 'notifications.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/notifications.php">
              <div class="notif-bell-wrap">
                <i class="fa-regular fa-bell"></i>
                <?php
                  $unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
                  $unreadStmt->execute([current_user_id()]);
                  $unreadCount = (int) $unreadStmt->fetchColumn();
                ?>
                <?php if ($unreadCount > 0): ?>
                  <span class="notif-badge"><?= $unreadCount ?></span>
                <?php endif; ?>
              </div>
            </a>
          </li>
          <li class="nav-item ms-lg-2">
            <a class="btn-nav-primary nav-link" href="<?= SITE_URL ?>/auth/logout.php">
              <i class="fa-solid fa-right-from-bracket"></i>
              <span class="d-none d-lg-inline ms-1"><?= clean(explode(' ', $_SESSION['user_name'])[0]) ?></span>
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item ms-lg-1">
            <a class="nav-link" href="<?= SITE_URL ?>/auth/login.php">Login</a>
          </li>
          <li class="nav-item ms-lg-2">
            <a class="btn-nav-primary nav-link" href="<?= SITE_URL ?>/auth/register.php">
              <i class="fa-solid fa-user-plus me-1"></i>Sign Up
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- ── Flash messages ─────────────────────────────────────────── -->
<?php if ($msg = flash('success')): ?>
  <div class="container mt-3">
    <div class="alert alert-success alert-dismissible fade show flash-alert flash-success" role="alert">
      <i class="fa-solid fa-circle-check me-2"></i><?= clean($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
<?php endif; ?>

<?php if ($msg = flash('error')): ?>
  <div class="container mt-3">
    <div class="alert alert-danger alert-dismissible fade show flash-alert flash-error" role="alert">
      <i class="fa-solid fa-circle-xmark me-2"></i><?= clean($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
<?php endif; ?>
