<?php
$page_class = "register-bg";
session_start();
require_once "includes/header.php";

// Get error message from session if exists
$error_message = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']); // Clear it after displaying

// Get success message from session if exists
$success_message = $_SESSION['login_success'] ?? '';
unset($_SESSION['login_success']); // Clear it after displaying

// Check for cookies
$cookie_email = $_COOKIE['member_login'] ?? '';
$cookie_password = $_COOKIE['member_password'] ?? '';
$remember_checked = !empty($cookie_email) ? 'checked' : '';
?>

<div class="login-container">
    <h2>Log In</h2>

    <form id="loginForm" method="post" action="process-login.php" novalidate>
        <div class="form-group">
            <label> Email<input type="email" id="email" name="email" placeholder="example@email.com"
                    value="<?= htmlspecialchars($cookie_email) ?>"></label>
            <span id="email_message" class="text-danger"></span>
        </div>
        <div class="form-group" style="position: relative;">
            <label>Password<input type="password" name="password" id="password" style="padding-right: 40px;"
                    value="<?= htmlspecialchars($cookie_password) ?>"></label>
            <span id="togglePassword"
                style="position: absolute; right: 10px; top: 32px; cursor: pointer; user-select: none;">👁️</span>
            <span id="password_message" class="text-danger"></span>
        </div>


        <?php if ($error_message): ?>
            <div style="text-align: center; margin: 10px 0;">
                <span class="text-danger" style="font-weight: bold;"><?= htmlspecialchars($error_message) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div style="text-align: center; margin: 10px 0;">
                <span style="color: #1ab394; font-weight: bold;"><?= htmlspecialchars($success_message) ?></span>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label style="display:inline-flex; align-items:center;">
                <input type="checkbox" name="remember" style="width: auto; margin-right: 10px;" <?= $remember_checked ?>>
                Remember Me
            </label>
        </div>

        <a href="forgot_pass.php">Forgot your password?</a>
        <button type="submit" style="margin-top: 20px;">Log In</button>
        <a href="register.php">Don't have an account? Sign up.</a>
    </form>
</div>

<?php require_once "includes/footer.php"; ?>

<script>
    document.getElementById("loginForm").addEventListener("submit", function (e) {

        var email = $("#email").val().trim();
        var password = $("#password").val().trim();
        var email_regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        var error = 0;


        $(".text-danger").text("");
        $("input").removeClass("border-danger");

        // Validimi i Email-it (Pika 18: Email-i duhet të jetë unik, por në front-end kontrollojmë vetëm formatin)
        if (email === "") {
            $("#email").addClass("border-danger");
            $("#email_message").text("Email cannot be empty.");
            error++;
        } else if (!email_regex.test(email)) {
            $("#email").addClass("border-danger");
            $("#email_message").text("Invalid email format.");
            error++;
        }

        // Validimi i Fjalëkalimit
        if (password === "") {
            $("#password").addClass("border-danger");
            $("#password_message").text("Password cannot be empty.");
            error++;
        } else if (password.length < 8) {
            $("#password").addClass("border-danger");
            $("#password_message").text("Password must be at least 8 characters.");
            error++;
        }

        // If everything is valid
        if (error > 0) {
            e.preventDefault();
        }
    });

    $(document).ready(function () {
        // Toggle password visibility
        $("#togglePassword").on("click", function () {
            var passwordField = $("#password");
            var type = passwordField.attr("type") === "password" ? "text" : "password";
            passwordField.attr("type", type);
            $(this).text(type === "password" ? "👁️" : "🙈");
        });

        // Kur përdoruesi shkruan te Email-i
        $("#email").on("input", function () {
            $(this).removeClass("border-danger");
            $("#email_message").text("");
        });

        // Kur përdoruesi shkruan te Password-i
        $("#password").on("input", function () {
            $(this).removeClass("border-danger");
            $("#password_message").text("");
        });
    });
</script>