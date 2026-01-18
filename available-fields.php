<?php
require_once "db.php";

// Marrim të dhënat nga kërkesa POST e JavaScript
$sportId = isset($_POST['sport_id']) ? (int) $_POST['sport_id'] : 0;
$location = $_POST['location'] ?? '';
$date = $_POST['date'] ?? '';
$startTime = $_POST['start_time'] ?? '';
$duration = isset($_POST['duration']) ? (int) $_POST['duration'] : 60;
$fieldType = $_POST['field_type'] ?? 'indoor';

// Llogaritja e orës së mbarimit bazuar në kohëzgjatjen
$endTime = date("H:i:s", strtotime("$startTime +$duration minutes"));

// Query SQL për të gjetur fushat që:
// 1. Janë të sportit të duhur (sport_id)
// 2. Janë në qytetin e duhur (location)
// 3. Janë të tipit të duhur (indoor/outdoor)
// 4. Janë aktive (statusi = 'active')
// 5. NUK kanë rezervim në tabelën bookings që përplaset me orarin e zgjedhur
$sql = "
SELECT f.id, f.name, f.price_per_hour FROM fushat f
WHERE f.sport_id = ? 
  AND f.location = ? 
  AND f.type = ? 
  AND f.statusi = 'active' 
  AND f.id NOT IN (
    SELECT b.field_id FROM bookings b
    WHERE b.booking_date = ? 
      AND b.payment_status != 'cancelled'
      AND (
          (b.start_time < ? AND b.end_time > ?)
      )
)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isssss", $sportId, $location, $fieldType, $date, $endTime, $startTime);
$stmt->execute();
$result = $stmt->get_result();

$fields = [];
while ($row = $result->fetch_assoc()) {
  $fields[] = $row;
}

// Kthejmë rezultatin në format JSON për JavaScript-in
header('Content-Type: application/json');
echo json_encode(["fields" => $fields]);