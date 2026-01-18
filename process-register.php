<?php
require_once "db.php";
require_once "mail.php";
session_start();

// Merr inputet
$firstName = htmlspecialchars($_POST['firstName'] ?? '');
$lastName = htmlspecialchars($_POST['lastName'] ?? '');
$email = htmlspecialchars($_POST['regEmail'] ?? '');
$password = $_POST['regPass'] ?? '';
$confirm = $_POST['confirmPass'] ?? '';

$errors = [];

// Validimi i emrit dhe mbiemrit
if (!preg_match("/^[a-zA-Z]{3,40}$/", $firstName)) {
    $errors[] = "First name must be 3-40 letters only.";
}
if (!preg_match("/^[a-zA-Z]{3,40}$/", $lastName)) {
    $errors[] = "Last name must be 3-40 letters only.";
}

// Validimi i email
if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email)) {
    $errors[] = "Invalid email format.";
}

// Validimi i password
$password_regex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%?&])[A-Za-z\d@$!%?&]{8,}$/";
if (empty($password)) {
    $errors[] = "Password cannot be empty.";
} elseif (!preg_match($password_regex, $password)) {
    $errors[] = "Password must be at least 8 characters long, include 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.";
}

// Kontrolli i confirm password
if ($password !== $confirm) {
    $errors[] = "Passwords do not match.";
}

// Kontrollo në DB nëse email ekziston (tek përdoruesit aktivë)
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $errors[] = "Email is already registered";
}

// Nëse ka gabime, i shfaq dhe ndalon ekzekutimin
if (!empty($errors)) {
    echo json_encode([
        "status" => 400,
        "message" => implode(", ", $errors)
    ]);
    exit;
}

// Hash i fjalëkalimit
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 1. Generate verification code
$verification_code = random_int(100000, 999999);

// 2. STORE DATA IN SESSION (Not database yet!)
$_SESSION['pending_registration'] = [
    'first_name' => $firstName,
    'last_name' => $lastName,
    'email' => $email,
    'password' => $hashed_password,
    'code' => $verification_code,
    'expiry' => time() + 3600 // 1 hour
];

// 3. Send verification email
if (sendEmail($email, $verification_code, 'verify')) {
    echo json_encode([
        "status" => "success",
        "message" => "Registration details saved! Please check your email for the verification code.",
        "redirect" => "verify.php?email=" . urlencode($email) . "&type=registration"
    ]);
} else {
    // If email fails, we might still want to let them know
    echo json_encode([
        "status" => "error",
        "message" => "Failed to send verification email. Please try again."
    ]);
}
exit;
