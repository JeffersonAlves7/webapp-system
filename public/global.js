// Botao de voltar
const buttonToGoBack = document.getElementById("go-back");
buttonToGoBack?.addEventListener("click", () => {
  // Se o elemento tiver um data-url, redireciona para ele
  const dataUrl = buttonToGoBack.getAttribute("data-url");
  if (dataUrl) {
    window.location = dataUrl;
    return;
  }

  // As vezes acontece de o historico voltar para a mesma pagina
  // Para evitar isso, verificamos se a pagina anterior é a mesma
  if (window.history.length > 1) {
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

// Verifica se o navegador é o safari
const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

// Se for safari, adiciona o evento de keydown para os inputs do tipo search
if (isSafari) {
  console.log("Safari");
  document.querySelectorAll("input[type=search]").forEach(function (element) {
    element.addEventListener("keydown", function (event) {
      // Se a tecla pressionada for o enter, previne o evento padrao
      if (event.key === "Enter") {
        event.preventDefault();

        // Se o input estiver dentro de um form, submete o form
        const form = element.closest("form");
        if (form) {
          form.submit();
        }

        return;
      }

      // Se a tecla pressionada for o esc, limpa o input
      if (event.key === "Escape") {
        element.value = "";
      }
    });
  });
}
