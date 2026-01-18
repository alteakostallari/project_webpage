$(document).ready(function () {

    $("form").on("submit", function (e) {
        let valid = true;

        $(this).find("input[required]").each(function () {
            if ($(this).val().trim() === "") {
                valid = false;
                $(this).css("border", "2px solid red");
            } else {
                $(this).css("border", "none");
            }
        });

        if (!valid) {
            e.preventDefault();
            alert("Please fill all required fields");
        }
    });

});
