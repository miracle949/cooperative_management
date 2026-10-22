const imageFile = document.querySelector('#inputImage');
const inputBox = document.querySelector('#inputBox');
// const exit = document.querySelector("#exit");

inputBox.addEventListener('change', function () {

    imageFile.style.display = "block";
    // exit.style.display = "flex";
    document.getElementById("text").style.display = "none";
    document.getElementById("text2").style.display = "none";
    imageFile.src = window.URL.createObjectURL(this.files[0]);

});

// exit.addEventListener("click", () => {
//     imageFile.style.display = "none";
//     exit.style.display = "none";
//     document.getElementById("text").style.display = "flex";
//     document.getElementById("text2").style.display = "flex";
// });