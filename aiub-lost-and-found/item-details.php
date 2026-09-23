<?php
require_once __DIR__ . '/includes/functions.php';

$type = ($_GET['type'] ?? '') === 'found' ? 'found' : 'lost';
$id   = (int)($_GET['id'] ?? 0);

$table     = $type === 'found' ? 'found_items' : 'lost_items';
$dateCol   = $type === 'found' ? 'date_found' : 'date_lost';
$uploadDir = $type === 'found' ? 'uploads/found/' : 'uploads/lost/';

$stmt = $pdo->prepare(
    "SELECT i.*, u.full_name AS reporter_name, u.email AS reporter_email, u.phone AS reporter_phone
     FROM $table i JOIN users u ON u.id = i.user_id WHERE i.id = ?"
);
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    flash('error', 'Item not found.');
    redirect($type === 'found' ? 'browse-found.php' : 'browse-lost.php');
}

$isOwner = is_logged_in() && current_user_id() == $item['user_id'];

// Handle resolve
if ($isOwner && isset($_GET['resolve'])) {
    $pdo->prepare("UPDATE $table SET status = 'resolved' WHERE id = ?")->execute([$id]);
    flash('success', '🎉 Marked as resolved! Glad it worked out!');
    redirect("item-details.php?type=$type&id=$id");
}

// Handle sending/resending match email notification
if (isset($_GET['notify_match']) && is_logged_in()) {
    $matchId = (int)$_GET['notify_match'];
    $stmt = $pdo->prepare(
        "SELECT m.*, 
                l.id AS lost_id, l.user_id AS lost_user_id, l.item_name AS lost_name, l.category AS lost_category, l.location AS lost_location, l.date_lost,
                f.id AS found_id, f.user_id AS found_user_id, f.item_name AS found_name, f.category AS found_category, f.location AS found_location, f.date_found
         FROM matches m
         JOIN lost_items l ON l.id = m.lost_item_id
         JOIN found_items f ON f.id = m.found_item_id
         WHERE m.id = ?"
    );
    $stmt->execute([$matchId]);
    $mRow = $stmt->fetch();
    if ($mRow) {
        $lostArr  = [
            'id' => $mRow['lost_id'], 'user_id' => $mRow['lost_user_id'], 'item_name' => $mRow['lost_name'],
            'category' => $mRow['lost_category'], 'location' => $mRow['lost_location'], 'date_lost' => $mRow['date_lost']
        ];
        $foundArr = [
            'id' => $mRow['found_id'], 'user_id' => $mRow['found_user_id'], 'item_name' => $mRow['found_name'],
            'category' => $mRow['found_category'], 'location' => $mRow['found_location'], 'date_found' => $mRow['date_found']
        ];
        $sent = notify_match_found($pdo, $lostArr, $foundArr, $mRow['match_score']);
        if ($sent) {
            flash('success', '📧 Match notification email sent to Gmail successfully!');
        } else {
            flash('error', '⚠️ Email sending attempted. Please check spam folder or unblock redipbiswas8@gmail.com.');
        }
        redirect("item-details.php?type=$type&id=$id");
    }
}

// Fetch matches
if ($type === 'lost') {
    $matchStmt = $pdo->prepare(
        "SELECT m.*, f.item_name AS other_name, f.image AS other_image,
                f.location AS other_location, f.id AS other_id,
                u.full_name AS finder_name, u.email AS finder_email, u.phone AS finder_phone
         FROM matches m
         JOIN found_items f ON f.id = m.found_item_id
         JOIN users u ON u.id = f.user_id
         WHERE m.lost_item_id = ? ORDER BY m.match_score DESC"
    );
} else {
    $matchStmt = $pdo->prepare(
        "SELECT m.*, l.item_name AS other_name, l.image AS other_image,
                l.location AS other_location, l.id AS other_id,
                u.full_name AS finder_name, u.email AS finder_email, u.phone AS finder_phone
         FROM matches m
         JOIN lost_items l ON l.id = m.lost_item_id
         JOIN users u ON u.id = l.user_id
         WHERE m.found_item_id = ? ORDER BY m.match_score DESC"
    );
}
$matchStmt->execute([$id]);
$matches = $matchStmt->fetchAll();

$pageTitle = $item['item_name'];
require_once __DIR__ . '/includes/header.php';

$typeColor = $type === 'lost' ? '#ef4444' : '#22c55e';
$typeLabel = $type === 'lost' ? 'Lost' : 'Found';
$typeIcon  = $type === 'lost' ? 'fa-triangle-exclamation' : 'fa-hand-holding-heart';
?>

<div class="detail-page">
  <div class="container">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4" data-aos="fade-up">
      <ol class="breadcrumb" style="background:none;padding:0;margin:0;">
        <li class="breadcrumb-item">
          <a href="index.php" style="color:var(--text-muted);font-size:0.82rem;">Home</a>
        </li>
        <li class="breadcrumb-item">
          <a href="browse-<?= $type ?>.php" style="color:var(--text-muted);font-size:0.82rem;"><?= $typeLabel ?> Items</a>
        </li>
        <li class="breadcrumb-item active" style="color:var(--text-secondary);font-size:0.82rem;">
          <?= clean($item['item_name']) ?>
        </li>
      </ol>
    </nav>

    <div class="row g-4">
      <!-- IMAGE -->
      <div class="col-md-5" data-aos="fade-right">
        <div class="detail-img-wrap">
          <?php if ($item['image']): ?>
            <img src="<?= $uploadDir . clean($item['image']) ?>"
                 alt="<?= clean($item['item_name']) ?>">
          <?php else: ?>
            <div class="detail-img-placeholder">
              <i class="fa-regular fa-image"></i>
              <span>No photo uploaded</span>
            </div>
          <?php endif; ?>
        </div>

        <?php if ($item['image']): ?>
          <p style="color:var(--text-muted);font-size:0.75rem;text-align:center;margin-top:8px;">
            <i class="fa-solid fa-magnifying-glass-plus me-1"></i>Click image to zoom
          </p>
        <?php endif; ?>
      </div>

      <!-- INFO -->
      <div class="col-md-7" data-aos="fade-left">
        <div class="detail-info-card">
          <div class="detail-badges">
            <span class="badge-category"><?= clean($item['category']) ?></span>
            <span class="badge-status badge-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span>
            <span style="background:rgba(<?= $type==='lost'?'239,68,68':'34,197,94' ?>,0.15);color:<?= $typeColor ?>;
                   border:1px solid rgba(<?= $type==='lost'?'239,68,68':'34,197,94' ?>,0.25);
                   border-radius:20px;padding:3px 10px;font-size:0.72rem;font-weight:700;">
              <i class="fa-solid <?= $typeIcon ?> me-1"></i><?= $typeLabel ?>
            </span>
          </div>

          <h1 class="detail-title"><?= clean($item['item_name']) ?></h1>

          <div class="detail-meta-row">
            <i class="fa-solid fa-location-dot"></i>
            <span><?= clean($item['location']) ?></span>
          </div>
          <div class="detail-meta-row">
            <i class="fa-regular fa-calendar"></i>
            <span><?= $type === 'lost' ? 'Lost on' : 'Found on' ?> <?= date('d F Y', strtotime($item[$dateCol])) ?></span>
          </div>
          <div class="detail-meta-row">
            <i class="fa-solid fa-clock"></i>
            <span>Reported <?= date('d M Y, h:i A', strtotime($item['created_at'])) ?></span>
          </div>

          <div class="detail-divider"></div>

          <h5 style="font-size:0.9rem;font-weight:700;margin-bottom:10px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.5px;">Description</h5>
          <p class="detail-description"><?= nl2br(clean($item['description'])) ?></p>

          <?php if ($item['color'] || $item['brand']): ?>
            <div class="detail-specs">
              <?php if ($item['color']): ?>
                <div class="spec-item">
                  <div class="spec-label"><i class="fa-solid fa-palette me-1"></i>Color</div>
                  <div class="spec-value"><?= clean($item['color']) ?></div>
                </div>
              <?php endif; ?>
              <?php if ($item['brand']): ?>
                <div class="spec-item">
                  <div class="spec-label"><i class="fa-solid fa-tag me-1"></i>Brand / Model</div>
                  <div class="spec-value"><?= clean($item['brand']) ?></div>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <div class="detail-divider"></div>

          <!-- Contact Info -->
          <?php if (is_logged_in() && !$isOwner): ?>
            <div class="contact-card">
              <div class="contact-card-title"><i class="fa-solid fa-address-card me-2"></i>Reporter Contact</div>
              <div class="contact-row">
                <div class="contact-icon"><i class="fa-regular fa-user"></i></div>
                <span class="contact-value"><?= clean($item['reporter_name']) ?></span>
              </div>
              <div class="contact-row">
                <div class="contact-icon"><i class="fa-regular fa-envelope"></i></div>
                <span class="contact-value"><?= clean($item['reporter_email']) ?></span>
                <button class="copy-btn" data-copy="<?= clean($item['reporter_email']) ?>" title="Copy email">
                  <i class="fa-regular fa-copy"></i>
                </button>
              </div>
              <div class="contact-row">
                <div class="contact-icon"><i class="fa-solid fa-phone"></i></div>
                <span class="contact-value"><?= clean($item['reporter_phone']) ?></span>
                <button class="copy-btn" data-copy="<?= clean($item['reporter_phone']) ?>" title="Copy phone">
                  <i class="fa-regular fa-copy"></i>
                </button>
              </div>
            </div>
          <?php elseif (!is_logged_in()): ?>
            <div class="contact-card" style="text-align:center;">
              <i class="fa-solid fa-lock" style="font-size:1.5rem;color:var(--primary-light);margin-bottom:10px;display:block;"></i>
              <p style="color:var(--text-secondary);font-size:0.88rem;margin-bottom:12px;">
                Log in to view contact details and reach out to the reporter.
              </p>
              <a href="auth/login.php" class="btn-primary-custom" style="font-size:0.85rem;padding:8px 20px;">
                <i class="fa-solid fa-right-to-bracket"></i> Login to View
              </a>
            </div>
          <?php endif; ?>

          <!-- Owner actions -->
          <?php if ($isOwner && $item['status'] !== 'resolved'): ?>
            <div class="detail-divider"></div>
            <div class="d-flex gap-2 flex-wrap">
              <a href="?type=<?= $type ?>&id=<?= $id ?>&resolve=1"
                 class="btn-resolve"
                 data-confirm="Mark this item as resolved? This means it has been returned to its owner."
                 onclick="return confirm('Mark this item as resolved? This means it has been returned to its owner.')">
                <i class="fa-solid fa-circle-check"></i> Mark as Resolved
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- MATCHES SECTION -->
    <?php if (!empty($matches)): ?>
      <div class="matches-section" data-aos="fade-up">
        <div class="detail-divider" style="margin:40px 0 28px;"></div>
        <div class="d-flex align-items-center gap-3 mb-4">
          <div style="width:44px;height:44px;background:var(--gold-soft);border:1px solid rgba(240,165,0,0.25);
               border-radius:12px;display:flex;align-items:center;justify-content:center;">
            <i class="fa-solid fa-link" style="color:var(--gold);"></i>
          </div>
          <div>
            <h3 style="font-size:1.2rem;font-weight:800;margin-bottom:2px;">Possible Matches</h3>
            <p style="color:var(--text-muted);font-size:0.82rem;"><?= count($matches) ?> potential match<?= count($matches)!==1?'es':'' ?> found by our engine</p>
          </div>
        </div>

        <div class="row g-3">
          <?php foreach ($matches as $m): ?>
            <div class="col-md-6" data-aos="fade-up">
              <a href="item-details.php?type=<?= $type==='lost'?'found':'lost' ?>&id=<?= $m['other_id'] ?>"
                 class="match-card">
                <div class="match-card-img">
                  <?php if ($m['other_image']): ?>
                    <img src="uploads/<?= $type==='lost'?'found':'lost' ?>/<?= clean($m['other_image']) ?>" alt="">
                  <?php else: ?>
                    <i class="fa-regular fa-image"></i>
                  <?php endif; ?>
                </div>
                <div style="flex:1;min-width:0;">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <h6 style="font-size:0.92rem;font-weight:700;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                      <?= clean($m['other_name']) ?>
                    </h6>
                    <span class="match-score-badge ms-2">
                      <i class="fa-solid fa-percentage" style="font-size:0.65rem;"></i>
                      <?= (int)$m['match_score'] ?>% Match
                    </span>
                  </div>
                  <div class="item-meta mb-2"><i class="fa-solid fa-location-dot"></i> <?= clean($m['other_location']) ?></div>
                  <div class="match-progress">
                    <div class="match-progress-fill" data-score="<?= (int)$m['match_score'] ?>" style="width:0%;"></div>
                  </div>
                </div>
                <i class="fa-solid fa-chevron-right" style="color:var(--text-muted);font-size:0.8rem;flex-shrink:0;"></i>
              </a>
              <?php if (is_logged_in()): ?>
                <div class="mt-2 d-flex justify-content-end">
                  <a href="item-details.php?type=<?= $type ?>&id=<?= $id ?>&notify_match=<?= $m['id'] ?>"
                     class="btn btn-sm"
                     style="background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.25);border-radius:20px;font-size:0.75rem;padding:4px 14px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:all 0.2s ease;"
                     onmouseover="this.style.background='rgba(239,68,68,0.25)'"
                     onmouseout="this.style.background='rgba(239,68,68,0.12)'"
                     title="Send/resend match notification email to Gmail">
                    <i class="fa-solid fa-envelope"></i> Send Match Email to Gmail
                  </a>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
