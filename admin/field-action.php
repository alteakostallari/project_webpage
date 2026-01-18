<?php
require_once "../includes/session.php";
require_once "../db.php";

/* Vetëm admin */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$id = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id <= 0 && $action !== 'add_field') {
    header("Location: manage-fields.php");
    exit;
}

switch ($action) {

    case "add_field":
        $name = $_POST['name'] ?? '';
        $sport_id = $_POST['sport_id'] ?? '';
        $type = $_POST['type'] ?? '';
        $location = $_POST['location'] ?? '';
        $price = $_POST['price_per_hour'] ?? '';
        $status = $_POST['statusi'] ?? 'active';

        $stmt = $conn->prepare("INSERT INTO fushat (name, sport_id, type, location, price_per_hour, statusi) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $sport_id, $type, $location, $price, $status);
        $stmt->execute();
        break;

    case "toggle_status":
        // Merr statusin aktual
        $stmt = $conn->prepare("SELECT statusi FROM fushat WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            $newStatus = ($row['statusi'] === 'active') ? 'inactive' : 'active';

            $update = $conn->prepare("UPDATE fushat SET statusi = ? WHERE id = ?");
            $update->bind_param("si", $newStatus, $id);
            $update->execute();
        }
        break;

    case "update_field":
        $name = $_POST['name'] ?? '';
        $sport_id = $_POST['sport_id'] ?? '';
        $type = $_POST['type'] ?? '';
        $location = $_POST['location'] ?? '';
        $price = $_POST['price_per_hour'] ?? '';
        $status = $_POST['statusi'] ?? '';

        $stmt = $conn->prepare("UPDATE fushat SET name=?, sport_id=?, type=?, location=?, price_per_hour=?, statusi=? WHERE id=?");
        $stmt->bind_param("ssssssi", $name, $sport_id, $type, $location, $price, $status, $id);
        $stmt->execute();
        break;

    case "delete":
        $stmt = $conn->prepare("DELETE FROM fushat WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        break;
}

header("Location: manage-fields.php");
exit;
