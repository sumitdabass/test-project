<?php
/**
 * Form Handler — Replaces form-codecopy.php
 * Honeypot + time-based anti-spam (no CAPTCHA friction)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$form_error = '';
$form_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Honeypot check — bots fill hidden fields
    if (!empty($_POST['website'])) {
        // Bot detected, silently redirect (don't reveal detection)
        header("Location: /thank-you.php");
        exit();
    }

    // Time-based check — reject submissions faster than 2 seconds
    $form_loaded = $_SESSION['form_loaded_at'] ?? 0;
    if ($form_loaded > 0 && (time() - $form_loaded) < 2) {
        header("Location: /thank-you.php");
        exit();
    }

    // Sanitize inputs
    $name   = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email  = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
    $phone  = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
    $course = htmlspecialchars(trim($_POST['course'] ?? ''), ENT_QUOTES, 'UTF-8');

    // Validate required fields (phone is primary, email is optional)
    if (!$name || !$phone || !$course) {
        $form_error = 'Please fill in Name, Phone, and Course.';
    } elseif (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        $form_error = 'Please enter a valid 10-digit Indian phone number.';
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = 'Please enter a valid email address.';
    } else {
        // Capture UTM parameters if present
        $utm_source = htmlspecialchars($_POST['utm_source'] ?? $_GET['utm_source'] ?? '', ENT_QUOTES, 'UTF-8');
        $utm_medium = htmlspecialchars($_POST['utm_medium'] ?? $_GET['utm_medium'] ?? '', ENT_QUOTES, 'UTF-8');
        $utm_campaign = htmlspecialchars($_POST['utm_campaign'] ?? $_GET['utm_campaign'] ?? '', ENT_QUOTES, 'UTF-8');
        $page_url = htmlspecialchars($_POST['page_url'] ?? $_SERVER['HTTP_REFERER'] ?? '', ENT_QUOTES, 'UTF-8');

        // Build email
        $to = "sumitdabass@gmail.com,sonamdabas222@gmail.com";
        $subject = "New Enquiry: $name - $course";

        $body = "Name: $name\r\n";
        $body .= "Phone: $phone\r\n";
        $body .= "Email: $email\r\n";
        $body .= "Course: $course\r\n";
        $body .= "Source Page: $page_url\r\n";
        if ($utm_source) $body .= "UTM Source: $utm_source\r\n";
        if ($utm_medium) $body .= "UTM Medium: $utm_medium\r\n";
        if ($utm_campaign) $body .= "UTM Campaign: $utm_campaign\r\n";
        $body .= "Time: " . date('Y-m-d H:i:s') . "\r\n";

        $headers = "From: noreply@ipu.co.in\r\n";
        $headers .= "Reply-To: " . ($email ?: 'admission@ipu.co.in') . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
        $headers .= "X-Priority: 1\r\n";

        // Send email
        mail($to, $subject, $body, $headers);

        // Send to Google Sheets
        $sheets_data = json_encode([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'course' => $course,
            'source' => $utm_source,
            'medium' => $utm_medium,
            'campaign' => $utm_campaign,
            'page' => $page_url,
        ]);

        $ch = curl_init('https://script.google.com/macros/s/AKfycbz_8geQQfgTGW5FT6kVahb7KeVGh0EGyIBzKvwcISjqA0ZN7GhALp9jXqTGN0iqiQaQvw/exec');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $sheets_data,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        curl_exec($ch);
        curl_close($ch);

        // Store for enhanced conversions on thank-you page
        $_SESSION['enh_email'] = $email;
        $_SESSION['enh_phone'] = $phone;

        // Redirect to thank-you
        header("Location: /thank-you.php");
        exit();
    }
}
?>
