// Botao de voltar
const buttonToGoBack = document.getElementById("go-back");
buttonToGoBack?.addEventListener("click", () => {
  // As vezes acontece de o historico voltar para a mesma pagina
  // Para evitar isso, verificamos se a pagina anterior é a mesma
  if (window.history.length > 1){
    const previousUrl = window.history.state?.url;
    const currentUrl = window.location.href;
    if (previousUrl && previousUrl === currentUrl) {
      window.history.back();
      return;
    }
  }
  window.history.back();
});

// Clicar na descriçao e aparecer na tela
document.querySelectorAll(".toggle-description").forEach(function (element) {
  element.addEventListener("click", function (event) {
    event.preventDefault();
    var fullDescription = this.getAttribute("data-full-description");
    alert(fullDescription);
  });
});

document.getElementById("user-info").addEventListener("click", function () {
  document.getElementById("logout-button").style.display = "block";
});
