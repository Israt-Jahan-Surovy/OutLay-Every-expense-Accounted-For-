document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("loginForm");

    if (form)
    {
        form.addEventListener("submit", function (event) {

            const email = document.getElementById("user_email").value.trim();
            const password = document.getElementById("user_password").value;

            if (email === "" || password === "")
            {
                event.preventDefault();
                alert("Please enter email and password.");
            }

        });
    }

});
