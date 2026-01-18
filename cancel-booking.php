<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: my-bookings.php");
    exit;
}

$bookingId = (int) $_GET['id'];
$userId = $_SESSION['user_id'];

// Check if the booking belongs to the user and is in the future
$stmt = $conn->prepare("SELECT booking_date, start_time FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $bookingId, $userId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if ($booking) {
    $booking_start = strtotime($booking['booking_date'] . ' ' . $booking['start_time']);
    $now = time();

    if ($now < $booking_start) {
        $update = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $update->bind_param("i", $bookingId);
        $update->execute();
    }
}

header("Location: my-bookings.php");
exit;
