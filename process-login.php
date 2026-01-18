<?php
require_once "db.php";
require_once "includes/logger.php";
session_start();

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// ================= VALIDIM BACKEND =================
if ($email === "" || $password === "") {
    logLogin($conn, $email, "FAILED", "Empty email or password");
    $_SESSION['login_error'] = "Email and password cannot be empty.";
    header("Location: login.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    logLogin($conn, $email, "FAILED", "Invalid email format");
    $_SESSION['login_error'] = "Invalid email format.";
    header("Location: login.php");
    exit;
}

// ================= KERKIMI NE DB =================
$sql = "SELECT id, email, password, role, failed_attempts, lock_until, is_verified
        FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ================= USER NUK EKZISTON =================
if (!$user) {
    logLogin($conn, $email, "FAILED", "Email not found");
    $_SESSION['login_error'] = "No user found with this email.";
    header("Location: login.php");
    exit;
}


// ================= KONTROLL BLOKIMI =================
if (!empty($user['lock_until']) && strtotime($user['lock_until']) > time()) {
    logLogin($conn, $email, "BLOCKED", "Account temporarily locked");
    $_SESSION['login_error'] = "Account has been blocked for 30 minutes. Please try again later.";
    header("Location: login.php");
    exit;
}

// ================= PASSWORD I GABUAR =================
if (!password_verify($password, $user['password'])) {
    $failed = $user['failed_attempts'] + 1;
    $lockUntil = null;

    if ($failed >= 7) {
        $lockUntil = date("Y-m-d H:i:s", strtotime("+30 minutes"));
    }

    $update = $conn->prepare("UPDATE users SET failed_attempts = ?, lock_until = ? WHERE id = ?");
    $update->bind_param("isi", $failed, $lockUntil, $user['id']);
    $update->execute();

    logLogin($conn, $email, "FAILED", "Wrong password ($failed/7)");
    $_SESSION['login_error'] = "Incorrect password. Attempts: $failed/7";
    header("Location: login.php");
    exit;
}

// ================= LOGIN I SUKSESSHËM =================

// reset tentativat
$reset = $conn->prepare(
    "UPDATE users SET failed_attempts = 0, lock_until = NULL WHERE id = ?"
);
$reset->bind_param("i", $user['id']);
$reset->execute();

// session
$_SESSION['user_id'] = $user['id'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$_SESSION['last_activity'] = time();

logLogin($conn, $email, "SUCCESS", "Login successful");

// ================= REMEMBER ME  =================
if (!empty($_POST["remember"])) {
    // Set cookies for 30 days
    setcookie("member_login", $email, time() + (30 * 24 * 60 * 60));
    setcookie("member_password", $password, time() + (30 * 24 * 60 * 60));
} else {
    // Clear cookies if not checked
    if (isset($_COOKIE["member_login"])) {
        setcookie("member_login", "", time() - 3600);
    }
    if (isset($_COOKIE["member_password"])) {
        setcookie("member_password", "", time() - 3600);
    }
}

// redirect sipas rolit
if ($user['role'] === 'admin') {
    header("Location: admin/manage-users.php");
} else {
    header("Location: profile.php");
}
exit;
