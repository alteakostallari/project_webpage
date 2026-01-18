<?php
session_start();
require_once "db.php";

$message = '';
$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $type = $_GET['type'] ?? 'reset';

    if (empty($email) || empty($code)) {
        $message = "Please enter the verification code.";
    } else {
        if ($type === 'registration') {
            // Handle Account Verification (From Session)
            if (isset($_SESSION['pending_registration']) && $_SESSION['pending_registration']['email'] === $email) {
                $pending = $_SESSION['pending_registration'];

                if ($pending['code'] == $code && time() <= $pending['expiry']) {
                    // 1. ALL GOOD! Create the user in the database now
                    $stmt = $conn->prepare("
                        INSERT INTO users (name, surname, email, password, role, is_verified) 
                        VALUES (?, ?, ?, ?, 'user', 1)
                    ");
                    $stmt->bind_param("ssss", $pending['first_name'], $pending['last_name'], $pending['email'], $pending['password']);

                    if ($stmt->execute()) {
                        // 2. Success! Clear the session and redirect
                        unset($_SESSION['pending_registration']);
                        $_SESSION['login_success'] = "Your account has been created and verified successfully! You can now login.";
                        header('Location: login.php');
                        exit;
                    } else {
                        $message = "Error while creating account. Please try again.";
                    }
                } else {
                    $message = "The verification code is incorrect or has expired.";
                }
            } else {
                $message = "No pending registration found for this email.";
            }
        } else {
            // Handle Password Reset (From Database)
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user) {
                $userId = $user['id'];
                $stmt = $conn->prepare("SELECT id FROM password_resets WHERE user_id = ? AND reset_token = ? AND expires_at > NOW()");
                $stmt->bind_param("is", $userId, $code);
                $stmt->execute();

                if ($stmt->get_result()->num_rows > 0) {
                    $_SESSION['reset_user_id'] = $userId;
                    $_SESSION['reset_code'] = $code;
                    header('Location: update-password.php');
                    exit;
                } else {
                    $message = "The verification code is incorrect or has expired.";
                }
            } else {
                $message = "Incorrect email.";
            }
        }
    }
}

$page_class = "register-bg";
require_once "includes/header.php";
?>

<div class="login-container">
    <div class="login-box">
        <h2>Verify Account</h2>
        <p>Please enter the 6-digit code we sent to your email <strong><?= htmlspecialchars($email) ?></strong>
        </p>

        <?php if ($message): ?>
            <div class="alert alert-danger"
                style="color: #f87171; margin-bottom: 15px; background: rgba(248, 113, 113, 0.1); padding: 10px; border-radius: 8px;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

            <div class="input-group">
                <label for="code">Verification Code</label>
                <input type="text" id="code" name="code" placeholder="123456" maxlength="6" required autofocus>
            </div>

            <button type="submit" class="btn" style="margin-top: 30px;">Verify Code</button>
        </form>

        <div class="login-footer">
            <p><a href="register.php">Back to Registration</a></p>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>