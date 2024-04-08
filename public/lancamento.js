const productListContainer = document.getElementById("productListContainer");
const inputProdutoId = document.getElementById("inputProdutoId");
const inputProduto = document.getElementById("inputProduto");
inputProduto.addEventListener("input", function () {
  const search = inputProduto.value.trim();

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
          return `<div>
          <button type="button" class="btn btn-outline-primary product-button" data-id="${product.ID}" data-code="${product.code}">
            ${product.code} ${product.importer} - ${product.name}
          </button>
        </div>`;
        })
        .join("");

      productListContainer.innerHTML = productListHTML;

      // Adiciona o evento de clique aos botões
      document.querySelectorAll(".product-button").forEach((button) => {
        button.addEventListener("click", function () {
          inputProduto.value = this.getAttribute("data-code"); // Define o valor visualmente no input
          inputProdutoId.value = this.getAttribute("data-id"); // Define o ID do produto nos dados do input oculto
          productListContainer.innerHTML = "";
        });
      });
    })
    .catch((error) => {
      console.error("Erro ao buscar produtos:", error);
    });
});
