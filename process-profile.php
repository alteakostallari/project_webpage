<?php
session_start();
require_once "db.php";

// vetëm përdorues të loguar mund të hyjnë

$userId = $_SESSION['user_id'];
$firstName = $_POST['first_name'] ?? '';
$lastName = $_POST['last_name'] ?? '';

// Validim bazik
if (!$firstName || !$lastName) {
    echo json_encode(["success" => false, "message" => "First and last name are required"]);
    exit;
}

// Përpunimi i avatar
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}
$avatarPath = null;
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $newName = "uploads/avatar_" . $userId . "." . $ext;
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $newName)) {
        $avatarPath = $newName;
    }
}

// Përditëso DB
$result = false;
if ($avatarPath) {
    $stmt = $conn->prepare("UPDATE users SET name = ?, surname = ?, profile_image = ? WHERE id = ?");
    $stmt->bind_param("sssi", $firstName, $lastName, $avatarPath, $userId);
    $result = $stmt->execute();
} else {
    $stmt = $conn->prepare("UPDATE users SET name = ?, surname = ? WHERE id = ?");
    $stmt->bind_param("ssi", $firstName, $lastName, $userId);
    $result = $stmt->execute();
}

if ($result) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Database error"]);
}

// PDO connections close automatically
