<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['reset_code'])) {
    header('Location: forgot_pass.php');
    exit;
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } else {
        $userId = $_SESSION['reset_user_id'];
        $code = $_SESSION['reset_code'];

        // 1. Verify token one last time for safety
        $stmt = $conn->prepare("SELECT id FROM password_resets WHERE user_id = ? AND reset_token = ? AND expires_at > NOW()");
        $stmt->bind_param("is", $userId, $code);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            // 2. Update password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->bind_param("si", $hashedPassword, $userId);

            if ($upd->execute()) {
                // 3. Clear reset tokens
                $del = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
                $del->bind_param("i", $userId);
                $del->execute();

                // 4. Success
                $success = true;
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_code']);
            } else {
                $message = "Error while saving password.";
            }
        } else {
            $message = "Session expired. Please try again.";
        }
    }
}

$page_class = "register-bg";
require_once "includes/header.php";
?>

<div class="login-container">
    <div class="login-box">
        <h2>New Password</h2>
        <p>Please enter your new password below.</p>

        <?php if ($message): ?>
            <div class="alert alert-danger"
                style="color: #f87171; margin-bottom: 15px; background: rgba(248, 113, 113, 0.1); padding: 10px; border-radius: 8px;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"
                style="color: #4ade80; margin-bottom: 15px; background: rgba(74, 222, 128, 0.1); padding: 10px; border-radius: 8px;">
                Password has been changed successfully! <br>
                <a href="login.php" style="color: #4ade80; text-decoration: underline;">Click here to login.</a>
            </div>
        <?php else: ?>
            <form method="post" class="login-form">
                <div class="input-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required autofocus>
                </div>

                <div class="input-group">
                    <label for="confirm">Confirm Password</label>
                    <input type="password" id="confirm" name="confirm" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn">Update Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>