<?php
$page_class = "register-bg";
require_once "../includes/session.php";
require_once "../includes/header.php";
require_once "../db.php";


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}


$result = $conn->query("
    SELECT id, name, sport_id, type, location, price_per_hour, statusi
    FROM fushat
    ORDER BY id DESC
");

$fields = [];
while ($row = $result->fetch_assoc()) {
    $fields[] = $row;
}
?>

<div class="users-wrapper">
    <h2>Field Management</h2>
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="add-field.php" class="btn btn-role" style="text-decoration: none;">+ Add Field</a>
    </div>

    <table class="users-table">
        <tr>
            <th>Name</th>
            <th>Sport</th>
            <th>Type</th>
            <th>Location</th>
            <th>Price (€)</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($fields as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['name']) ?></td>
                <td><?= htmlspecialchars($f['sport_id']) ?></td>
                <td><?= htmlspecialchars($f['type']) ?></td>
                <td><?= htmlspecialchars($f['location']) ?></td>
                <td><?= number_format($f['price_per_hour'], 2) ?></td>

                <td>
                    <?php if ($f['statusi'] === 'inactive'): ?>
                        <span class="status-blocked">Inactive</span>
                    <?php else: ?>
                        <span class="status-active">Active</span>
                    <?php endif; ?>
                </td>

                <td class="actions">

                    <!-- TOGGLE STATUS -->
                    <form method="post" action="field-action.php" class="action-form">
                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                        <input type="hidden" name="action" value="toggle_status">
                        <button class="btn btn-role">Status</button>
                    </form>

                    <!-- EDIT -->
                    <a href="edit-field.php?id=<?= $f['id'] ?>" class="btn btn-role" style="text-decoration:none;">
                        Edit
                    </a>

                    <!-- DELETE -->
                    <form method="post" action="field-action.php" class="action-form"
                        onsubmit="return confirm('Delete this field?');">
                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="btn btn-delete">Delete</button>
                    </form>

                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>