<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/matching.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemName    = clean($_POST['item_name'] ?? '');
    $category    = clean($_POST['category'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $color       = clean($_POST['color'] ?? '');
    $brand       = clean($_POST['brand'] ?? '');
    $location    = clean($_POST['location'] ?? '');
    $dateFound   = clean($_POST['date_found'] ?? '');

    if (!$itemName || !$category || !$description || !$location || !$dateFound) {
        flash('error', 'Please fill in all required fields.');
    } else {
        $image = handle_image_upload('image', UPLOAD_DIR_FOUND);
        if ($image === false) {
            flash('error', 'Image upload failed. Please use JPG/PNG/WEBP under 5 MB.');
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO found_items (user_id, item_name, category, description, color, brand, location, date_found, image)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                current_user_id(), $itemName, $category, $description,
                $color, $brand, $location, $dateFound, $image
            ]);

            $newId = $pdo->lastInsertId();
            match_new_found_item($pdo, $newId);

            flash('success', 'Found item reported! If we find a match, the owner will be notified instantly.');
            redirect('dashboard.php');
        }
    }
}

$pageTitle = 'Report Found Item';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-wrapper">
  <div class="page-bg-glow"></div>
  <div class="container">
    <div class="form-card">

      <div class="form-card-header">
        <div class="form-card-icon" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
          <i class="fa-solid fa-hand-holding-heart"></i>
        </div>
        <h3>Report a Found Item</h3>
        <p class="form-subtitle">Great job finding it! Fill in the details so we can reunite it with the owner.</p>
      </div>

      <form method="POST" enctype="multipart/form-data" id="reportFoundForm" class="needs-validation" novalidate>

        <!-- Item Name -->
        <div class="mb-4">
          <label class="form-label" for="item_name">Item Name *</label>
          <input type="text" id="item_name" name="item_name" class="form-control"
                 placeholder="e.g. Blue Wallet with AIUB ID inside" required
                 value="<?= clean($_POST['item_name'] ?? '') ?>">
        </div>

        <!-- Category -->
        <div class="mb-4">
          <label class="form-label">Category *</label>
          <div class="category-grid">
            <?php
            $catIcons = [
              'Electronics'        => 'fa-solid fa-microchip',
              'Documents / ID Card'=> 'fa-solid fa-id-card',
              'Wallet / Purse'     => 'fa-solid fa-wallet',
              'Keys'               => 'fa-solid fa-key',
              'Bag / Backpack'     => 'fa-solid fa-bag-shopping',
              'Jewelry / Watch'    => 'fa-solid fa-gem',
              'Clothing'           => 'fa-solid fa-shirt',
              'Books / Notes'      => 'fa-solid fa-book',
              'Mobile Phone'       => 'fa-solid fa-mobile-screen',
              'Laptop'             => 'fa-solid fa-laptop',
              'Other'              => 'fa-solid fa-box',
            ];
            $selectedCat = clean($_POST['category'] ?? '');
            foreach (item_categories() as $cat):
            ?>
              <div class="category-option">
                <input type="radio" name="category" id="cat_<?= str_replace(' ', '_', $cat) ?>"
                       value="<?= clean($cat) ?>" <?= $selectedCat === clean($cat) ? 'checked' : '' ?> required>
                <label for="cat_<?= str_replace(' ', '_', $cat) ?>">
                  <i class="<?= $catIcons[$cat] ?? 'fa-solid fa-box' ?>"></i>
                  <?= clean($cat) ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Date + Location -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label" for="date_found">Date Found *</label>
            <input type="date" id="date_found" name="date_found" class="form-control" required
                   max="<?= date('Y-m-d') ?>"
                   value="<?= clean($_POST['date_found'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="location">Where Found *</label>
            <input type="text" id="location" name="location" class="form-control" required
                   placeholder="e.g. AIUB Cafeteria, 2nd Floor"
                   value="<?= clean($_POST['location'] ?? '') ?>">
          </div>
        </div>

        <!-- Color + Brand -->
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label" for="color">Color</label>
            <input type="text" id="color" name="color" class="form-control"
                   placeholder="e.g. Black, Silver, Red"
                   value="<?= clean($_POST['color'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="brand">Brand / Model</label>
            <input type="text" id="brand" name="brand" class="form-control"
                   placeholder="e.g. Samsung, Apple, HP"
                   value="<?= clean($_POST['brand'] ?? '') ?>">
          </div>
        </div>

        <!-- Description -->
        <div class="mb-4">
          <label class="form-label" for="description">Description *</label>
          <textarea id="description" name="description" class="form-control" rows="4" required
                    placeholder="Describe what you found — condition, any visible marks, contents, etc."><?= clean($_POST['description'] ?? '') ?></textarea>
        </div>

        <!-- Image Upload -->
        <div class="mb-4">
          <label class="form-label">Photo of Found Item <span style="color:var(--text-muted);font-weight:400;">(recommended)</span></label>
          <div class="upload-wrapper">
            <div class="upload-zone">
              <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
              <div class="upload-icon"><i class="fa-solid fa-camera"></i></div>
              <p class="upload-text"><strong>Click to upload</strong> or drag &amp; drop</p>
              <p class="upload-hint">JPG, PNG, WEBP &mdash; max 5 MB &bull; Photo helps owners identify their item</p>
            </div>
            <div class="upload-preview">
              <img src="" alt="Preview">
              <button class="remove-img" type="button"><i class="fa-solid fa-xmark"></i></button>
            </div>
          </div>
        </div>

        <button type="submit" class="btn-primary-custom btn-submit" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
          <i class="fa-solid fa-paper-plane"></i> Submit Found Report
        </button>

        <p class="text-center mt-3" style="color:var(--text-muted);font-size:0.8rem;">
          <i class="fa-solid fa-heart me-1" style="color:#f43f5e;"></i>
          Thank you for helping the AIUB community!
        </p>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
