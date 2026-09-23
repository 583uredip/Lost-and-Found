/* ================================================================
   AIUB Lost & Found — Enhanced JavaScript
   ================================================================ */

document.addEventListener('DOMContentLoaded', function () {

  // ── Navbar scroll behavior ───────────────────────────────────
  const navbar = document.querySelector('.navbar-custom');
  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('scrolled', window.scrollY > 50);
    });
  }

  // ── Generate hero particles ──────────────────────────────────
  const particleContainer = document.querySelector('.hero-particles');
  if (particleContainer) {
    for (let i = 0; i < 18; i++) {
      const p = document.createElement('div');
      p.className = 'particle';
      const size = Math.random() * 8 + 3;
      p.style.cssText = `
        width: ${size}px;
        height: ${size}px;
        left: ${Math.random() * 100}%;
        animation-duration: ${Math.random() * 15 + 10}s;
        animation-delay: ${Math.random() * 10}s;
        opacity: ${Math.random() * 0.15 + 0.05};
      `;
      particleContainer.appendChild(p);
    }
  }

  // ── Animated counters ────────────────────────────────────────
  function animateCounter(el, target, duration = 1800) {
    let start = 0;
    const step = target / (duration / 16);
    const timer = setInterval(() => {
      start += step;
      if (start >= target) {
        el.textContent = target.toLocaleString();
        clearInterval(timer);
      } else {
        el.textContent = Math.floor(start).toLocaleString();
      }
    }, 16);
  }

  const counters = document.querySelectorAll('[data-counter]');
  if (counters.length) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !entry.target.dataset.animated) {
          entry.target.dataset.animated = '1';
          animateCounter(entry.target, parseInt(entry.target.dataset.counter));
        }
      });
    }, { threshold: 0.3 });
    counters.forEach(c => observer.observe(c));
  }

  // ── Image upload (drag & drop) ────────────────────────────────
  document.querySelectorAll('.upload-zone').forEach(zone => {
    const input = zone.querySelector('input[type="file"]');
    const preview = zone.closest('.upload-wrapper')?.querySelector('.upload-preview');
    const previewImg = preview?.querySelector('img');
    const removeBtn = preview?.querySelector('.remove-img');

    if (!input) return;

    // Drag events
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
      e.preventDefault();
      zone.classList.remove('dragover');
      if (e.dataTransfer.files.length) {
        input.files = e.dataTransfer.files;
        showPreview(e.dataTransfer.files[0]);
      }
    });

    input.addEventListener('change', () => {
      if (input.files.length) showPreview(input.files[0]);
    });

    function showPreview(file) {
      if (!file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = e => {
        if (previewImg) {
          previewImg.src = e.target.result;
          preview.style.display = 'block';
        }
      };
      reader.readAsDataURL(file);
    }

    if (removeBtn) {
      removeBtn.addEventListener('click', e => {
        e.preventDefault();
        input.value = '';
        if (preview) preview.style.display = 'none';
        if (previewImg) previewImg.src = '';
      });
    }
  });

  // Old-style image preview support (data-preview attribute)
  document.querySelectorAll('input[data-preview]').forEach(input => {
    input.addEventListener('change', function () {
      const previewId = this.dataset.preview;
      const preview = document.getElementById(previewId);
      if (!preview) return;
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
          preview.src = e.target.result;
          preview.classList.remove('d-none');
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  });

  // ── Category radio (visual grid) ─────────────────────────────
  // Ensure hidden select (if used) stays synced
  document.querySelectorAll('.category-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function () {
      const hiddenSelect = document.getElementById('category_hidden');
      if (hiddenSelect) hiddenSelect.value = this.value;
    });
  });

  // ── Bootstrap form validation ────────────────────────────────
  document.querySelectorAll('.needs-validation').forEach(form => {
    form.addEventListener('submit', e => {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  });

  // ── Copy to clipboard ─────────────────────────────────────────
  document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const text = this.dataset.copy;
      if (!text) return;
      navigator.clipboard.writeText(text).then(() => {
        const orig = this.innerHTML;
        this.innerHTML = '<i class="fa-solid fa-check"></i>';
        this.style.color = '#4ade80';
        setTimeout(() => {
          this.innerHTML = orig;
          this.style.color = '';
        }, 2000);
      });
    });
  });

  // ── Match progress bars ───────────────────────────────────────
  document.querySelectorAll('.match-progress-fill').forEach(bar => {
    const score = parseInt(bar.dataset.score || 0);
    setTimeout(() => { bar.style.width = score + '%'; }, 200);
  });

  // ── Search debounce filter ────────────────────────────────────
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    let debounceTimer;
    searchInput.addEventListener('input', function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        const query = this.value.toLowerCase().trim();
        document.querySelectorAll('.item-card-wrapper').forEach(card => {
          const text = card.textContent.toLowerCase();
          card.style.display = text.includes(query) ? '' : 'none';
        });
      }, 300);
    });
  }

  // ── Scroll to top button ──────────────────────────────────────
  const scrollTopBtn = document.getElementById('scrollTopBtn');
  if (scrollTopBtn) {
    window.addEventListener('scroll', () => {
      scrollTopBtn.classList.toggle('visible', window.scrollY > 400);
    });
    scrollTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ── Notification auto-mark on hover ──────────────────────────
  document.querySelectorAll('.notif-item.unread').forEach(item => {
    item.addEventListener('mouseenter', function () {
      // Visual only — server marks read on next page load
      this.classList.remove('unread');
      const dot = this.querySelector('.notif-unread-dot');
      if (dot) dot.remove();
    });
  });

  // ── Dashboard tab persistence ─────────────────────────────────
  const activeTab = localStorage.getItem('dashTab');
  if (activeTab) {
    const tab = document.querySelector(`[data-bs-target="${activeTab}"]`);
    if (tab) {
      const bsTab = new bootstrap.Tab(tab);
      bsTab.show();
    }
  }
  document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tab => {
    tab.addEventListener('shown.bs.tab', e => {
      localStorage.setItem('dashTab', e.target.dataset.bsTarget);
    });
  });

  // ── Sidebar nav active state ──────────────────────────────────
  document.querySelectorAll('.sidebar-link[data-tab]').forEach(link => {
    link.addEventListener('click', function () {
      document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
      this.classList.add('active');
      const target = this.dataset.tab;
      const tab = document.querySelector(`[data-bs-target="#${target}"]`);
      if (tab) new bootstrap.Tab(tab).show();
    });
  });

  // ── Toast system ──────────────────────────────────────────────
  window.showToast = function (message, type = 'success') {
    let container = document.querySelector('.toast-container-custom');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container-custom';
      document.body.appendChild(container);
    }

    const icon = type === 'success'
      ? '<i class="fa-solid fa-circle-check"></i>'
      : '<i class="fa-solid fa-circle-xmark"></i>';

    const toast = document.createElement('div');
    toast.className = `toast-custom ${type}`;
    toast.innerHTML = `
      <span class="toast-custom-icon">${icon}</span>
      <span class="toast-custom-text">${message}</span>
      <button class="toast-close" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
    `;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.transition = 'opacity 0.3s ease';
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  };

  // ── AOS init (if loaded) ──────────────────────────────────────
  if (typeof AOS !== 'undefined') {
    AOS.init({ once: true, duration: 600, easing: 'ease-out-cubic', offset: 60 });
  }

  // ── Lightbox for detail page image ────────────────────────────
  const detailImg = document.querySelector('.detail-img-wrap img');
  if (detailImg) {
    detailImg.style.cursor = 'zoom-in';
    detailImg.addEventListener('click', function () {
      const overlay = document.createElement('div');
      overlay.style.cssText = `
        position:fixed;inset:0;background:rgba(0,0,0,0.92);z-index:10000;
        display:flex;align-items:center;justify-content:center;cursor:zoom-out;
        animation: fadeIn 0.2s ease;
      `;
      const img = document.createElement('img');
      img.src = this.src;
      img.style.cssText = 'max-width:90vw;max-height:90vh;object-fit:contain;border-radius:12px;';
      overlay.appendChild(img);
      overlay.addEventListener('click', () => overlay.remove());
      document.body.appendChild(overlay);
    });
  }

  // ── Resolve confirm ───────────────────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function (e) {
      if (!confirm(this.dataset.confirm)) e.preventDefault();
    });
  });

});
