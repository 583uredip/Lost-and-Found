<?php
require_once __DIR__ . '/includes/functions.php';

$search   = clean($_GET['q'] ?? '');
$category = clean($_GET['cat'] ?? '');
$status   = clean($_GET['status'] ?? '');

$params = [];
$where  = ['1=1'];

if ($search) {
    $where[]  = "(item_name LIKE ? OR description LIKE ? OR location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category) {
    $where[]  = "category = ?";
    $params[] = $category;
}
if ($status) {
    $where[]  = "status = ?";
    $params[] = $status;
}

$whereClause = implode(' AND ', $where);
$stmt = $pdo->prepare("SELECT * FROM found_items WHERE $whereClause ORDER BY created_at DESC");
$stmt->execute($params);
$items = $stmt->fetchAll();

$pageTitle = 'Browse Found Items';
require_once __DIR__ . '/includes/header.php';
?>

<div style="padding:40px 0 80px;">
  <div class="container">

    <!-- Header -->
    <div class="browse-header" data-aos="fade-up">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <h1 class="browse-title">
            <i class="fa-solid fa-hand-holding-heart me-2" style="color:#22c55e;font-size:1.5rem;"></i>
            Found Items
          </h1>
          <p style="color:var(--text-muted);font-size:0.88rem;margin-top:4px;">
            <?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?> reported
            <?= $search ? " matching \"$search\"" : '' ?>
          </p>
        </div>
        <?php if (is_logged_in()): ?>
          <a href="report-found.php" class="btn-primary-custom" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
            <i class="fa-solid fa-plus"></i> Report Found Item
          </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Search + Filters -->
    <div data-aos="fade-up" data-aos-delay="50">
      <form method="GET" id="searchForm">
        <div class="search-bar-wrap mb-3">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
          <input type="text" id="searchInput" name="q" class="search-input"
                 placeholder="Search by name, description or location..."
                 value="<?= clean($_GET['q'] ?? '') ?>">
        </div>

        <div class="filter-bar">
          <a href="browse-found.php" class="filter-chip <?= !$category && !$status ? 'active' : '' ?>">
            <i class="fa-solid fa-border-all"></i> All Categories
          </a>
          <?php foreach (item_categories() as $cat): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['cat' => $cat])) ?>"
               class="filter-chip <?= $category === $cat ? 'active' : '' ?>">
              <?= clean($cat) ?>
            </a>
          <?php endforeach; ?>
        </div>

        <div class="filter-bar" style="margin-top:0;">
          <a href="browse-found.php<?= $search ? '?q='.urlencode($search) : '' ?>" class="filter-chip <?= !$status ? 'active' : '' ?>">All Status</a>
          <a href="?<?= http_build_query(array_merge($_GET, ['status' => 'open'])) ?>" class="filter-chip <?= $status==='open' ? 'active' : '' ?>">
            <i class="fa-solid fa-circle" style="color:#60a5fa;font-size:0.6rem;"></i> Open
          </a>
          <a href="?<?= http_build_query(array_merge($_GET, ['status' => 'matched'])) ?>" class="filter-chip <?= $status==='matched' ? 'active' : '' ?>">
            <i class="fa-solid fa-circle" style="color:var(--gold);font-size:0.6rem;"></i> Matched
          </a>
          <a href="?<?= http_build_query(array_merge($_GET, ['status' => 'resolved'])) ?>" class="filter-chip <?= $status==='resolved' ? 'active' : '' ?>">
            <i class="fa-solid fa-circle" style="color:#4ade80;font-size:0.6rem;"></i> Resolved
          </a>
        </div>
      </form>
    </div>

    <!-- Items Grid -->
    <div class="items-grid">
      <?php if (empty($items)): ?>
        <div class="empty-state" data-aos="fade-up">
          <div class="empty-icon"><i class="fa-solid fa-hand-holding-heart"></i></div>
          <h5>No found items yet</h5>
          <p>Found something on campus? Help someone recover it!</p>
          <?php if (is_logged_in()): ?>
            <a href="report-found.php" class="btn-primary-custom mt-3" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
              <i class="fa-solid fa-plus"></i> Report Found Item
            </a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <?php foreach ($items as $item): ?>
          <div class="item-card-wrapper" data-aos="fade-up">
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
                  <?php if ($item['color']): ?>
                    <div class="item-meta"><i class="fa-solid fa-palette"></i> <?= clean($item['color']) ?></div>
                  <?php endif; ?>
                  <div class="item-card-footer">
                    <span class="badge-status badge-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span>
                    <span style="font-size:0.72rem;color:var(--text-muted);"><?= date('d M', strtotime($item['created_at'])) ?></span>
                  </div>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
