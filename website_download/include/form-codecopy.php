<?php
ob_start();
// Start session for CAPTCHA
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CAPTCHA if not set
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = [
        'num1' => rand(1, 10),
        'num2' => rand(1, 10),
    ];
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name   = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email  = htmlspecialchars(trim($_POST['email'] ?? ''));
    $phone  = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $course = htmlspecialchars(trim($_POST['course'] ?? ''));
    $captcha = (int) ($_POST['captcha'] ?? 0);

    // Validate inputs
    if (!$name || !$email || !$phone || !$course) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($captcha !== ($_SESSION['captcha']['num1'] + $_SESSION['captcha']['num2'])) {
        $error = 'Incorrect CAPTCHA answer.';
    } else {
        // Email content
        $to = "sumitdabass@gmail.com,sonamdabas222@gmail.com";
        $subject = "Enquiry via Landing Page - $name";
        $message = "Name: $name\r\nEmail: $email\r\nPhone: $phone\r\nCourse: $course\r\n";
        $headers = "From: noreply@ipu.co.in\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
        $headers .= "X-Priority: 1\r\n";

        // Send email
        if (mail($to, $subject, $message, $headers)) {
            header("Location: https://ipu.co.in/thank-you.php");
            exit();
        } else {
            header("Location: https://ipu.co.in/thank-you.php?status=fail");
            exit();
        }

        // Reset CAPTCHA
        $_SESSION['captcha'] = [
            'num1' => rand(1, 10),
            'num2' => rand(1, 10),
        ];

        // Clear fields
        $name = $email = $phone = $course = '';
    }
}
?>
