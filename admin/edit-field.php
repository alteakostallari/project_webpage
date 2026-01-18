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
    header("Location: manage-fields.php");
    exit;
}

/* Merr user-in */
$stmt = $conn->prepare("SELECT name, sport_id, type, location, price_per_hour, statusi FROM fushat WHERE id = ?");
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
        <h2>Edit Field</h2>
        <form action="field-action.php" method="POST" class="details-form">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="action" value="update_field">

            <label for="name">Name</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label for="sport_id">Sport</label>
            <input type="text" name="sport_id" id="sport_id" value="<?= htmlspecialchars($user['sport_id']) ?>"
                required>

            <label for="type">Type</label>
            <input type="text" name="type" id="type" value="<?= htmlspecialchars($user['type']) ?>" required>

            <label for="location">Location</label>
            <input type="text" name="location" id="location" value="<?= htmlspecialchars($user['location']) ?>"
                required>

            <label for="price_per_hour">Price per hour</label>
            <input type="text" name="price_per_hour" id="price_per_hour"
                value="<?= htmlspecialchars($user['price_per_hour']) ?>" required>

            <label for="statusi">Status</label>
            <input type="text" name="statusi" id="statusi" value="<?= htmlspecialchars($user['statusi']) ?>" required>

            <button type="submit" class="btn-book save-btn">Save Changes</button>
            <a href="manage-fields.php" class="cancel-link">Cancel</a>
        </form>
    </div>
</div>