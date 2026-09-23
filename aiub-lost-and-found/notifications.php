<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$uid = current_user_id();

// Mark all as read on visit
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$uid]);

// Fetch all notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$uid]);
$notifications = $stmt->fetchAll();

$pageTitle = 'Notifications';
require_once __DIR__ . '/includes/header.php';
?>

<div class="notif-center">
  <div class="container" style="max-width:720px;">

    <div class="notif-page-header" data-aos="fade-up">
      <div>
        <h1 style="font-size:1.8rem;font-weight:800;letter-spacing:-0.5px;">
          <i class="fa-regular fa-bell me-2" style="color:var(--accent);"></i>Notifications
        </h1>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:4px;">
          <?= count($notifications) ?> total notification<?= count($notifications)!==1?'s':'' ?>
        </p>
      </div>
      <?php if (!empty($notifications)): ?>
        <a href="dashboard.php" class="btn-ghost-custom">
          <i class="fa-solid fa-gauge"></i> Dashboard
        </a>
      <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
      <div class="empty-state" data-aos="fade-up">
        <div class="empty-icon" style="font-size:4rem;"><i class="fa-regular fa-bell-slash"></i></div>
        <h5>No notifications yet</h5>
        <p>When a match is found for your lost item, you'll be notified here and by email.</p>
        <div class="d-flex gap-2 justify-content-center mt-3">
          <a href="report-lost.php" class="btn-primary-custom">
            <i class="fa-solid fa-triangle-exclamation"></i> Report Lost Item
          </a>
          <a href="browse-found.php" class="btn-outline-custom">Browse Found Items</a>
        </div>
      </div>
    <?php else: ?>
      <div data-aos="fade-up">
        <?php
        $prevDate = null;
        foreach ($notifications as $notif):
          $notifDate = date('d F Y', strtotime($notif['created_at']));
          $today = date('d F Y');
          $yesterday = date('d F Y', strtotime('-1 day'));
          $displayDate = $notifDate === $today ? 'Today' : ($notifDate === $yesterday ? 'Yesterday' : $notifDate);

          if ($notifDate !== $prevDate):
            $prevDate = $notifDate;
        ?>
          <div style="color:var(--text-muted);font-size:0.75rem;font-weight:700;text-transform:uppercase;
               letter-spacing:0.8px;margin:24px 0 10px;display:flex;align-items:center;gap:10px;">
            <?= $displayDate ?>
            <span style="flex:1;height:1px;background:var(--border);display:block;"></span>
          </div>
        <?php endif; ?>

          <?php
          $link = $notif['link'] ? SITE_URL . '/' . ltrim($notif['link'], '/') : '#';
          $isMatch = strpos($notif['title'], 'match') !== false || strpos($notif['title'], 'Match') !== false;
          ?>
          <a href="<?= clean($link) ?>"
             class="notif-item <?= !$notif['is_read'] ? 'unread' : '' ?>"
             style="text-decoration:none;">
            <div class="notif-icon-wrap" style="background:<?= $isMatch ? 'var(--gold-soft)' : 'var(--primary-glow)' ?>;">
              <i class="fa-solid <?= $isMatch ? 'fa-link' : 'fa-bell' ?>"
                 style="color:<?= $isMatch ? 'var(--gold)' : '#f4a0b5' ?>;"></i>
            </div>
              <?php 
                $cleanTitle = str_replace(['\ud83d\udd14', '\u{1f514}'], '🔔', $notif['title']);
              ?>
              <p class="notif-title"><?= clean($cleanTitle) ?></p>
              <p class="notif-message"><?= clean($notif['message']) ?></p>
              <p class="notif-time">
                <i class="fa-regular fa-clock"></i>
                <?= date('h:i A', strtotime($notif['created_at'])) ?>
                &bull; <?= $displayDate ?>
              </p>
            </div>
            <?php if (!$notif['is_read']): ?>
              <div class="notif-unread-dot"></div>
            <?php endif; ?>
          </a>

        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
