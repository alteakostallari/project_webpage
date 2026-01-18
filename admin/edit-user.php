<?php
$page_class = "register-bg";
require_once "../includes/session.php";
require_once "../includes/header.php";
require_once "../db.php";

/* Vetëm admin */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: manage-users.php");
    exit;
}

/* Merr user-in */
$stmt = $conn->prepare("SELECT name, surname, email FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<p style='text-align:center; color:white; margin-top:50px;'>User not found.</p>";
    require_once "../includes/footer.php";
    exit;
}
?>

<div class="admin-container">
    <div class="admin-card">
        <h2>Edit User</h2>
        <form action="user-action.php" method="POST" class="details-form">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="action" value="update_user">

            <label for="name">Name</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label for="surname">Surname</label>
            <input type="text" name="surname" id="surname" value="<?= htmlspecialchars($user['surname']) ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <button type="submit" class="btn-book save-btn">Save Changes</button>
            <a href="manage-users.php" class="cancel-link">Cancel</a>
        </form>
    </div>
</div>