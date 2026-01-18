$(document).ready(function () {

    const currentPage = window.location.pathname.split("/").pop();

    $(".menu a").each(function () {
        if ($(this).attr("href") === currentPage) {
            $(this).css("text-decoration", "underline");
        }
    });

});

document.addEventListener("DOMContentLoaded", function () {

    const loggedIn = localStorage.getItem("loggedIn");

    const guestItems = document.querySelectorAll(".guest-only");
    const userItems = document.querySelectorAll(".user-only");

    if (loggedIn === "true") {
        guestItems.forEach(item => item.style.display = "none");
        userItems.forEach(item => item.style.display = "block");
    } else {
        guestItems.forEach(item => item.style.display = "block");
        userItems.forEach(item => item.style.display = "none");
    }

    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", function () {
            localStorage.removeItem("loggedIn");
            window.location.href = "index.html";
        });
    }

});

