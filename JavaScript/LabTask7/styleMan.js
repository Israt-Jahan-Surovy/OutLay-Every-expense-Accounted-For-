const ch = document.getElementById("ch");
const para = document.getElementById("para");

ch.addEventListener("click", function () {

    para.style.color = "red";
});



const incr = document.getElementById("incr");
let currentFontSize = 20;
incr.addEventListener("click", function () {
    currentFontSize += 4;
    para.style.fontSize = currentFontSize + "px";
});


const cen = document.getElementById("cen");
cen.addEventListener("click", function () {

    para.style.textAlign = "center";
});


const reset = document.getElementById("reset");
reset.addEventListener("click", function () {

    para.style.color = "black";
    para.style.fontSize = "20px";
    para.style.textAlign = "left";
    currentFontSize = 20;
});