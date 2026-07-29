const button = document.getElementById("analyze");

button.addEventListener("click", function () {



    let text2 = document.getElementById("t1");
    let text3 = document.getElementById("t2");
    let text4 = document.getElementById("t3");



    const text = document.getElementById("textanalysis").value;
    text3.innerHTML = ("Total characters:" + text.length);
    const trimmed = text.trim();
    text2.innerHTML = ("Total words:" + (trimmed === "" ? 0 : trimmed.split(/\s+/).length));
    text4.innerHTML = ("Reversed text:" + text.split("").reverse().join(""));

});