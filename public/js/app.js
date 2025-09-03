document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("toggleBtn");
    const sidebar = document.getElementById("sidebar");

    toggleBtn.addEventListener("click", function () {
        if (window.innerWidth <= 768) {
            sidebar.classList.toggle("show"); // mobile slide in/out
        } else {
            sidebar.classList.toggle("collapsed"); // desktop shrink
        }
    });
});
