const productListContainer = document.getElementById("productListContainer");
const inputProdutoId = document.getElementById("inputProdutoId");
const inputProduto = document.getElementById("inputProduto");

inputProduto.addEventListener("input", function () {
  const search = inputProduto.value.trim();
  inputProdutoId.value = ""; // Limpa o ID do produto
  inputProduto.style.borderColor = ""; // Remove a cor de erro
  if (search.length === 0) {
    productListContainer.innerHTML = ""; // Limpa o conteúdo
    return;
  }

  fetch(`/produtos/findAllByCodeOrEan?search=${search}`)
    .then((response) => response.json())
    .then((data) => {
      const products = data.products;
      const productListHTML = products
        .map((product) => {
          const { ID, code, importer, description } = product;
          return (
            `
            <article>
              <button type="button" class="btn btn-outline-primary product-button" 
              data-id="${ID}" 
              data-text="${code} - ${importer}"
              aria-label="Product ${code}"> <strong>${code}</strong> - ${importer}` +
            (description ? ` - ${description}` : "") +
            `
              </button>
            </article>`
          );
        })
        .join("");

      productListContainer.innerHTML = productListHTML;

      // Adiciona o evento de clique aos botões
      document.querySelectorAll(".product-button").forEach((button) => {
        button.addEventListener("click", function () {
          inputProduto.value = this.getAttribute("data-text"); // Define o valor visualmente no input
          inputProdutoId.value = this.getAttribute("data-id"); // Define o ID do produto nos dados do input oculto
          productListContainer.innerHTML = "";
        });
      });
    })
    .catch((error) => {
      console.error("Erro ao buscar produtos:", error);
    });
});

// Função para verificar se os valores selecionados nos dois selects são diferentes
function estoquesDiferentes() {
  try {
    var estoqueOrigem = document.getElementById("inputEstoqueOrigem").value;
    var estoqueDestino = document.getElementById("inputEstoqueDestino").value;
  } catch (e) {
    return true;
  }

  if (estoqueOrigem === estoqueDestino) {
    alert(
      "Os estoques de origem e destino não podem ser iguais. Por favor, selecione estoques diferentes."
    );
    return false;
  }

  return true;
}

// Adiciona um listener ao formulário para chamar a função antes de enviar
const formulario = document.querySelector("form");
formulario.addEventListener("submit", function (event) {
  event.preventDefault(); // Impede o envio do formulário

  // Verificar se inputProdutoId está preenchido
  if (!inputProdutoId.value) {
    // Deixar inputProduto com foco, vermelho e exibir mensagem de erro
    inputProduto.focus();
    inputProduto.style.borderColor = "red";
    alert("Selecione um produto válido.");
    return;
  }

  if (!estoquesDiferentes()) {
    try {
      event.target.reset();
    } catch {}
    return;
  }

  formulario.submit();
});
