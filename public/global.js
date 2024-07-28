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

// No safari não existe input do tipo month, entao precisamos fazer um input que
// simule o comportamento do input do tipo month

// Adiciona o evento de click no input
document.querySelectorAll("input[type=month]").forEach(function (element) {
  // Mudar texto do input para o mes e ano
  const date = new Date(element.value);
  const monthName = new Intl.DateTimeFormat("pt-BR", { month: "long" }).format(
    date
  );
  element.value = `${monthName} de ${date.getFullYear()}`;

  element.addEventListener("click", function () {
    // Eh preciso que ao clicar apareça o calendario onde o usuário
    // possa escolher o mes e o ano
    const date = new Date();

    // Cria o calendario
    const calendar = document.createElement("div");
    calendar.className = "calendar";
    calendar.style.position = "absolute";
    calendar.style.zIndex = "1000";
    calendar.style.backgroundColor = "white";
    calendar.style.border = "1px solid black";
    calendar.style.padding = "10px";
    calendar.style.borderRadius = "5px";
    calendar.style.boxShadow = "0 0 5px 0 rgba(0, 0, 0, 0.1)";

    // Cria o input do mes em formato de select de janeiro a dezembro
    const monthInput = document.createElement("select");
    monthInput.style.marginRight = "5px";
    calendar.appendChild(monthInput);

    // Adiciona os options no select
    for (let i = 0; i < 12; i++) {
      const option = document.createElement("option");
      option.value = i + 1;
      option.textContent = new Intl.DateTimeFormat("pt-BR", {
        month: "long",
      }).format(new Date(2020, i));
      monthInput.appendChild(option);
    }

    // Seleciona o mes atual
    monthInput.value = date.getMonth() + 1;

    // Cria o input do ano
    const yearInput = document.createElement("input");
    yearInput.type = "number";
    yearInput.min = "1900";
    yearInput.max = "2100";
    yearInput.value = date.getFullYear();
    yearInput.style.width = "70px";
    yearInput.style.marginRight = "5px";
    calendar.appendChild(yearInput);

    // Cria o botao de confirmar
    const confirmButton = document.createElement("button");
    confirmButton.textContent = "Confirmar";
    confirmButton.style.marginTop = "5px";
    calendar.appendChild(confirmButton);

    // Adiciona o calendario no body
    document.body.appendChild(calendar);

    // Adiciona o evento de click no botao de confirmar
    confirmButton.addEventListener("click", function () {
      const month = monthInput.value.padStart(2, "0");
      const year = yearInput.value;
      // element.value = `${year}-${month}`;
      // Apresentar mes e ano em PT-BR formato de texto
      // Exemploi: "Janeiro de 2021"
      const monthName = new Intl.DateTimeFormat("pt-BR", {
        month: "long",
      }).format(new Date(year, month - 1));
      element.value = `${monthName} de ${year}`;
      calendar.remove();
    });

    // Adiciona o evento de click fora do calendario
    document.addEventListener("click", function (event) {
      if (!calendar.contains(event.target) && event.target !== element) {
        calendar.remove();
      }
    });

    // Posiciona o calendario
    const rect = element.getBoundingClientRect();
    calendar.style.left = rect.left + "px";
    calendar.style.top = rect.bottom + window.scrollY + "px";

    // Adiciona o evento de click no input
    monthInput.addEventListener("click", function (event) {
      event.stopPropagation();
    });

    // Adiciona o evento de click no input
    yearInput.addEventListener("click", function (event) {
      event.stopPropagation();
    });

    // Adiciona o evento de click no calendario
    calendar.addEventListener("click", function (event) {
      event.stopPropagation();
    });

    // Adiciona o evento de click no input
    confirmButton.addEventListener("click", function (event) {
      event.stopPropagation();
    });
  });
});
