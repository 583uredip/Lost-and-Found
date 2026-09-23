<?php
require_once __DIR__ . '/includes/functions.php';

$result = '';
$error  = '';

if (isset($_GET['send'])) {
    $to   = trim($_GET['to'] ?? '');
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $error = 'Valid email address দাও!';
    } else {
        $subject = '🔔 Similar Product Found — Test Email (AIUB Lost & Found)';
        $body = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:32px 16px;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.10);">
        <tr>
          <td style="background:linear-gradient(135deg,#7a0c2e 0%,#b91c1c 100%);padding:36px 32px;text-align:center;">
            <p style="margin:0 0 8px;font-size:13px;color:rgba(255,255,255,0.75);letter-spacing:2px;text-transform:uppercase;">AIUB Lost &amp; Found</p>
            <h1 style="margin:0;font-size:28px;font-weight:800;color:#ffffff;">&#128276; Similar Product Found!</h1>
            <p style="margin:12px 0 0;font-size:15px;color:rgba(255,255,255,0.85);">We may have found what you were looking for</p>
          </td>
        </tr>
        <tr>
          <td style="padding:32px;">
            <p style="font-size:16px;color:#374151;">Hi <strong>Test User</strong>,</p>
            <p style="font-size:15px;color:#6b7280;line-height:1.6;">Great news! Someone reported finding an item that closely matches your lost item report.</p>
            <div style="background:#f0fdf4;border:1.5px solid #16a34a;border-radius:12px;padding:16px 20px;margin:16px 0;">
              <span style="font-size:13px;font-weight:600;color:#16a34a;text-transform:uppercase;letter-spacing:1px;">&#9989; Match Confidence: 85%</span>
            </div>
            <table width="100%" cellpadding="0" cellspacing="0" style="border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
              <tr style="background:#f9fafb;">
                <th style="padding:12px 16px;text-align:left;font-size:12px;text-transform:uppercase;color:#9ca3af;">Field</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;text-transform:uppercase;color:#ef4444;">&#128683; Your Lost Item</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;text-transform:uppercase;color:#16a34a;">&#10003; Item Found</th>
              </tr>
              <tr style="border-top:1px solid #e5e7eb;">
                <td style="padding:12px 16px;font-size:13px;color:#6b7280;font-weight:600;">Item Name</td>
                <td style="padding:12px 16px;font-size:14px;font-weight:700;">Samsung Galaxy S23</td>
                <td style="padding:12px 16px;font-size:14px;font-weight:700;">Samsung Galaxy S23</td>
              </tr>
              <tr style="background:#f9fafb;border-top:1px solid #e5e7eb;">
                <td style="padding:12px 16px;font-size:13px;color:#6b7280;font-weight:600;">Location Found</td>
                <td style="padding:12px 16px;font-size:14px;" colspan="2">AIUB Library, 3rd Floor</td>
              </tr>
            </table>
            <div style="text-align:center;margin-top:24px;">
              <a href="#" style="display:inline-block;background:linear-gradient(135deg,#7a0c2e,#b91c1c);color:#fff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 32px;border-radius:50px;">&#128269; View Match Details</a>
            </div>
            <div style="background:#eff6ff;border-left:4px solid #3b82f6;border-radius:0 8px 8px 0;padding:14px 16px;margin-top:20px;">
              <p style="margin:0;font-size:13px;color:#1e40af;">&#128274; <strong>Privacy Note:</strong> This is a test email from AIUB Lost &amp; Found system.</p>
            </div>
          </td>
        </tr>
        <tr>
          <td style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:20px 32px;text-align:center;">
            <p style="margin:0;font-size:12px;color:#9ca3af;">AIUB Lost &amp; Found &mdash; American International University-Bangladesh</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

        // Try sending
        $errorMsg = null;
        $ok = send_email_notification($to, 'Test User', $subject, $body, $errorMsg);
        if ($ok) {
            $result = $to;
        } else {
            $error = 'Email পাঠানো সম্ভব হয়নি! Error: ' . ($errorMsg ?: 'PHPMailer error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Email Test — AIUB Lost & Found</title>
<style>
  body { font-family: 'Segoe UI', sans-serif; background: #0f0f0f; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
  .card { background: #1a1a1a; border: 1px solid #333; border-radius: 16px; padding: 36px; max-width: 520px; width: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
  h1 { color: #f87171; font-size: 1.5rem; margin: 0 0 8px; }
  p { color: #9ca3af; margin: 0 0 20px; font-size: 14px; }
  input { width: 100%; box-sizing: border-box; padding: 12px 16px; border-radius: 10px; border: 1px solid #333; background: #111; color: #fff; font-size: 15px; margin-bottom: 16px; }
  button { width: 100%; padding: 13px; background: linear-gradient(135deg,#7a0c2e,#b91c1c); color: #fff; border: none; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
  button:hover { opacity: 0.9; transform: translateY(-1px); }
  .success { background: #052e16; border: 1px solid #16a34a; border-radius: 10px; padding: 16px; margin-bottom: 20px; color: #4ade80; font-size: 14px; line-height: 1.5; }
  .error-box { background: #2d0a0a; border: 1px solid #dc2626; border-radius: 10px; padding: 16px; margin-bottom: 20px; color: #f87171; font-size: 14px; line-height: 1.5; }
  .tip-box { background: rgba(240,165,0,0.1); border: 1px solid rgba(240,165,0,0.3); border-radius: 10px; padding: 14px; margin-top: 20px; font-size: 13px; color: #fcd34d; line-height: 1.5; }
</style>
</head>
<body>
<div class="card">
  <h1>📧 Email System Diagnostics</h1>
  <p>এই page থেকে test email পাঠিয়ে নিশ্চিত হতে পারো যে Gmail SMTP ঠিকমতো কাজ করছে।</p>

  <?php if ($result): ?>
    <div class="success">
      ✅ Email সফলভাবে পাঠানো হয়েছে: <strong><?= htmlspecialchars($result) ?></strong> এ!<br>
      <small style="color:#86efac;margin-top:6px;display:block;">
        Inbox চেক করো। না পেলে <strong>Spam</strong> ফোল্ডার চেক করো এবং "Report not spam" এ ক্লিক করো।
      </small>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="error-box">❌ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="GET">
    <input type="hidden" name="send" value="1">
    <input type="email" name="to" placeholder="যেকোনো Gmail দাও (test এর জন্য)" required
           value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
    <button type="submit">🚀 Test Email পাঠাও</button>
  </form>

  <div class="tip-box">
    <strong>⚠️ জরুরী পরামর্শ (Gmail Blocked Check):</strong><br>
    যদি Gmail এ মেইল Spam এ যায় বা দেখা না যায়, চেক করো যে তোমার Gmail এ <code>redipbiswas8@gmail.com</code> <strong>Block</strong> করা আছে কিনা। Block থাকলে Gmail এ গিয়ে <strong>"Unblock sender"</strong> বাটনে ক্লিক করে আনব্লক করো!
  </div>

  <p style="margin-top:20px;font-size:12px;text-align:center;color:#6b7280;">
    SMTP Host: <code style="color:#93c5fd">smtp.gmail.com:587</code> &bull; Sender: <code style="color:#60a5fa"><?= SMTP_USERNAME ?></code>
  </p>
</div>
</body>
</html>
