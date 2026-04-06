<?php
/**
 * sendemail.php — Form submission handler
 * 5-layer duplicate prevention (no CAPTCHA friction):
 *   1. Honeypot           — bots fill hidden `website` field
 *   2. Time-based check   — reject submissions faster than 3 seconds
 *   3. 5-min cooldown     — block any resubmission within 5 minutes (session)
 *   4. Phone session dedup — reject same phone number within the session
 *   5. Cookie 24h dedup   — reject same phone hash within 24 hours (cookie)
 */
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

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

    mail($to, $subject, $message, $headers);

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
    curl_exec($ch);
    curl_close($ch);

    // Store for enhanced conversions
    $_SESSION['enh_email'] = $email;
    $_SESSION['enh_phone'] = $phone;

    // Record dedup state so subsequent submissions are blocked
    $_SESSION['last_submit_time']   = time();
    $_SESSION['submitted_phones'][] = $phone;
    setcookie($phone_hash, '1', time() + 86400, '/', '', true, true);

    // Redirect to thank-you page
    header("Location: /thank-you.php");
    exit();

} else {
    header("Location: /");
    exit();
}
?>
