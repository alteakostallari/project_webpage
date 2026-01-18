<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    exit;
}

$userId = $_SESSION['user_id'];
$fieldId = (int) $_POST['field_id'];
$date = $_POST['date'];
$startTime = $_POST['start_time'];
$duration = (int) $_POST['duration'];
$paymentMethod = $_POST['payment_method'] ?? 'on_field'; // Default to on_field

$endTime = date("H:i:s", strtotime("$startTime +$duration minutes"));

// Merr çmimin e saktë
$stmt = $conn->prepare("SELECT price_per_hour, name FROM fushat WHERE id = ?");
$stmt->bind_param("i", $fieldId);
$stmt->execute();
$field = $stmt->get_result()->fetch_assoc();
$totalPrice = $field['price_per_hour'] * ($duration / 60);

// For Stripe, we defer insertion until success page
// Store ALL needed info in session
$_SESSION['pending_booking'] = [
    'user_id' => $userId,
    'field_id' => $fieldId,
    'date' => $date,
    'start_time' => $startTime,
    'end_time' => $endTime,
    'price' => $totalPrice,
    'price_per_hour' => $field['price_per_hour']
];

// Store details for payment.php display
$_SESSION['booking_details'] = [
    'field_name' => $field['name'],
    'date' => $date,
    'start_time' => $startTime,
    'duration' => $duration,
    'price' => $totalPrice,
    'price_per_hour' => $field['price_per_hour']
];

header("Location: payment.php");
exit;