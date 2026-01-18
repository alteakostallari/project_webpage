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
        <h2>Add New Field</h2>
        <form action="field-action.php" method="POST" class="details-form">
            <input type="hidden" name="action" value="add_field">

            <label for="name">Name</label>
            <input type="text" name="name" id="name" required>

            <label for="sport_id">Sport</label>
            <input type="text" name="sport_id" id="sport_id" required>

            <label for="type">Type</label>
            <input type="text" name="type" id="type" required>

            <label for="location">Location</label>
            <input type="text" name="location" id="location" required>

            <label for="price_per_hour">Price per hour</label>
            <input type="text" name="price_per_hour" id="price_per_hour" required>

            <label for="statusi">Status</label>
            <select name="statusi" id="statusi">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <button type="submit" class="btn-book save-btn">Add Field</button>
            <a href="manage-fields.php" class="cancel-link">Cancel</a>
        </form>
    </div>
</div>