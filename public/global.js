const buttonToGoBack = document.getElementById("go-back");

buttonToGoBack?.addEventListener("click", () => {
  window.history.back();
});
