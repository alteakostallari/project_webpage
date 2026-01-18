<?php
require_once "db.php";

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';

if (strlen($password) < 8) {
    die("Password must be at least 8 characters long");
}

$hashedToken = hash('sha256', $token);

// Gjej token
$stmt = $conn->prepare("
    SELECT user_id FROM password_resets
    WHERE reset_token = ? AND expires_at > NOW()
");
$stmt->bind_param("s", $hashedToken);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Token i pavlefshëm ose i skaduar.");
}

$row = $result->fetch_assoc();
$userId = $row['user_id'];

// Update password
$newPass = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $newPass, $userId);
$stmt->execute();

// Fshij token
$conn->query("DELETE FROM password_resets WHERE user_id = $userId");

echo "Password has been changed successfully. <a href='login.php'>Login</a>";
