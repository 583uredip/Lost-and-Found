<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$uid = current_user_id();

$myLost = $pdo->prepare("SELECT * FROM lost_items WHERE user_id = ? ORDER BY created_at DESC");
$myLost->execute([$uid]);
$myLost = $myLost->fetchAll();

$myFound = $pdo->prepare("SELECT * FROM found_items WHERE user_id = ? ORDER BY created_at DESC");
$myFound->execute([$uid]);
$myFound = $myFound->fetchAll();

// Count notifications
$notifStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$notifStmt->execute([$uid]);
$unreadNotifs = (int)$notifStmt->fetchColumn();

// Count matches (both lost and found)
$matchLostStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM matches m
     LEFT JOIN lost_items l ON l.id = m.lost_item_id
     LEFT JOIN found_items f ON f.id = m.found_item_id
     WHERE l.user_id = ? OR f.user_id = ?"
);
$matchLostStmt->execute([$uid, $uid]);
$totalMatches = (int)$matchLostStmt->fetchColumn();

$userName = clean($_SESSION['user_name']);
$userInitial = strtoupper(substr($userName, 0, 1));

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
  <div class="dashboard-layout">

    <!-- SIDEBAR -->
    <aside class="dashboard-sidebar" data-aos="fade-right">
      <div class="user-avatar-wrap">
        <div class="user-avatar"><?= $userInitial ?></div>
        <p class="user-name"><?= $userName ?></p>
        <p class="user-role">AIUB Community Member</p>
      </div>

      <nav class="sidebar-nav">
        <button class="sidebar-link active" data-tab="lostTab">
          <i class="fa-solid fa-triangle-exclamation"></i> My Lost Reports
          <?php if (count($myLost)): ?>
            <span class="ms-auto" style="font-size:0.72rem;background:rgba(239,68,68,0.2);color:#f87171;padding:1px 7px;border-radius:10px;"><?= count($myLost) ?></span>
          <?php endif; ?>
        </button>
        <button class="sidebar-link" data-tab="foundTab">
          <i class="fa-solid fa-hand-holding-heart"></i> My Found Reports
          <?php if (count($myFound)): ?>
            <span class="ms-auto" style="font-size:0.72rem;background:rgba(34,197,94,0.2);color:#4ade80;padding:1px 7px;border-radius:10px;"><?= count($myFound) ?></span>
          <?php endif; ?>
        </button>
        <div class="divider"></div>
        <a href="report-lost.php" class="sidebar-link">
          <i class="fa-solid fa-plus-circle"></i> Report Lost Item
        </a>
        <a href="report-found.php" class="sidebar-link">
          <i class="fa-solid fa-plus-circle"></i> Report Found Item
        </a>
        <a href="notifications.php" class="sidebar-link">
          <i class="fa-regular fa-bell"></i> Notifications
          <?php if ($unreadNotifs): ?>
            <span class="ms-auto" style="font-size:0.72rem;background:rgba(232,35,74,0.25);color:var(--accent);padding:1px 7px;border-radius:10px;"><?= $unreadNotifs ?></span>
          <?php endif; ?>
        </a>
        <a href="browse-lost.php" class="sidebar-link">
          <i class="fa-solid fa-magnifying-glass"></i> Browse All Lost
        </a>
        <a href="browse-found.php" class="sidebar-link">
          <i class="fa-solid fa-list"></i> Browse All Found
        </a>
      </nav>
    </aside>

    <!-- MAIN -->
    <main class="dash-main" data-aos="fade-left">

      <!-- Welcome -->
      <div class="mb-4">
        <h2 style="font-size:1.5rem;font-weight:800;letter-spacing:-0.3px;">
          Hey, <?= explode(' ', $userName)[0] ?> 👋
        </h2>
        <p style="color:var(--text-muted);font-size:0.88rem;">Manage your lost and found reports from here.</p>
      </div>

      <!-- Mini stats -->
      <div class="dash-stats mb-4">
        <div class="mini-stat">
          <div class="mini-stat-icon" style="background:rgba(239,68,68,0.15);color:#f87171;">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </div>
          <div>
            <div class="mini-stat-num"><?= count($myLost) ?></div>
            <div class="mini-stat-label">Lost Reports</div>
          </div>
        </div>
        <div class="mini-stat">
          <div class="mini-stat-icon" style="background:rgba(34,197,94,0.15);color:#4ade80;">
            <i class="fa-solid fa-hand-holding-heart"></i>
          </div>
          <div>
            <div class="mini-stat-num"><?= count($myFound) ?></div>
            <div class="mini-stat-label">Found Reports</div>
          </div>
        </div>
        <div class="mini-stat">
          <div class="mini-stat-icon" style="background:rgba(240,165,0,0.15);color:var(--gold);">
            <i class="fa-solid fa-link"></i>
          </div>
          <div>
            <div class="mini-stat-num"><?= $totalMatches ?></div>
            <div class="mini-stat-label">Matches Found</div>
          </div>
        </div>
      </div>

      <!-- Tab content -->
      <div class="dash-tab-content">
        <ul class="nav nav-pills mb-4 gap-2" role="tablist">
          <li class="nav-item">
            <button class="nav-link active" id="tab-lost" data-bs-toggle="pill" data-bs-target="#lostTab">
              <i class="fa-solid fa-triangle-exclamation me-2"></i>My Lost Reports (<?= count($myLost) ?>)
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" id="tab-found" data-bs-toggle="pill" data-bs-target="#foundTab">
              <i class="fa-solid fa-hand-holding-heart me-2"></i>My Found Reports (<?= count($myFound) ?>)
            </button>
          </li>
        </ul>

        <div class="tab-content">
          <!-- Lost Tab -->
          <div class="tab-pane fade show active" id="lostTab">
            <?php if (empty($myLost)): ?>
              <div class="empty-state" style="padding:50px 20px;">
                <div class="empty-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <h5>No lost reports yet</h5>
                <p>Lost something on campus? Report it and we'll help find it.</p>
                <a href="report-lost.php" class="btn-primary-custom mt-3" style="background:linear-gradient(135deg,#ef4444,var(--primary));">
                  <i class="fa-solid fa-plus"></i> Report Lost Item
                </a>
              </div>
            <?php else: ?>
              <div class="row g-3">
                <?php foreach ($myLost as $item): ?>
                  <div class="col-md-4 col-sm-6">
                    <a href="item-details.php?type=lost&id=<?= $item['id'] ?>" class="text-decoration-none">
                      <div class="item-card">
                        <div class="item-card-img">
                          <?php if ($item['image']): ?>
                            <img src="uploads/lost/<?= clean($item['image']) ?>" alt="" loading="lazy">
                          <?php else: ?>
                            <div class="no-image"><i class="fa-regular fa-image"></i></div>
                          <?php endif; ?>
                          <span class="item-type-badge item-type-lost"><i class="fa-solid fa-triangle-exclamation me-1"></i>Lost</span>
                        </div>
                        <div class="item-card-body">
                          <span class="badge-status badge-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span>
                          <h6 class="item-card-title mt-2"><?= clean($item['item_name']) ?></h6>
                          <div class="item-meta"><i class="fa-solid fa-location-dot"></i> <?= clean($item['location']) ?></div>
                          <div class="item-meta"><i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($item['date_lost'])) ?></div>
                        </div>
                      </div>
                    </a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Found Tab -->
          <div class="tab-pane fade" id="foundTab">
            <?php if (empty($myFound)): ?>
              <div class="empty-state" style="padding:50px 20px;">
                <div class="empty-icon"><i class="fa-solid fa-hand-holding-heart"></i></div>
                <h5>No found reports yet</h5>
                <p>Found something on campus? Help someone recover it!</p>
                <a href="report-found.php" class="btn-primary-custom mt-3" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                  <i class="fa-solid fa-plus"></i> Report Found Item
                </a>
              </div>
            <?php else: ?>
              <div class="row g-3">
                <?php foreach ($myFound as $item): ?>
                  <div class="col-md-4 col-sm-6">
                    <a href="item-details.php?type=found&id=<?= $item['id'] ?>" class="text-decoration-none">
                      <div class="item-card">
                        <div class="item-card-img">
                          <?php if ($item['image']): ?>
                            <img src="uploads/found/<?= clean($item['image']) ?>" alt="" loading="lazy">
                          <?php else: ?>
                            <div class="no-image"><i class="fa-regular fa-image"></i></div>
                          <?php endif; ?>
                          <span class="item-type-badge item-type-found"><i class="fa-solid fa-hand-holding me-1"></i>Found</span>
                        </div>
                        <div class="item-card-body">
                          <span class="badge-status badge-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span>
                          <h6 class="item-card-title mt-2"><?= clean($item['item_name']) ?></h6>
                          <div class="item-meta"><i class="fa-solid fa-location-dot"></i> <?= clean($item['location']) ?></div>
                          <div class="item-meta"><i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($item['date_found'])) ?></div>
                        </div>
                      </div>
                    </a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
