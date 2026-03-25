<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Honeypot check
    if (!empty($_POST['website'])) {
        header("Location: /thank-you.php");
        exit();
    }

    // Sanitize input
    $name   = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email  = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
    $phone  = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
    $course = htmlspecialchars(trim($_POST['course'] ?? ''), ENT_QUOTES, 'UTF-8');

    // Validate
    if (!$name || !$phone || !$course) {
        header("Location: /?error=fields");
        exit();
    }

    // Capture UTM & page source
    $page_url = htmlspecialchars($_POST['page_url'] ?? $_SERVER['HTTP_REFERER'] ?? '', ENT_QUOTES, 'UTF-8');

    // Send email
    $to = "sumitdabass@gmail.com,sonamdabas222@gmail.com";
    $subject = "New Enquiry: $name - $course";

    $message = "Name: $name\r\n";
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
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'course' => $course,
        'city' => 'Website',
        'message' => $page_url,
        'source' => 'ipu.co.in',
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    curl_exec($ch);
    curl_close($ch);

    // Store for enhanced conversions
    $_SESSION['enh_email'] = $email;
    $_SESSION['enh_phone'] = $phone;

    // Redirect to thank-you page
    header("Location: /thank-you.php");
    exit();

} else {
    header("Location: /");
    exit();
}
?>
