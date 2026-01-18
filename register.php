<?php
$page_class = "register-bg";
require_once "includes/header.php";
?>

<div class="reg-container">
    <h2>Create Account</h2>

    <form id="registerForm" method="post" action="process-register.php" novalidate>
        <div class="form-group">
            <label>First Name<input type="text" id="firstName" name="firstName"
                    placeholder="Enter your first name"></label>
            <span id="name_message" class="text-danger"></span>
        </div>

        <div class="form-group">
            <label>Last Name<input type="text" id="lastName" name="lastName" placeholder="Enter your last name"></label>
            <span id="surname_message" class="text-danger"></span>
        </div>

        <div class="form_group">
            <label> Email<input type="email" id="regEmail" name="regEmail" placeholder="example@email.com"></label>
            <span id="reg_email_message" class="text-danger"></span>
        </div>

        <div class="form_group" style="position: relative;">
            <label>Password<input type="password" id="regPass" name="regPass" style="padding-right: 40px;"> </label>
            <span id="toggleRegPass"
                style="position: absolute; right: 10px; top: 32px; cursor: pointer; user-select: none;">👁️</span>
            <span id="reg_pass_message" class="text-danger"></span>
        </div>

        <div class="form_group" style="position: relative;">
            <label>Confirm Password<input type="password" id="confirmPass" name="confirmPass"
                    style="padding-right: 40px;"></label>
            <span id="toggleConfirmPass"
                style="position: absolute; right: 10px; top: 32px; cursor: pointer; user-select: none;">👁️</span>
            <span id="confirm_pass_message" class="text-danger"></span>
        </div>

        <button type="submit" style="margin-top: 20px;">Create Account</button>
        <label>Already have an account? <a href="login.php">Login here.</a></label>
    </form>
</div>

<?php require_once "includes/footer.php"; ?>

<script>
    $(document).ready(function () {
        $("#registerForm").on("submit", function (e) {

            var firstName = $("#firstName").val().trim();
            var lastName = $("#lastName").val().trim();
            var email = $("#regEmail").val().trim();
            var password = $("#regPass").val();
            var confirm = $("#confirmPass").val();

            var email_regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            var error = 0;


            $(".text-danger").text("");
            $("input").removeClass("border-danger");

            // Validimi i Emrit
            if (firstName === "") {
                $("#firstName").addClass("border-danger");
                $("#name_message").text("First name cannot be empty.");
                error++;
            }

            // Validimi i Mbiemrit
            if (lastName === "") {
                $("#lastName").addClass("border-danger");
                $("#surname_message").text("Last name cannot be empty.");
                error++;
            }

            // Validimi i Email-it
            if (email === "") {
                $("#regEmail").addClass("border-danger");
                $("#reg_email_message").text("Email cannot be empty.");
                error++;
            } else if (!email_regex.test(email)) {
                $("#regEmail").addClass("border-danger");
                $("#reg_email_message").text("Invalid email format.");
                error++;
            }


            if (password === "") {
                $("#regPass").addClass("border-danger");
                $("#reg_pass_message").text("Password cannot be empty.");
                error++;
            } else if (password.length < 8) {
                $("#regPass").addClass("border-danger");
                $("#reg_pass_message").text("Password must be at least 8 characters.");
                error++;
            }


            if (password !== confirm) {
                $("#confirmPass").addClass("border-danger");
                $("#confirm_pass_message").text("Passwords do not match");
                error++;
            }

            // Finalizimi
            if (error === 0) {
                e.preventDefault(); // Stop standard form submission
                $.ajax({
                    url: 'process-register.php',
                    type: 'POST',
                    data: {
                        firstName: firstName,
                        lastName: lastName,
                        regEmail: email,
                        regPass: password,
                        confirmPass: confirm
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success' || response.status === 200) {
                            alert(response.message);
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                window.location.href = "login.php";
                            }
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function () {
                        alert("An error occurred during registration. Please try again.");
                    }
                });
            }
        });

        // Password visibility toggles
        $("#toggleRegPass").on("click", function () {
            var passwordField = $("#regPass");
            var type = passwordField.attr("type") === "password" ? "text" : "password";
            passwordField.attr("type", type);
            $(this).text(type === "password" ? "👁️" : "🙈");
        });

        $("#toggleConfirmPass").on("click", function () {
            var passwordField = $("#confirmPass");
            var type = passwordField.attr("type") === "password" ? "text" : "password";
            passwordField.attr("type", type);
            $(this).text(type === "password" ? "👁️" : "🙈");
        });

        // 2. Real-time Reset
        $("input").on("input", function () {
            $(this).removeClass("border-danger");
            // Find the span that follows the input or inside the parent form-group
            $(this).closest(".form-group").find(".text-danger").text("");
        });
    });
</script>