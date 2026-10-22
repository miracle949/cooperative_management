const loginButton = document.getElementById("loginButton");
const loading_screen = document.querySelector(".loading-screen");
const load = document.querySelector(".load");

loginButton.addEventListener("click", () => {

    loading_screen.style.display = "block";

    setTimeout(() => {
        loading_screen.style.display = "none";
    }, 3000);
});