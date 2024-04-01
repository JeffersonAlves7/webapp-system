<?php
$pageTitle = "Incluir Lançamento";
ob_start();

require "Components/Header.php";
?>

<div class="container position-relative">
    <h1 class="mt-4 mb-3"><?php echo $pageTitle ?></h1>

    <div class="d-flex gap-3">
        <a href="/lancamento/entrada">
            <button class="btn btn-custom">Entrada</button>
        </a>
        <button class="btn btn-custom active">Saída</button>
        <a href="/lancamento/transferencia">
            <button class="btn btn-custom">Transferência</button>
        </a>
        <a href="/lancamento/devolucao">
            <button class="btn btn-custom">Devolução</button>
        </a>
        <a href="/lancamento/reserva">
            <button class="btn btn-custom">Reservas</button>
        </a>
    </div>

    <?php
    // Verifica se a variável $sucesso está definida e é true
    if (isset($sucesso) && $sucesso) {
    ?>
        <div id="successAlert" class="alert alert-success alert-dismissible fade show fixed-bottom mx-auto my-3" role="alert" style="max-width: 600px;">
            <strong>Sucesso!</strong> As informações foram inseridas com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <script>
            // Fecha o alerta de sucesso automaticamente após 5 segundos
            setTimeout(function() {
                var successAlert = document.getElementById('successAlert');
                successAlert.remove();
            }, 5000);
        </script>
    <?php
    } elseif (!empty($mensagem_erro)) { // Verifica se a mensagem de erro não está vazia
    ?>
        <div id="errorAlert" class="alert alert-danger alert-dismissible fade show fixed-bottom mx-auto my-3" role="alert" style="max-width: 600px;">
            <strong>Erro!</strong> <?php echo $mensagem_erro; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <script>
            // Fecha o alerta de erro automaticamente após 5 segundos
            setTimeout(function() {
                var errorAlert = document.getElementById('errorAlert');
                errorAlert.remove();
            }, 5000);
        </script>
    <?php
    }
    ?>

    <div class="d-flex justify-content-center align-items-center mt-3">
        <div class="card border rounded shadow p-2" style="max-width: 600px; width: 100%;">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title">Saída</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inputProduto" class="form-label">Produto</label>
                                <input type="text" class="form-control" id="inputProduto" name="produto" placeholder="Insira o código ou EAN" required>
                                <input type="hidden" id="inputProdutoId" name="produto_id"> <!-- Campo oculto para armazenar o ID do produto -->
                                <div id="productListContainer" class="product-list-container"></div> <!-- Container para exibir a lista de produtos -->
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inputQuantidade" class="form-label">Quantidade</label>
                                <input type="number" class="form-control" id="inputQuantidade" name="quantidade" placeholder="0" required min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inputEstoque" class="form-label">Estoque de Origem</label>
                                <select class="form-select" id="inputEstoque" name="estoque_origem" required>
                                    <option value="">Selecione um estoque</option>
                                    <?php
                                    if ($stocks->num_rows > 0) {
                                        while ($stock = $stocks->fetch_assoc()) {
                                            echo '<option value="' . $stock['ID'] . '">' . $stock['name'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inputCliente" class="form-label">Destino/Cliente</label>
                                <input type="text" class="form-control" id="inputCliente" name="cliente" placeholder="Nome do Cliente" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="inputObservacao" class="form-label">Observação</label>
                                <textarea class="form-control" id="inputObservacao" name="observacao" rows="3" placeholder="Observação"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .product-list-container {
        position: absolute;
        background-color: #fff;
        border: 1px solid #ccc;
        max-height: 200px;
        /* Defina a altura máxima que desejar */
        overflow-y: auto;
        /* Adiciona uma barra de rolagem vertical caso os botões ultrapassem a altura máxima */
        width: max-content;
        z-index: 9999;
        /* Garante que os botões estejam na frente do conteúdo */
    }

    .product-button {
        display: block;
        width: 100%;
        padding: 8px;
        text-align: left;
        border: none;
        background-color: transparent;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .product-button:hover {
        background-color: #f0f0f0;
    }

    .product-button.selected {
        border: 1px solid #007bff;
    }
</style>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        const inputProduto = document.getElementById("inputProduto");
        const inputProdutoId = document.getElementById("inputProdutoId");
        const productListContainer = document.getElementById("productListContainer");

        inputProduto.addEventListener("input", function() {
            const search = inputProduto.value.trim();

            if (search.length === 0) {
                productListContainer.innerHTML = ""; // Limpa o conteúdo
                return;
            }

            fetch(`/produto/findAllByCodeOrEan?search=${search}`)
                .then(response => response.json())
                .then(data => {
                    const products = data.products;
                    const productListHTML = products.map(product => {
                        return `<div>
                            <button type="button" class="btn btn-outline-primary product-button" data-id="${product.ID}" data-code="${product.code}">
                                ${product.code} ${product.importer}
                            </button>
                        </div>`;
                    }).join("");

                    productListContainer.innerHTML = productListHTML;

                    // Adiciona o evento de clique aos botões
                    document.querySelectorAll(".product-button").forEach(button => {
                        button.addEventListener("click", function() {
                            inputProduto.value = this.getAttribute("data-code"); // Define o valor visualmente no input
                            inputProdutoId.value = this.getAttribute("data-id"); // Define o ID do produto nos dados do input oculto
                            productListContainer.innerHTML = "";
                        });
                    });
                })
                .catch(error => {
                    console.error("Erro ao buscar produtos:", error);
                });
        });
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>