<?php
/**
 * sendemail.php — Form submission handler
 * 6-layer duplicate prevention (no CAPTCHA friction):
 *   1. Honeypot           — bots fill hidden `website` field
 *   2. Time-based check   — reject submissions faster than 3 seconds
 *   3. 5-min cooldown     — block any resubmission within 5 minutes (session)
 *   4. Phone session dedup — reject same phone number within the session
 *   5. Cookie 24h dedup   — reject same phone hash within 24 hours (cookie)
 *   6. Persistent phone dedup — reject same phone hash within 7 days (server-side file)
 */
require_once __DIR__ . '/include/helpers/phone-dedup.php';
require_once __DIR__ . '/include/helpers/lead-fallback.php';
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_cache_limiter('public'); session_cache_expire(30); session_start(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ── Layer 1: Honeypot — bots fill hidden fields ──────────────────────────
    if (!empty($_POST['website'])) {
        header("Location: /thank-you.php");
        exit();
    }

    // ── Layer 2: Time-based check — reject submissions faster than 3 seconds ─
    $form_loaded = $_SESSION['form_loaded_at'] ?? 0;
    if ($form_loaded > 0 && (time() - $form_loaded) < 3) {
        header("Location: /thank-you.php");
        exit();
    }

    // ── Layer 3: 5-minute cooldown — block any resubmission within 5 min ─────
    $last_submit = $_SESSION['last_submit_time'] ?? 0;
    if ($last_submit > 0 && (time() - $last_submit) < 300) {
        header("Location: /thank-you.php");
        exit();
    }

    // Sanitize input
    $name   = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email  = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
    $phone  = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
    $course = htmlspecialchars(trim($_POST['course'] ?? ''), ENT_QUOTES, 'UTF-8');

    // Validate required fields + phone format
    if (!$name || !$phone || !$course) {
        header("Location: /?error=fields");
        exit();
    }
    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        header("Location: /?error=phone");
        exit();
    }
    // Reject email values containing CR/LF (header-injection guard on Reply-To)
    if ($email !== '' && (preg_match('/[\r\n]/', $email) || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
        $email = '';
    }

    // ── Layer 4: Phone session dedup — reject same phone in this session ──────
    if (!isset($_SESSION['submitted_phones'])) {
        $_SESSION['submitted_phones'] = [];
    }
    if (in_array($phone, $_SESSION['submitted_phones'], true)) {
        header("Location: /thank-you.php");
        exit();
    }

    // ── Layer 5: Cookie 24h dedup — reject same phone hash within 24h ─────────
    $phone_hash = 'ipu_eq_' . hash('sha256', $phone);
    if (!empty($_COOKIE[$phone_hash])) {
        header("Location: /thank-you.php");
        exit();
    }

    // ── Layer 6: Persistent 7-day dedup — survives cookie clear / new session ─
    if (phone_recently_seen($phone)) {
        header("Location: /thank-you.php");
        exit();
    }

    // Capture UTM & page source
    $page_url = htmlspecialchars($_POST['page_url'] ?? $_SERVER['HTTP_REFERER'] ?? '', ENT_QUOTES, 'UTF-8');

    // Send email
    $to = "sumitdabass@gmail.com,sonamdabas222@gmail.com";
    $subject = "New Enquiry: $name - $course";

    $message  = "Name: $name\r\n";
    $message .= "Phone: $phone\r\n";
    $message .= "Email: $email\r\n";
    $message .= "Course: $course\r\n";
    $message .= "Source: $page_url\r\n";
    $message .= "Time: " . date('Y-m-d H:i:s') . "\r\n";

    $headers  = "From: noreply@ipu.co.in\r\n";
    $headers .= "Reply-To: " . ($email ?: 'admission@ipu.co.in') . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    $headers .= "X-Priority: 1\r\n";

    $mail_ok = mail($to, $subject, $message, $headers);

    // Send to Google Sheet
    $url = "https://script.google.com/macros/s/AKfycbz_8geQQfgTGW5FT6kVahb7KeVGh0EGyIBzKvwcISjqA0ZN7GhALp9jXqTGN0iqiQaQvw/exec";
    $data = json_encode([
        'name'    => $name,
        'email'   => $email,
        'phone'   => $phone,
        'course'  => $course,
        'city'    => 'Website',
        'message' => $page_url,
        'source'  => 'ipu.co.in',
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $data,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $sheet_resp = curl_exec($ch);
    $sheet_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $sheet_err  = curl_errno($ch);
    curl_close($ch);
    $sheet_ok = ($sheet_err === 0 && $sheet_code >= 200 && $sheet_code < 400);

    // If EITHER delivery channel failed, persist the full lead so it is recoverable.
    if (!$mail_ok || !$sheet_ok) {
        lead_fallback_save([
            'name' => $name, 'phone' => $phone, 'email' => $email,
            'course' => $course, 'source' => $page_url,
            'mail_ok' => (bool)$mail_ok, 'sheet_ok' => $sheet_ok, 'sheet_code' => $sheet_code,
        ], 'delivery_failure');
    }

    // Store for enhanced conversions
    $_SESSION['enh_email'] = $email;
    $_SESSION['enh_phone'] = $phone;

    // Record dedup state so subsequent submissions are blocked
    $_SESSION['last_submit_time']   = time();
    $_SESSION['submitted_phones'][] = $phone;
    setcookie($phone_hash, '1', time() + 86400, '/', '', true, true);
    phone_record_seen($phone);
    lead_record($phone, 'sendemail');

    // Redirect to thank-you with success flag (only genuine submissions get src=submit)
    header("Location: /thank-you.php?src=submit");
    exit();

} else {
    header("Location: /");
    exit();
}
?>
