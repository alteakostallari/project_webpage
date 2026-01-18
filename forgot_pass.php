<?php
session_start();
require_once "db.php";
require_once "mail.php";

$page_class = "register-bg";
require_once "includes/header.php";
?>

<div class="login-container">
    <div class="login-box">
        <h2>Reset Your Password</h2>
        <p>Enter your email and we will send a code to reset your password.</p>

        <div id="forgot_message"></div>

        <form id="forgotForm" novalidate>
            <div class="input-group">
                <label for="forgotEmail">Email Address</label>
                <input type="email" id="forgotEmail" name="email" placeholder="example@email.com" required>
                <div id="forgot_email_message" class="error-text"></div>
            </div>
            <button type="submit" class="btn" style="margin-top: 20px;">Send Reset Code</button>
        </form>

        <div class="login-footer">
            <p>Remembered your password? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $("#forgotForm").on("submit", function (e) {
            e.preventDefault();

            var email = $("#forgotEmail").val().trim();
            var email_regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            var error = 0;

            // Reset errors
            $("#forgot_email_message").text("");
            $("#forgotEmail").removeClass("border-danger");
            $("#forgot_message").html("");

            // Validation
            if (email === "") {
                $("#forgotEmail").addClass("border-danger");
                $("#forgot_email_message").text("Please enter your email.");
                error++;
            } else if (!email_regex.test(email)) {
                $("#forgotEmail").addClass("border-danger");
                $("#forgot_email_message").text("This is not a valid email.");
                error++;
            }

            if (error === 0) {
                // Submit the form via AJAX
                $.ajax({
                    url: 'process-forgot-request.php',
                    type: 'POST',
                    data: { email: email },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            $("#forgot_message").html('<div class="alert alert-success" style="color: #4ade80; margin-bottom: 15px; background: rgba(74, 222, 128, 0.1); padding: 10px; border-radius: 8px;">' + response.message + '</div>');
                            setTimeout(function () {
                                window.location.href = 'verify.php?email=' + encodeURIComponent(email);
                            }, 2000);
                        } else {
                            $("#forgot_message").html('<div class="alert alert-danger" style="color: #f87171; margin-bottom: 15px; background: rgba(248, 113, 113, 0.1); padding: 10px; border-radius: 8px;">' + response.message + '</div>');
                        }
                    },
                    error: function () {
                        $("#forgot_message").html('<div class="alert alert-danger" style="color: #f87171; margin-bottom: 15px;">An error occurred. Please try again.</div>');
                    }
                });
            }
        });

        $("#forgotEmail").on("input", function () {
            $(this).removeClass("border-danger");
            $("#forgot_email_message").text("");
        });
    });
</script>

<?php require_once "includes/footer.php"; ?>