<!-- ── Footer ─────────────────────────────────────────────────── -->
<footer class="footer">
  <div class="footer-inner">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4 col-md-6">
          <div class="footer-brand">
            <div class="d-flex align-items-center gap-2 mb-3">
              <div class="brand-icon" style="width:36px;height:36px;">
                <i class="fa-solid fa-magnifying-glass" style="font-size:0.85rem;"></i>
              </div>
              <h4 class="mb-0" style="font-size:1.1rem;">AIUB Lost &amp; Found</h4>
            </div>
            <p>Helping AIUB students and staff reunite with their lost belongings through smart AI-powered matching.</p>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 col-6">
          <p class="footer-heading">Navigation</p>
          <ul class="footer-links">
            <li><a href="<?= SITE_URL ?>/index.php"><i class="fa-solid fa-chevron-right fa-xs"></i> Home</a></li>
            <li><a href="<?= SITE_URL ?>/browse-lost.php"><i class="fa-solid fa-chevron-right fa-xs"></i> Lost Items</a></li>
            <li><a href="<?= SITE_URL ?>/browse-found.php"><i class="fa-solid fa-chevron-right fa-xs"></i> Found Items</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 col-6">
          <p class="footer-heading">Account</p>
          <ul class="footer-links">
            <?php if (is_logged_in()): ?>
              <li><a href="<?= SITE_URL ?>/dashboard.php"><i class="fa-solid fa-chevron-right fa-xs"></i> Dashboard</a></li>
              <li><a href="<?= SITE_URL ?>/report-lost.php"><i class="fa-solid fa-chevron-right fa-xs"></i> Report Lost</a></li>
              <li><a href="<?= SITE_URL ?>/report-found.php"><i class="fa-solid fa-chevron-right fa-xs"></i> Report Found</a></li>
              <li><a href="<?= SITE_URL ?>/notifications.php"><i class="fa-solid fa-chevron-right fa-xs"></i> Notifications</a></li>
            <?php else: ?>
              <li><a href="<?= SITE_URL ?>/auth/login.php"><i class="fa-solid fa-chevron-right fa-xs"></i> Login</a></li>
              <li><a href="<?= SITE_URL ?>/auth/register.php"><i class="fa-solid fa-chevron-right fa-xs"></i> Sign Up</a></li>
            <?php endif; ?>
          </ul>
        </div>

        <div class="col-lg-4 col-md-6">
          <p class="footer-heading">About AIUB</p>
          <p style="color:var(--text-muted);font-size:0.82rem;line-height:1.7;">
            American International University-Bangladesh (AIUB) is a leading private university in Dhaka, Bangladesh.
            This platform is maintained by students for the campus community.
          </p>
          <div class="d-flex gap-2 mt-3">
            <a href="https://aiub.edu" target="_blank" class="btn-ghost-custom" style="font-size:0.78rem;padding:6px 12px;">
              <i class="fa-solid fa-globe"></i> aiub.edu
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="footer-bottom">
      <p class="footer-copy">
        &copy; <?= date('Y') ?> <a href="<?= SITE_URL ?>">AIUB Lost &amp; Found</a>. Built for the AIUB campus community.
      </p>
      <p class="footer-copy" style="font-size:0.75rem;">
        <i class="fa-solid fa-shield-halved me-1" style="color:var(--primary-light);"></i>
        Secure &bull; Private &bull; Student-run
      </p>
    </div>
  </div>
</footer>

<!-- Scroll to top -->
<button class="scroll-top-btn" id="scrollTopBtn" aria-label="Scroll to top">
  <i class="fa-solid fa-chevron-up"></i>
</button>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS -->
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<!-- Custom JS -->
<script src="<?= SITE_URL ?>/assets/js/script.js"></script>
</body>
</html>
