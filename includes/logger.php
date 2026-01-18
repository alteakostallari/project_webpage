<?php
function logLogin($conn, $email, $status, $message)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    // MySQLi përdor placeholder-e me ?
    $stmt = $conn->prepare("
        INSERT INTO login_logs (user_email, ip_address, status, message)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("ssss", $email, $ip, $status, $message);
    $stmt->execute();
}
