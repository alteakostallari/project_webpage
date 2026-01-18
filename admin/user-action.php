<?php
require_once "../includes/session.php";
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$id = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id <= 0 && $action !== 'add_user') {
    header("Location: manage-users.php");
    exit;
}

switch ($action) {

    case "toggle_role":
        $stmt = $conn->prepare("
            UPDATE users
            SET role = IF(role='admin','user','admin')
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        break;

    case "add_user":
        $name = $_POST['name'] ?? '';
        $surname = $_POST['surname'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        if ($name && $email && $password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, surname, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $surname, $email, $hashed, $role);
            $stmt->execute();
        }
        break;

    case "update_user":
        $name = $_POST['name'] ?? '';
        $surname = $_POST['surname'] ?? '';
        $email = $_POST['email'] ?? '';

        $stmt = $conn->prepare("UPDATE users SET name = ?, surname = ?, email = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $surname, $email, $id);
        $stmt->execute();
        break;

    case "delete":
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        break;
}

header("Location: manage-users.php");
exit;
