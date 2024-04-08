const buttonToGoBack = document.getElementById("go-back");

buttonToGoBack?.addEventListener("click", () => {
  window.history.back();
});

document.querySelectorAll(".toggle-description").forEach(function (element) {
  element.addEventListener("click", function (event) {
    event.preventDefault();
    var fullDescription = this.getAttribute("data-full-description");
    alert(fullDescription);
  });
});
