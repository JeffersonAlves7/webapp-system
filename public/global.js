// Botao de voltar
const buttonToGoBack = document.getElementById("go-back");
buttonToGoBack?.addEventListener("click", () => {
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

document.getElementById('user-info').addEventListener('click', function() {
  document.getElementById('logout-button').style.display = 'block';
});
