<?php
session_start();
require_once "db.php";
require_once "mail.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Email is required.']);
    exit;
}

// 1. Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $userId = $user['id'];
    $code = random_int(100000, 999999);
    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry

    // 2. Clear old tokens for this user
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    // 3. Store new token (code)
    // Note: We are storing the 6-digit code as the reset_token
    $stmt = $conn->prepare("INSERT INTO password_resets (user_id, reset_token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $code, $expiresAt);

    if ($stmt->execute()) {
        // 4. Send email
        if (sendEmail($email, $code, 'reset')) {
            echo json_encode(['status' => 'success', 'message' => 'A verification code has been sent to your email.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send email. Please try again.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Server error. Please try again.']);
    }
} else {
    // For security, don't explicitly say the email doesn't exist?
    // Actually, in many cases it's better UX to say it doesn't exist.
    echo json_encode(['status' => 'error', 'message' => 'This email is not registered.']);
}
