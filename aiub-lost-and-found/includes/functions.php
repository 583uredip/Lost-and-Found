<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

/* ------------------------------------------------------------------ */
/* General helpers                                                    */
/* ------------------------------------------------------------------ */

function clean($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . SITE_URL . '/auth/login.php');
        exit;
    }
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function redirect($path) {
    header('Location: ' . SITE_URL . '/' . ltrim($path, '/'));
    exit;
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return;
    }
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function item_categories() {
    return [
        'Electronics', 'Documents / ID Card', 'Wallet / Purse', 'Keys',
        'Bag / Backpack', 'Jewelry / Watch', 'Clothing', 'Books / Notes',
        'Mobile Phone', 'Laptop', 'Other'
    ];
}

/* ------------------------------------------------------------------ */
/* Image upload                                                       */
/* ------------------------------------------------------------------ */

/**
 * Handles a single image upload from $_FILES.
 * Returns the stored filename on success, null if no file, false on error.
 */
function handle_image_upload($fileField, $targetDir) {
    if (empty($_FILES[$fileField]['name'])) {
        return null; // no file selected - allowed (optional)
    }

    $file      = $_FILES[$fileField];
    $allowed   = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSizeMB = 5;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    if ($file['size'] > $maxSizeMB * 1024 * 1024) {
        return false;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return false;
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $newName = uniqid('img_', true) . '.' . $ext;
    $destination = $targetDir . $newName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $newName;
    }
    return false;
}

/* ------------------------------------------------------------------ */
/* Notifications + Email                                              */
/* ------------------------------------------------------------------ */

function create_notification($pdo, $userId, $title, $message, $link = null) {
    $stmt = $pdo->prepare(
        "INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$userId, $title, $message, $link]);
}

/**
 * Sends an HTML email via Gmail SMTP using PHPMailer.
 * Requires SMTP_USERNAME, SMTP_PASSWORD defined in config/database.php
 */
function send_email_notification($toEmail, $toName, $subject, $htmlBody, &$errorMsg = null) {
    require_once __DIR__ . '/PHPMailer/Exception.php';
    require_once __DIR__ . '/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';
        $mail->Timeout    = 15;

        // SSL options to prevent self-signed or missing CA issues on InfinityFree
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        // Sender & recipient
        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo(SMTP_USERNAME, SMTP_FROM_NAME);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $mail->send();
        return true;
    } catch (PHPMailer\PHPMailer\Exception $e) {
        $errorMsg = $e->getMessage();
        error_log('[PHPMailer] Email failed to ' . $toEmail . ': ' . $errorMsg);
        return false;
    } catch (\Exception $e) {
        $errorMsg = $e->getMessage();
        error_log('[PHPMailer General Error] ' . $errorMsg);
        return false;
    }
}

function notify_match_found($pdo, $lostItem, $foundItem, $matchScore) {
    // 1. Fetch Lost item owner details
    $stmt = $pdo->prepare("SELECT id, full_name, email FROM users WHERE id = ?");
    $stmt->execute([$lostItem['user_id']]);
    $lostOwner = $stmt->fetch();

    // 2. Fetch Found item finder details
    $stmt = $pdo->prepare("SELECT id, full_name, email FROM users WHERE id = ?");
    $stmt->execute([$foundItem['user_id']]);
    $finder = $stmt->fetch();

    $siteName       = SITE_NAME;
    $dashboardUrl   = SITE_URL . '/dashboard.php';
    $lostDetailUrl  = SITE_URL . '/item-details.php?type=lost&id=' . $lostItem['id'];
    $foundDetailUrl = SITE_URL . '/item-details.php?type=found&id=' . $foundItem['id'];
    $lostName       = htmlspecialchars($lostItem['item_name']);
    $foundName      = htmlspecialchars($foundItem['item_name']);
    $foundLoc       = htmlspecialchars($foundItem['location'] ?? 'AIUB Campus');
    $foundDate      = htmlspecialchars($foundItem['date_found'] ?? date('Y-m-d'));
    $foundCat       = htmlspecialchars($foundItem['category'] ?? 'General');
    $scoreColor     = $matchScore >= 75 ? '#16a34a' : ($matchScore >= 55 ? '#d97706' : '#dc2626');
    $scoreBg        = $matchScore >= 75 ? '#f0fdf4' : ($matchScore >= 55 ? '#fffbeb' : '#fef2f2');

    $allSent = true;

    // --- A. Notify Lost Item Owner ---
    if ($lostOwner && !empty($lostOwner['email'])) {
        $ownerName = htmlspecialchars($lostOwner['full_name']);
        $title     = '🔔 Similar Product Found — ' . $lostItem['item_name'];
        $message   = "We found a '{$foundItem['item_name']}' that looks like a match ({$matchScore}% similarity) for your lost report '{$lostItem['item_name']}'. Check your dashboard for details.";
        $link      = 'item-details.php?type=lost&id=' . $lostItem['id'];

        create_notification($pdo, $lostOwner['id'], $title, $message, $link);

        $bodyLost = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:32px 16px;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.10);">

        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#7a0c2e 0%,#b91c1c 100%);padding:36px 32px;text-align:center;">
            <p style="margin:0 0 8px;font-size:13px;color:rgba(255,255,255,0.75);letter-spacing:2px;text-transform:uppercase;">{$siteName}</p>
            <h1 style="margin:0;font-size:28px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">&#128276; Similar Product Found!</h1>
            <p style="margin:12px 0 0;font-size:15px;color:rgba(255,255,255,0.85);">We may have found what you were looking for</p>
          </td>
        </tr>

        <!-- Greeting -->
        <tr>
          <td style="padding:32px 32px 0;">
            <p style="margin:0;font-size:16px;color:#374151;">Hi <strong>{$ownerName}</strong>,</p>
            <p style="margin:12px 0 0;font-size:15px;color:#6b7280;line-height:1.6;">
              Great news! Someone reported finding an item that closely matches your lost item report. Here are the details:
            </p>
          </td>
        </tr>

        <!-- Match Score Banner -->
        <tr>
          <td style="padding:24px 32px 0;">
            <div style="background:{$scoreBg};border:1.5px solid {$scoreColor};border-radius:12px;padding:16px 20px;display:flex;align-items:center;">
              <span style="font-size:13px;font-weight:600;color:{$scoreColor};text-transform:uppercase;letter-spacing:1px;">&#9989; Match Confidence</span>
              <span style="float:right;font-size:22px;font-weight:800;color:{$scoreColor};">{$matchScore}%</span>
            </div>
          </td>
        </tr>

        <!-- Item Comparison Table -->
        <tr>
          <td style="padding:24px 32px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" style="border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
              <tr style="background:#f9fafb;">
                <th style="padding:12px 16px;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;font-weight:600;">Field</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#ef4444;font-weight:600;">&#128683; Your Lost Item</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#16a34a;font-weight:600;">&#10003; Item Found</th>
              </tr>
              <tr style="border-top:1px solid #e5e7eb;">
                <td style="padding:12px 16px;font-size:13px;color:#6b7280;font-weight:600;">Item Name</td>
                <td style="padding:12px 16px;font-size:14px;color:#111827;font-weight:700;">{$lostName}</td>
                <td style="padding:12px 16px;font-size:14px;color:#111827;font-weight:700;">{$foundName}</td>
              </tr>
              <tr style="background:#f9fafb;border-top:1px solid #e5e7eb;">
                <td style="padding:12px 16px;font-size:13px;color:#6b7280;font-weight:600;">Category</td>
                <td style="padding:12px 16px;font-size:14px;color:#374151;">{$foundCat}</td>
                <td style="padding:12px 16px;font-size:14px;color:#374151;">{$foundCat}</td>
              </tr>
              <tr style="border-top:1px solid #e5e7eb;">
                <td style="padding:12px 16px;font-size:13px;color:#6b7280;font-weight:600;">Location Found</td>
                <td style="padding:12px 16px;font-size:14px;color:#374151;" colspan="2">{$foundLoc}</td>
              </tr>
              <tr style="background:#f9fafb;border-top:1px solid #e5e7eb;">
                <td style="padding:12px 16px;font-size:13px;color:#6b7280;font-weight:600;">Date Found</td>
                <td style="padding:12px 16px;font-size:14px;color:#374151;" colspan="2">{$foundDate}</td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- CTA Buttons -->
        <tr>
          <td style="padding:28px 32px;text-align:center;">
            <a href="{$lostDetailUrl}" style="display:inline-block;background:linear-gradient(135deg,#7a0c2e,#b91c1c);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 32px;border-radius:50px;margin:0 8px 12px;">
              &#128269; View Match Details
            </a>
            <br>
            <a href="{$dashboardUrl}" style="display:inline-block;background:#f3f4f6;color:#374151;text-decoration:none;font-size:14px;font-weight:600;padding:12px 28px;border-radius:50px;margin-top:8px;border:1px solid #e5e7eb;">
              Go to Dashboard
            </a>
          </td>
        </tr>

        <!-- Note -->
        <tr>
          <td style="padding:0 32px 20px;">
            <div style="background:#eff6ff;border-left:4px solid #3b82f6;border-radius:0 8px 8px 0;padding:14px 16px;">
              <p style="margin:0;font-size:13px;color:#1e40af;line-height:1.5;">
                <strong>&#128274; Privacy Note:</strong> Contact information is only shared after you confirm a match. Please log in to your dashboard to proceed.
              </p>
            </div>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:20px 32px;text-align:center;">
            <p style="margin:0;font-size:12px;color:#9ca3af;">This email was sent by <strong>{$siteName}</strong>.</p>
            <p style="margin:8px 0 0;font-size:12px;color:#9ca3af;">American International University-Bangladesh &mdash; Lost &amp; Found System</p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

        $subjectLost = '🔔 Similar Product Found — ' . $lostItem['item_name'];
        $resLost = send_email_notification($lostOwner['email'], $lostOwner['full_name'], $subjectLost, $bodyLost);
        if (!$resLost) $allSent = false;
    }

    // --- B. Notify Found Item Finder (if different from lost owner) ---
    if ($finder && !empty($finder['email']) && (!$lostOwner || $finder['id'] !== $lostOwner['id'])) {
        $finderName    = htmlspecialchars($finder['full_name']);
        $titleFinder   = '🔔 Match Found for Your Found Report — ' . $foundItem['item_name'];
        $messageFinder = "Someone reported losing '{$lostItem['item_name']}' that matches the item you found ({$matchScore}% similarity). Thank you for helping!";
        $linkFinder    = 'item-details.php?type=found&id=' . $foundItem['id'];

        create_notification($pdo, $finder['id'], $titleFinder, $messageFinder, $linkFinder);

        $bodyFinder = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:32px 16px;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.10);">

        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#15803d 0%,#16a34a 100%);padding:36px 32px;text-align:center;">
            <p style="margin:0 0 8px;font-size:13px;color:rgba(255,255,255,0.75);letter-spacing:2px;text-transform:uppercase;">{$siteName}</p>
            <h1 style="margin:0;font-size:28px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">&#127881; Match Found!</h1>
            <p style="margin:12px 0 0;font-size:15px;color:rgba(255,255,255,0.85);">Someone may have lost the item you found</p>
          </td>
        </tr>

        <!-- Greeting -->
        <tr>
          <td style="padding:32px 32px 0;">
            <p style="margin:0;font-size:16px;color:#374151;">Hi <strong>{$finderName}</strong>,</p>
            <p style="margin:12px 0 0;font-size:15px;color:#6b7280;line-height:1.6;">
              Great news! A lost item report has been matched with the item you reported finding (<strong>{$foundName}</strong>).
            </p>
          </td>
        </tr>

        <!-- Match Score Banner -->
        <tr>
          <td style="padding:24px 32px 0;">
            <div style="background:{$scoreBg};border:1.5px solid {$scoreColor};border-radius:12px;padding:16px 20px;display:flex;align-items:center;">
              <span style="font-size:13px;font-weight:600;color:{$scoreColor};text-transform:uppercase;letter-spacing:1px;">&#9989; Match Confidence</span>
              <span style="float:right;font-size:22px;font-weight:800;color:{$scoreColor};">{$matchScore}%</span>
            </div>
          </td>
        </tr>

        <!-- Comparison Table -->
        <tr>
          <td style="padding:24px 32px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" style="border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
              <tr style="background:#f9fafb;">
                <th style="padding:12px 16px;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;font-weight:600;">Field</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#16a34a;font-weight:600;">&#10003; Item You Found</th>
                <th style="padding:12px 16px;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#ef4444;font-weight:600;">&#128683; Matched Lost Report</th>
              </tr>
              <tr style="border-top:1px solid #e5e7eb;">
                <td style="padding:12px 16px;font-size:13px;color:#6b7280;font-weight:600;">Item Name</td>
                <td style="padding:12px 16px;font-size:14px;color:#111827;font-weight:700;">{$foundName}</td>
                <td style="padding:12px 16px;font-size:14px;color:#111827;font-weight:700;">{$lostName}</td>
              </tr>
              <tr style="background:#f9fafb;border-top:1px solid #e5e7eb;">
                <td style="padding:12px 16px;font-size:13px;color:#6b7280;font-weight:600;">Category</td>
                <td style="padding:12px 16px;font-size:14px;color:#374151;">{$foundCat}</td>
                <td style="padding:12px 16px;font-size:14px;color:#374151;">{$foundCat}</td>
              </tr>
              <tr style="border-top:1px solid #e5e7eb;">
                <td style="padding:12px 16px;font-size:13px;color:#6b7280;font-weight:600;">Location</td>
                <td style="padding:12px 16px;font-size:14px;color:#374151;" colspan="2">{$foundLoc}</td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- CTA Buttons -->
        <tr>
          <td style="padding:28px 32px;text-align:center;">
            <a href="{$foundDetailUrl}" style="display:inline-block;background:linear-gradient(135deg,#15803d,#16a34a);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 32px;border-radius:50px;margin:0 8px 12px;">
              &#128269; View Match Details
            </a>
            <br>
            <a href="{$dashboardUrl}" style="display:inline-block;background:#f3f4f6;color:#374151;text-decoration:none;font-size:14px;font-weight:600;padding:12px 28px;border-radius:50px;margin-top:8px;border:1px solid #e5e7eb;">
              Go to Dashboard
            </a>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:20px 32px;text-align:center;">
            <p style="margin:0;font-size:12px;color:#9ca3af;">Thank you for helping the AIUB community return lost items!</p>
            <p style="margin:8px 0 0;font-size:12px;color:#9ca3af;">{$siteName} &mdash; American International University-Bangladesh</p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

        $subjectFinder = '🔔 Match Found for Your Found Item — ' . $foundItem['item_name'];
        $resFinder = send_email_notification($finder['email'], $finder['full_name'], $subjectFinder, $bodyFinder);
        if (!$resFinder) $allSent = false;
    }

    return $allSent;
}
