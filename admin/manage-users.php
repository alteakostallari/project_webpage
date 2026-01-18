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

/* Merr user-at */
$result = $conn->query("
    SELECT id, name, surname, email, role, failed_attempts, lock_until
    FROM users
    ORDER BY id DESC
");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>


<div class="users-wrapper">
    <h2>User Management</h2>
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="add-user.php" class="btn btn-role" style="text-decoration: none;">+ Add User</a>
    </div>

    <table class="users-table">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['name'] . " " . $u['surname']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>

                <td>
                    <?php if (!empty($u['lock_until']) && strtotime($u['lock_until']) > time()): ?>
                        <span class="status-blocked">Blocked</span>
                    <?php else: ?>
                        <span class="status-active">Active</span>
                    <?php endif; ?>
                </td>

                <td class="actions">

                    <!-- CHANGE ROLE -->
                    <form method="post" action="user-action.php" class="action-form">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="toggle_role">
                        <button class="btn btn-role">Role</button>
                    </form>

                    <!-- EDIT -->
                    <a href="edit-user.php?id=<?= $u['id'] ?>" class="btn btn-role"
                        style="text-decoration: none; display: inline-block;">Edit</a>

                    <!-- DELETE -->
                    <form method="post" action="user-action.php" class="action-form"
                        onsubmit="return confirm('Are you sure you want to delete this user?');">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="btn btn-delete">Delete</button>
                    </form>

                </td>
            </tr>
        <?php endforeach; ?>

    </table>
</div>