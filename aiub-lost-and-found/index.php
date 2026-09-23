<?php
require_once __DIR__ . '/includes/functions.php';

$totalLost     = $pdo->query("SELECT COUNT(*) FROM lost_items")->fetchColumn();
$totalFound    = $pdo->query("SELECT COUNT(*) FROM found_items")->fetchColumn();
$totalResolved = $pdo->query("SELECT COUNT(*) FROM lost_items WHERE status='resolved'")->fetchColumn();

$recentLost  = $pdo->query("SELECT * FROM lost_items ORDER BY created_at DESC LIMIT 8")->fetchAll();
$recentFound = $pdo->query("SELECT * FROM found_items ORDER BY created_at DESC LIMIT 8")->fetchAll();

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── HERO ──────────────────────────────────────────────────── -->
<section class="hero">
  <div class="hero-bg-grid"></div>
  <div class="hero-particles"></div>

  <div class="hero-content container">
    <div class="hero-eyebrow" data-aos="fade-down">
      <span class="hero-eyebrow-dot"></span>
      AIUB Campus &bull; Smart Matching System
    </div>

    <h1 data-aos="fade-up" data-aos-delay="100">
      Lost Something<br>
      <span class="grad">on Campus?</span>
    </h1>

    <p class="hero-desc" data-aos="fade-up" data-aos-delay="200">
      Post your lost or found item &mdash; our AI matching engine compares descriptions,
      categories &amp; locations and sends you an instant email when a match is found.
    </p>

    <div class="hero-actions" data-aos="fade-up" data-aos-delay="300">
      <?php if (is_logged_in()): ?>
        <a href="report-lost.php" class="btn-primary-custom">
          <i class="fa-solid fa-triangle-exclamation"></i> Report Lost Item
        </a>
        <a href="report-found.php" class="btn-outline-custom">
          <i class="fa-solid fa-hand-holding"></i> Report Found Item
        </a>
      <?php else: ?>
        <a href="auth/register.php" class="btn-primary-custom">
          <i class="fa-solid fa-rocket"></i> Get Started Free
        </a>
        <a href="browse-lost.php" class="btn-outline-custom">
          <i class="fa-solid fa-magnifying-glass"></i> Browse Items
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="hero-scroll-hint">
    <div class="scroll-mouse"><div class="scroll-dot"></div></div>
    <span>Scroll down</span>
  </div>
</section>

<!-- ── STATS ─────────────────────────────────────────────────── -->
<section class="stats-section">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
        <div class="stat-card">
          <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
          <div class="stat-num" data-counter="<?= (int)$totalLost ?>">0</div>
          <div class="stat-label">Lost Items Reported</div>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="stat-card">
          <div class="stat-icon"><i class="fa-solid fa-hand-holding-heart"></i></div>
          <div class="stat-num" data-counter="<?= (int)$totalFound ?>">0</div>
          <div class="stat-label">Found Items Reported</div>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="stat-card">
          <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
          <div class="stat-num" data-counter="<?= (int)$totalResolved ?>">0</div>
          <div class="stat-label">Items Successfully Reunited</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── HOW IT WORKS ───────────────────────────────────────────── -->
<section class="how-section">
  <div class="container">
    <div class="section-header mb-4" data-aos="fade-up">
      <h2 class="section-title">How It <span>Works</span></h2>
    </div>
    <div class="row g-4">
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
        <div class="how-card">
          <div class="how-step-num">01</div>
          <div class="how-icon"><i class="fa-solid fa-pen-to-square"></i></div>
          <h5 class="how-title">Report the Item</h5>
          <p class="how-desc">Post details of the item you lost or found — with a photo, category, color, brand, and the location on campus.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="how-card">
          <div class="how-step-num">02</div>
          <div class="how-icon"><i class="fa-solid fa-brain"></i></div>
          <h5 class="how-title">Smart Matching</h5>
          <p class="how-desc">Our engine scores similarity across category, description keywords, color, brand, location &amp; date — 0 to 100%.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="how-card">
          <div class="how-step-num">03</div>
          <div class="how-icon"><i class="fa-solid fa-bell"></i></div>
          <h5 class="how-title">Instant Notification</h5>
          <p class="how-desc">The moment a strong match is found you receive an email &amp; in-app notification with the finder's contact info.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── RECENTLY LOST ──────────────────────────────────────────── -->
<section class="py-4 mb-2">
  <div class="container">
    <div class="section-header" data-aos="fade-up">
      <h2 class="section-title"><span>Recently</span> Lost</h2>
      <a href="browse-lost.php" class="section-link">View all <i class="fa-solid fa-arrow-right fa-xs"></i></a>
    </div>

    <?php if (empty($recentLost)): ?>
      <div class="empty-state" data-aos="fade-up">
        <div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>
        <h5>No lost items yet</h5>
        <p>Be the first to report a lost item.</p>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($recentLost as $item): ?>
          <div class="col-lg-3 col-md-4 col-sm-6 item-card-wrapper" data-aos="fade-up">
            <a href="item-details.php?type=lost&id=<?= $item['id'] ?>" class="text-decoration-none">
              <div class="item-card">
                <div class="item-card-img">
                  <?php if ($item['image']): ?>
                    <img src="uploads/lost/<?= clean($item['image']) ?>" alt="<?= clean($item['item_name']) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="no-image">
                      <i class="fa-regular fa-image"></i>
                      <span>No image</span>
                    </div>
                  <?php endif; ?>
                  <span class="item-type-badge item-type-lost"><i class="fa-solid fa-triangle-exclamation me-1"></i>Lost</span>
                </div>
                <div class="item-card-body">
                  <span class="badge-category"><?= clean($item['category']) ?></span>
                  <h6 class="item-card-title"><?= clean($item['item_name']) ?></h6>
                  <div class="item-meta"><i class="fa-solid fa-location-dot"></i> <?= clean($item['location']) ?></div>
                  <div class="item-meta"><i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($item['date_lost'])) ?></div>
                  <div class="item-card-footer">
                    <span class="badge-status badge-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span>
                    <span style="font-size:0.75rem;color:var(--text-muted);"><?= date('d M', strtotime($item['created_at'])) ?></span>
                  </div>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ── RECENTLY FOUND ─────────────────────────────────────────── -->
<section class="py-4 mb-5">
  <div class="container">
    <div class="section-header" data-aos="fade-up">
      <h2 class="section-title"><span>Recently</span> Found</h2>
      <a href="browse-found.php" class="section-link">View all <i class="fa-solid fa-arrow-right fa-xs"></i></a>
    </div>

    <?php if (empty($recentFound)): ?>
      <div class="empty-state" data-aos="fade-up">
        <div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>
        <h5>No found items yet</h5>
        <p>Report a found item to help someone recover it.</p>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($recentFound as $item): ?>
          <div class="col-lg-3 col-md-4 col-sm-6 item-card-wrapper" data-aos="fade-up">
            <a href="item-details.php?type=found&id=<?= $item['id'] ?>" class="text-decoration-none">
              <div class="item-card">
                <div class="item-card-img">
                  <?php if ($item['image']): ?>
                    <img src="uploads/found/<?= clean($item['image']) ?>" alt="<?= clean($item['item_name']) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="no-image">
                      <i class="fa-regular fa-image"></i>
                      <span>No image</span>
                    </div>
                  <?php endif; ?>
                  <span class="item-type-badge item-type-found"><i class="fa-solid fa-hand-holding me-1"></i>Found</span>
                </div>
                <div class="item-card-body">
                  <span class="badge-category"><?= clean($item['category']) ?></span>
                  <h6 class="item-card-title"><?= clean($item['item_name']) ?></h6>
                  <div class="item-meta"><i class="fa-solid fa-location-dot"></i> <?= clean($item['location']) ?></div>
                  <div class="item-meta"><i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($item['date_found'])) ?></div>
                  <div class="item-card-footer">
                    <span class="badge-status badge-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span>
                    <span style="font-size:0.75rem;color:var(--text-muted);"><?= date('d M', strtotime($item['created_at'])) ?></span>
                  </div>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ── CTA BANNER ────────────────────────────────────────────── -->
<?php if (!is_logged_in()): ?>
<section class="py-5 mb-4">
  <div class="container">
    <div data-aos="fade-up" style="background: linear-gradient(135deg, rgba(122,12,46,0.25), rgba(232,35,74,0.1));
         border: 1px solid rgba(122,12,46,0.3); border-radius: 24px; padding: 50px 40px; text-align:center;">
      <div style="width:56px;height:56px;background:linear-gradient(135deg,var(--primary),var(--accent));
           border-radius:16px;display:flex;align-items:center;justify-content:center;
           margin:0 auto 20px;font-size:1.3rem;color:#fff;box-shadow:0 8px 30px var(--primary-glow);">
        <i class="fa-solid fa-shield-halved"></i>
      </div>
      <h3 style="font-size:1.8rem;font-weight:800;margin-bottom:12px;letter-spacing:-0.5px;">
        Join the AIUB Lost &amp; Found Community
      </h3>
      <p style="color:var(--text-secondary);max-width:500px;margin:0 auto 28px;font-size:0.95rem;line-height:1.7;">
        Create a free account to report items, receive match notifications, and help fellow AIUB students recover their belongings.
      </p>
      <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="auth/register.php" class="btn-primary-custom">
          <i class="fa-solid fa-user-plus"></i> Create Free Account
        </a>
        <a href="auth/login.php" class="btn-outline-custom">Already have an account? Login</a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
