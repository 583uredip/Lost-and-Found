<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
admin_require_login();

// Handle actions
$action = $_GET['action'] ?? '';
$type   = $_GET['type']   ?? '';
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id > 0) {
    if ($type === 'user') {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    } elseif ($type === 'lost') {
        $pdo->prepare("DELETE FROM lost_items WHERE id = ?")->execute([$id]);
    } elseif ($type === 'found') {
        $pdo->prepare("DELETE FROM found_items WHERE id = ?")->execute([$id]);
    } elseif ($type === 'match') {
        $pdo->prepare("DELETE FROM matches WHERE id = ?")->execute([$id]);
    }
    header('Location: dashboard.php?deleted=1'); exit;
}

if ($action === 'logout') {
    session_destroy(); header('Location: login.php'); exit;
}

// Stats
$totalUsers  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalLost   = $pdo->query("SELECT COUNT(*) FROM lost_items")->fetchColumn();
$totalFound  = $pdo->query("SELECT COUNT(*) FROM found_items")->fetchColumn();
$totalMatch  = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();

// Data
$users      = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$lostItems  = $pdo->query("SELECT l.*, u.full_name, u.email FROM lost_items l JOIN users u ON l.user_id=u.id ORDER BY l.created_at DESC")->fetchAll();
$foundItems = $pdo->query("SELECT f.*, u.full_name, u.email FROM found_items f JOIN users u ON f.user_id=u.id ORDER BY f.created_at DESC")->fetchAll();
$matches    = $pdo->query("SELECT m.*, l.item_name AS lost_name, f.item_name AS found_name FROM matches m JOIN lost_items l ON m.lost_item_id=l.id JOIN found_items f ON m.found_item_id=f.id ORDER BY m.created_at DESC")->fetchAll();

$tab = $_GET['tab'] ?? 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard — AIUB Lost & Found</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#0a0a0f;--sidebar:#0f0f1a;--card:rgba(255,255,255,0.04);--border:rgba(255,255,255,0.08);--text:#fff;--muted:rgba(255,255,255,0.45);--primary:#7a0c2e;--primary2:#b91c1c;--green:#16a34a;--yellow:#d97706;--blue:#2563eb}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex}
/* Sidebar */
.sidebar{width:260px;min-height:100vh;background:var(--sidebar);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:24px 0;position:fixed;top:0;left:0;bottom:0;z-index:100}
.sidebar-logo{padding:0 24px 24px;border-bottom:1px solid var(--border);margin-bottom:16px}
.logo-box{display:flex;align-items:center;gap:12px}
.logo-icon{width:44px;height:44px;background:linear-gradient(135deg,var(--primary),var(--primary2));border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;flex-shrink:0}
.logo-text h2{font-size:15px;font-weight:800;color:#fff}
.logo-text p{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1.5px}
.nav-section{padding:0 16px;margin-bottom:8px}
.nav-label{font-size:10px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:2px;padding:0 8px;margin-bottom:8px}
.nav-link{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:10px;color:var(--muted);text-decoration:none;font-size:14px;font-weight:500;transition:all .2s;margin-bottom:2px}
.nav-link:hover,.nav-link.active{background:rgba(122,12,46,0.15);color:#fff}
.nav-link.active{background:rgba(122,12,46,0.2);color:#fff;border:1px solid rgba(122,12,46,0.3)}
.nav-link i{width:18px;text-align:center;font-size:14px}
.nav-badge{margin-left:auto;background:var(--primary);color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px}
.sidebar-footer{margin-top:auto;padding:16px 24px;border-top:1px solid var(--border)}
.logout-btn{display:flex;align-items:center;gap:10px;color:rgba(248,113,113,0.8);text-decoration:none;font-size:14px;font-weight:600;transition:color .2s}
.logout-btn:hover{color:#f87171}
/* Main */
.main{margin-left:260px;flex:1;min-height:100vh;padding:32px}
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:32px}
.topbar h1{font-size:24px;font-weight:800}
.topbar p{font-size:13px;color:var(--muted);margin-top:2px}
.admin-tag{background:rgba(122,12,46,0.2);border:1px solid rgba(122,12,46,0.3);border-radius:20px;padding:6px 14px;font-size:12px;font-weight:600;color:#f87171;display:flex;align-items:center;gap:6px}
/* Stats */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:20px;transition:transform .2s}
.stat-card:hover{transform:translateY(-2px)}
.stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:12px}
.stat-value{font-size:28px;font-weight:800;margin-bottom:4px}
.stat-label{font-size:12px;color:var(--muted);font-weight:500}
/* Table */
.section{background:var(--card);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:24px}
.section-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.section-header h3{font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px}
.add-btn{background:linear-gradient(135deg,var(--primary),var(--primary2));color:#fff;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:all .2s}
.add-btn:hover{transform:translateY(-1px);box-shadow:0 4px 16px rgba(122,12,46,0.4)}
table{width:100%;border-collapse:collapse}
thead tr{background:rgba(255,255,255,0.03)}
th{padding:12px 16px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--muted);white-space:nowrap}
td{padding:12px 16px;font-size:13px;color:rgba(255,255,255,0.8);border-top:1px solid var(--border);vertical-align:middle}
tr:hover td{background:rgba(255,255,255,0.02)}
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.badge-open{background:rgba(22,163,74,0.15);color:#4ade80;border:1px solid rgba(22,163,74,0.3)}
.badge-matched{background:rgba(37,99,235,0.15);color:#60a5fa;border:1px solid rgba(37,99,235,0.3)}
.badge-resolved{background:rgba(255,255,255,0.08);color:var(--muted);border:1px solid var(--border)}
.badge-pending{background:rgba(217,119,6,0.15);color:#fbbf24;border:1px solid rgba(217,119,6,0.3)}
.del-btn{background:rgba(220,38,38,0.15);border:1px solid rgba(220,38,38,0.2);color:#f87171;padding:5px 10px;border-radius:7px;font-size:12px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:4px;transition:all .2s}
.del-btn:hover{background:rgba(220,38,38,0.3)}
.success-alert{background:rgba(22,163,74,0.15);border:1px solid rgba(22,163,74,0.3);border-radius:10px;padding:12px 16px;color:#4ade80;font-size:13px;margin-bottom:20px;display:flex;align-items:center;gap:8px}
.avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary2));display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0}
.empty{padding:40px;text-align:center;color:var(--muted);font-size:14px}
/* Modal */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);z-index:1000;display:none;align-items:center;justify-content:center}
.modal-overlay.open{display:flex}
.modal{background:#131320;border:1px solid var(--border);border-radius:20px;padding:32px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto}
.modal h3{font-size:18px;font-weight:800;margin-bottom:24px;display:flex;align-items:center;gap:10px}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px}
.form-control{width:100%;padding:11px 14px;background:rgba(255,255,255,0.06);border:1px solid var(--border);border-radius:10px;color:#fff;font-size:14px;font-family:'Inter',sans-serif;transition:all .2s}
.form-control:focus{outline:none;border-color:var(--primary);background:rgba(122,12,46,0.08)}
.form-control option{background:#131320}
.modal-btns{display:flex;gap:12px;margin-top:24px}
.btn-primary{flex:1;padding:12px;background:linear-gradient(135deg,var(--primary),var(--primary2));border:none;border-radius:10px;color:#fff;font-size:14px;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer}
.btn-cancel{padding:12px 20px;background:rgba(255,255,255,0.06);border:1px solid var(--border);border-radius:10px;color:var(--muted);font-size:14px;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer}
@media(max-width:1024px){.stats-grid{grid-template-columns:repeat(2,1fr)}}
</style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-box">
      <div class="logo-icon"><i class="fa-solid fa-shield-halved"></i></div>
      <div class="logo-text">
        <h2>Admin Panel</h2>
        <p>AIUB Lost &amp; Found</p>
      </div>
    </div>
  </div>

  <div class="nav-section">
    <div class="nav-label">Main</div>
    <a href="?tab=overview" class="nav-link <?= $tab==='overview'?'active':'' ?>"><i class="fa-solid fa-chart-pie"></i> Overview</a>
    <a href="?tab=users" class="nav-link <?= $tab==='users'?'active':'' ?>"><i class="fa-solid fa-users"></i> Members <span class="nav-badge"><?= $totalUsers ?></span></a>
    <a href="?tab=lost" class="nav-link <?= $tab==='lost'?'active':'' ?>"><i class="fa-solid fa-triangle-exclamation"></i> Lost Items <span class="nav-badge"><?= $totalLost ?></span></a>
    <a href="?tab=found" class="nav-link <?= $tab==='found'?'active':'' ?>"><i class="fa-solid fa-box-open"></i> Found Items <span class="nav-badge"><?= $totalFound ?></span></a>
    <a href="?tab=matches" class="nav-link <?= $tab==='matches'?'active':'' ?>"><i class="fa-solid fa-link"></i> Matches <span class="nav-badge"><?= $totalMatch ?></span></a>
  </div>

  <div class="sidebar-footer">
    <a href="?action=logout" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </div>
</aside>

<!-- Main Content -->
<main class="main">
  <div class="topbar">
    <div>
      <h1>
        <?php
        $titles = ['overview'=>'Overview','users'=>'Members','lost'=>'Lost Items','found'=>'Found Items','matches'=>'Matches'];
        echo $titles[$tab] ?? 'Overview';
        ?>
      </h1>
      <p>Welcome back, <strong>vogoban</strong> — <?= date('l, d M Y') ?></p>
    </div>
    <div class="admin-tag"><i class="fa-solid fa-shield-halved"></i> Super Admin</div>
  </div>

  <?php if (isset($_GET['deleted'])): ?>
  <div class="success-alert"><i class="fa-solid fa-circle-check"></i> Item deleted successfully!</div>
  <?php endif; ?>
  <?php if (isset($_GET['added'])): ?>
  <div class="success-alert"><i class="fa-solid fa-circle-check"></i> Item added successfully!</div>
  <?php endif; ?>

  <?php if ($tab === 'overview'): ?>
  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(122,12,46,0.2)"><i class="fa-solid fa-users" style="color:#f87171"></i></div>
      <div class="stat-value"><?= $totalUsers ?></div>
      <div class="stat-label">Total Members</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(220,38,38,0.15)"><i class="fa-solid fa-triangle-exclamation" style="color:#fb923c"></i></div>
      <div class="stat-value"><?= $totalLost ?></div>
      <div class="stat-label">Lost Items</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(22,163,74,0.15)"><i class="fa-solid fa-box-open" style="color:#4ade80"></i></div>
      <div class="stat-value"><?= $totalFound ?></div>
      <div class="stat-label">Found Items</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(37,99,235,0.15)"><i class="fa-solid fa-link" style="color:#60a5fa"></i></div>
      <div class="stat-value"><?= $totalMatch ?></div>
      <div class="stat-label">Total Matches</div>
    </div>
  </div>

  <!-- Recent Members -->
  <div class="section">
    <div class="section-header">
      <h3><i class="fa-solid fa-users" style="color:#f87171"></i> Recent Members</h3>
      <a href="?tab=users" style="color:rgba(255,255,255,0.4);font-size:13px;text-decoration:none">View all →</a>
    </div>
    <table>
      <thead><tr><th>Member</th><th>Email</th><th>Student ID</th><th>Joined</th></tr></thead>
      <tbody>
        <?php foreach(array_slice($users,0,5) as $u): ?>
        <tr>
          <td><div style="display:flex;align-items:center;gap:10px"><div class="avatar"><?= strtoupper(substr($u['full_name'],0,1)) ?></div><?= htmlspecialchars($u['full_name']) ?></div></td>
          <td style="color:rgba(255,255,255,0.5)"><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['student_id'] ?? '—') ?></td>
          <td style="color:rgba(255,255,255,0.4)"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($users)): ?><tr><td colspan="4" class="empty"><i class="fa-solid fa-inbox"></i> No members yet</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php elseif ($tab === 'users'): ?>
  <!-- Users Table -->
  <div class="section">
    <div class="section-header">
      <h3><i class="fa-solid fa-users" style="color:#f87171"></i> All Members (<?= count($users) ?>)</h3>
      <a href="#" onclick="openModal('addUserModal')" class="add-btn"><i class="fa-solid fa-plus"></i> Add Member</a>
    </div>
    <table>
      <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Student ID</th><th>Joined</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach($users as $i=>$u): ?>
        <tr>
          <td style="color:var(--muted)"><?= $i+1 ?></td>
          <td><div style="display:flex;align-items:center;gap:10px"><div class="avatar"><?= strtoupper(substr($u['full_name'],0,1)) ?></div><?= htmlspecialchars($u['full_name']) ?></div></td>
          <td style="color:rgba(255,255,255,0.5)"><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['phone']) ?></td>
          <td><?= htmlspecialchars($u['student_id'] ?? '—') ?></td>
          <td style="color:rgba(255,255,255,0.4)"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          <td><a href="?action=delete&type=user&id=<?= $u['id'] ?>&tab=users" class="del-btn" onclick="return confirm('Delete this member and all their items?')"><i class="fa-solid fa-trash"></i> Delete</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($users)): ?><tr><td colspan="7" class="empty"><i class="fa-solid fa-inbox"></i> No members yet</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php elseif ($tab === 'lost'): ?>
  <!-- Lost Items Table -->
  <div class="section">
    <div class="section-header">
      <h3><i class="fa-solid fa-triangle-exclamation" style="color:#fb923c"></i> All Lost Items (<?= count($lostItems) ?>)</h3>
      <a href="#" onclick="openModal('addLostModal')" class="add-btn"><i class="fa-solid fa-plus"></i> Add Lost Item</a>
    </div>
    <table>
      <thead><tr><th>#</th><th>Item</th><th>Category</th><th>Reported By</th><th>Location</th><th>Date Lost</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach($lostItems as $i=>$item): ?>
        <tr>
          <td style="color:var(--muted)"><?= $i+1 ?></td>
          <td><strong><?= htmlspecialchars($item['item_name']) ?></strong><?php if($item['color']): ?><br><small style="color:var(--muted)"><?= htmlspecialchars($item['color']) ?></small><?php endif; ?></td>
          <td><?= htmlspecialchars($item['category']) ?></td>
          <td><div style="font-size:12px"><?= htmlspecialchars($item['full_name']) ?><br><span style="color:var(--muted)"><?= htmlspecialchars($item['email']) ?></span></div></td>
          <td style="color:rgba(255,255,255,0.6);font-size:12px"><?= htmlspecialchars($item['location']) ?></td>
          <td style="color:rgba(255,255,255,0.4)"><?= htmlspecialchars($item['date_lost']) ?></td>
          <td><span class="badge badge-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span></td>
          <td><a href="?action=delete&type=lost&id=<?= $item['id'] ?>&tab=lost" class="del-btn" onclick="return confirm('Delete this lost item?')"><i class="fa-solid fa-trash"></i> Delete</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($lostItems)): ?><tr><td colspan="8" class="empty"><i class="fa-solid fa-inbox"></i> No lost items yet</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php elseif ($tab === 'found'): ?>
  <!-- Found Items Table -->
  <div class="section">
    <div class="section-header">
      <h3><i class="fa-solid fa-box-open" style="color:#4ade80"></i> All Found Items (<?= count($foundItems) ?>)</h3>
      <a href="#" onclick="openModal('addFoundModal')" class="add-btn"><i class="fa-solid fa-plus"></i> Add Found Item</a>
    </div>
    <table>
      <thead><tr><th>#</th><th>Item</th><th>Category</th><th>Reported By</th><th>Location</th><th>Date Found</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach($foundItems as $i=>$item): ?>
        <tr>
          <td style="color:var(--muted)"><?= $i+1 ?></td>
          <td><strong><?= htmlspecialchars($item['item_name']) ?></strong><?php if($item['color']): ?><br><small style="color:var(--muted)"><?= htmlspecialchars($item['color']) ?></small><?php endif; ?></td>
          <td><?= htmlspecialchars($item['category']) ?></td>
          <td><div style="font-size:12px"><?= htmlspecialchars($item['full_name']) ?><br><span style="color:var(--muted)"><?= htmlspecialchars($item['email']) ?></span></div></td>
          <td style="color:rgba(255,255,255,0.6);font-size:12px"><?= htmlspecialchars($item['location']) ?></td>
          <td style="color:rgba(255,255,255,0.4)"><?= htmlspecialchars($item['date_found']) ?></td>
          <td><span class="badge badge-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span></td>
          <td><a href="?action=delete&type=found&id=<?= $item['id'] ?>&tab=found" class="del-btn" onclick="return confirm('Delete this found item?')"><i class="fa-solid fa-trash"></i> Delete</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($foundItems)): ?><tr><td colspan="8" class="empty"><i class="fa-solid fa-inbox"></i> No found items yet</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php elseif ($tab === 'matches'): ?>
  <!-- Matches Table -->
  <div class="section">
    <div class="section-header">
      <h3><i class="fa-solid fa-link" style="color:#60a5fa"></i> All Matches (<?= count($matches) ?>)</h3>
    </div>
    <table>
      <thead><tr><th>#</th><th>Lost Item</th><th>Found Item</th><th>Score</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach($matches as $i=>$m): ?>
        <tr>
          <td style="color:var(--muted)"><?= $i+1 ?></td>
          <td style="color:#f87171"><?= htmlspecialchars($m['lost_name']) ?></td>
          <td style="color:#4ade80"><?= htmlspecialchars($m['found_name']) ?></td>
          <td><strong style="color:<?= $m['match_score']>=75?'#4ade80':($m['match_score']>=55?'#fbbf24':'#f87171') ?>"><?= $m['match_score'] ?>%</strong></td>
          <td><span class="badge badge-<?= $m['status'] ?>"><?= ucfirst($m['status']) ?></span></td>
          <td style="color:var(--muted);font-size:12px"><?= date('d M Y', strtotime($m['created_at'])) ?></td>
          <td><a href="?action=delete&type=match&id=<?= $m['id'] ?>&tab=matches" class="del-btn" onclick="return confirm('Delete this match?')"><i class="fa-solid fa-trash"></i> Delete</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($matches)): ?><tr><td colspan="7" class="empty"><i class="fa-solid fa-inbox"></i> No matches yet</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</main>

<!-- Add User Modal -->
<div class="modal-overlay" id="addUserModal">
  <div class="modal">
    <h3><i class="fa-solid fa-user-plus" style="color:#f87171"></i> Add New Member</h3>
    <form method="POST" action="add-user.php">
      <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" class="form-control" required placeholder="e.g. Rafi Ahmed"></div>
      <div class="form-group"><label>Email *</label><input type="email" name="email" class="form-control" required placeholder="student@aiub.edu"></div>
      <div class="form-group"><label>Phone *</label><input type="text" name="phone" class="form-control" required placeholder="+880 1X-XXXX-XXXX"></div>
      <div class="form-group"><label>Student ID</label><input type="text" name="student_id" class="form-control" placeholder="e.g. 22-49876-1"></div>
      <div class="form-group"><label>Password *</label><input type="password" name="password" class="form-control" required placeholder="Set a password"></div>
      <div class="modal-btns">
        <button type="button" class="btn-cancel" onclick="closeModal('addUserModal')">Cancel</button>
        <button type="submit" class="btn-primary"><i class="fa-solid fa-plus"></i> Add Member</button>
      </div>
    </form>
  </div>
</div>

<!-- Add Lost Item Modal -->
<div class="modal-overlay" id="addLostModal">
  <div class="modal">
    <h3><i class="fa-solid fa-triangle-exclamation" style="color:#fb923c"></i> Add Lost Item</h3>
    <form method="POST" action="add-item.php">
      <input type="hidden" name="type" value="lost">
      <div class="form-group"><label>Assign to User *</label>
        <select name="user_id" class="form-control" required>
          <option value="">— Select Member —</option>
          <?php foreach($users as $u): ?><option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Item Name *</label><input type="text" name="item_name" class="form-control" required placeholder="e.g. Samsung Galaxy S23"></div>
      <div class="form-group"><label>Category *</label>
        <select name="category" class="form-control" required>
          <?php foreach(['Electronics','Documents / ID Card','Wallet / Purse','Keys','Bag / Backpack','Jewelry / Watch','Clothing','Books / Notes','Mobile Phone','Laptop','Other'] as $c): ?><option><?= $c ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Location *</label><input type="text" name="location" class="form-control" required placeholder="e.g. AIUB Library, 3rd Floor"></div>
      <div class="form-group"><label>Date Lost *</label><input type="date" name="date_lost" class="form-control" required max="<?= date('Y-m-d') ?>"></div>
      <div class="form-group"><label>Color</label><input type="text" name="color" class="form-control" placeholder="e.g. Black, Silver"></div>
      <div class="form-group"><label>Brand</label><input type="text" name="brand" class="form-control" placeholder="e.g. Samsung, Apple"></div>
      <div class="form-group"><label>Description *</label><textarea name="description" class="form-control" rows="3" required placeholder="Describe the item..."></textarea></div>
      <div class="modal-btns">
        <button type="button" class="btn-cancel" onclick="closeModal('addLostModal')">Cancel</button>
        <button type="submit" class="btn-primary"><i class="fa-solid fa-plus"></i> Add Item</button>
      </div>
    </form>
  </div>
</div>

<!-- Add Found Item Modal -->
<div class="modal-overlay" id="addFoundModal">
  <div class="modal">
    <h3><i class="fa-solid fa-box-open" style="color:#4ade80"></i> Add Found Item</h3>
    <form method="POST" action="add-item.php">
      <input type="hidden" name="type" value="found">
      <div class="form-group"><label>Assign to User *</label>
        <select name="user_id" class="form-control" required>
          <option value="">— Select Member —</option>
          <?php foreach($users as $u): ?><option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Item Name *</label><input type="text" name="item_name" class="form-control" required placeholder="e.g. Black Wallet"></div>
      <div class="form-group"><label>Category *</label>
        <select name="category" class="form-control" required>
          <?php foreach(['Electronics','Documents / ID Card','Wallet / Purse','Keys','Bag / Backpack','Jewelry / Watch','Clothing','Books / Notes','Mobile Phone','Laptop','Other'] as $c): ?><option><?= $c ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label>Location Found *</label><input type="text" name="location" class="form-control" required placeholder="e.g. AIUB Cafeteria"></div>
      <div class="form-group"><label>Date Found *</label><input type="date" name="date_found" class="form-control" required max="<?= date('Y-m-d') ?>"></div>
      <div class="form-group"><label>Color</label><input type="text" name="color" class="form-control" placeholder="e.g. Black, Silver"></div>
      <div class="form-group"><label>Brand</label><input type="text" name="brand" class="form-control" placeholder="e.g. Samsung, Apple"></div>
      <div class="form-group"><label>Description *</label><textarea name="description" class="form-control" rows="3" required placeholder="Describe the item..."></textarea></div>
      <div class="modal-btns">
        <button type="button" class="btn-cancel" onclick="closeModal('addFoundModal')">Cancel</button>
        <button type="submit" class="btn-primary"><i class="fa-solid fa-plus"></i> Add Item</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-overlay').forEach(o=>o.addEventListener('click',function(e){if(e.target===this)this.classList.remove('open')}));
</script>
</body>
</html>
