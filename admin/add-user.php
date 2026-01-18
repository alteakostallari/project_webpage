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
?>

<div class="admin-container">
    <div class="admin-card">
        <h2>Add New User</h2>
        <form action="user-action.php" method="POST" class="details-form">
            <input type="hidden" name="action" value="add_user">

            <label for="name">Name</label>
            <input type="text" name="name" id="name" required>

            <label for="surname">Surname</label>
            <input type="text" name="surname" id="surname" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Password</label>
            <div style="position: relative;">
                <input type="password" name="password" id="password" required style="padding-right: 40px;">
                <span id="togglePassword"
                    style="position: absolute; right: 10px; top: 10px; cursor: pointer; user-select: none;">👁️</span>
            </div>

            <label for="role">Role</label>
            <select name="role" id="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>

            <button type="submit" class="btn-book save-btn">Add User</button>
            <a href="manage-users.php" class="cancel-link">Cancel</a>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#togglePassword").on("click", function () {
            var passwordField = $("#password");
            var type = passwordField.attr("type") === "password" ? "text" : "password";
            passwordField.attr("type", type);
            $(this).text(type === "password" ? "👁️" : "🙈");
        });
    });
</script>