$(document).ready(function () {
    // 1. Create variables to hold original values for the "Cancel" function
    let originalFirst, originalLast, originalAvatar;

    // Butoni Edit Profile
    $("#editProfileBtn").click(function () {
        // Save current values before editing
        originalFirst = $("#firstName").val();
        originalLast = $("#lastName").val();
        originalAvatar = $("#imagePreview").attr("src");

        $("#firstName, #lastName, #imageUpload").prop("disabled", false);
        $("#saveProfileBtn, #cancelProfileBtn").show();
        $("#editAvatarLabel").show(); // Show the upload button
        $(this).hide();
    });

    // Butoni Cancel
    $("#cancelProfileBtn").click(function (e) {
        e.preventDefault(); // Prevent form submission on cancel

        // Restore original values
        $("#firstName").val(originalFirst);
        $("#lastName").val(originalLast);
        $("#imagePreview").attr("src", originalAvatar);
        $("#imageUpload").val(''); // Clear the file input

        // Reset UI
        $("#firstName, #lastName, #imageUpload").prop("disabled", true);
        $("#saveProfileBtn, #cancelProfileBtn").hide();
        $("#editAvatarLabel").hide(); // Hide the upload button
        $("#editProfileBtn").show();
    });

    // Preview i imazhit
    $("#imageUpload").change(function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $("#imagePreview").attr("src", e.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    // Submit form
    $("#updateProfileForm").on("submit", function (e) {
        e.preventDefault();

        const formData = new FormData();
        formData.append("first_name", $("#firstName").val());
        formData.append("last_name", $("#lastName").val());

        const fileInput = $("#imageUpload")[0];
        if (fileInput.files.length > 0) {
            formData.append("avatar", fileInput.files[0]);
        }

        $.ajax({
            url: "process-profile.php", // Ensure this filename matches your PHP file
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    alert("Profile updated successfully!");
                    location.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText); // Log the actual error to console
                alert("Server error, try again.");
            }
        });
    });
});