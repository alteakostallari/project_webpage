<?php
$page_class = "register-bg";
require_once "includes/session.php";
// vetëm përdorues të loguar mund të hyjnë
require_once "includes/header.php";

require_once "db.php";

// Merr të dhënat e përdoruesit nga DB
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, surname, email, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Kontrollo nëse ekziston përdoruesi
if (!$user) {
    echo "User not found.";
    exit;
}
?>

<div class="profile-container">
    <div class="profile-card">
        <h2>Profile Management</h2>

        <div class="avatar-upload">
            <div class="avatar-preview">
                <img src="<?= $user['profile_image'] ? htmlspecialchars($user['profile_image']) : 'https://via.placeholder.com/150' ?>"
                    id="imagePreview" alt="Profile Photo">
            </div>
            <div class="avatar-edit">
                <input type='file' id="imageUpload" name="avatar" accept=".png, .jpg, .jpeg" />
                <label for="imageUpload" id="editAvatarLabel" style="display: none;">Change Profile Picture</label>
            </div>
        </div>

        <form id="updateProfileForm">

            <label>First Name
                <input type="text" id="firstName" name="first_name" value="<?= htmlspecialchars($user['name']) ?>"
                    disabled>
            </label>

            <label>Last Name
                <input type="text" id="lastName" name="last_name" value="<?= htmlspecialchars($user['surname']) ?>"
                    disabled>
            </label>

            <label>Email (Cannot be changed)
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled class="disabled-input">
            </label>

            <button type="button" id="editProfileBtn" class="btn-edit">Edit Profile</button>
            <button type="submit" id="cancelProfileBtn" class="btn-cancel" style="display:none;">Cancel</button>
            <button type="submit" id="saveProfileBtn" class="btn-save" style="display:none;">Save Changes</button>

        </form>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/profile.js?v=<?= time() ?>"></script>