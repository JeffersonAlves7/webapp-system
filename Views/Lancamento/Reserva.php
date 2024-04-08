<?php
$pageTitle = "Incluir Lançamento";
$extraHeadContent = '<link rel="stylesheet" href="/public/lancamento.css">';
ob_start();

require "Components/Header.php";
?>

<main class="container position-relative">
    <h1 class="mt-4 mb-3"><?= $pageTitle ?></h1>

    <div class="d-flex gap-3">
        <a href="/lancamento/entrada">
            <button class="btn btn-custom">Entrada</button>
        </a>
        <a href="/lancamento/saida">
            <button class="btn btn-custom">Saída</button>
        </a>
        <a href="/lancamento/transferencia">
            <button class="btn btn-custom">Transferência</button>
        </a>
        <a href="/lancamento/devolucao">
            <button class="btn btn-custom">Devolução</button>
        </a>
        <button class="btn btn-custom active">Reservas</button>
    </div>

    <?php require "Components/StatusMessage.php" ?>

    <div class="d-flex justify-content-center align-items-center mt-3">
        <div class="card border rounded shadow p-2" style="max-width: 600px; width: 100%;">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title">Reserva</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inputProduto" class="form-label">Código ou EAN</label>
                                <input type="text" class="form-control" id="inputProduto" name="produto" placeholder="Insira o código ou EAN" required>
                                <input type="hidden" id="inputProdutoId" name="produto_id" required readonly> <!-- Campo oculto para armazenar o ID do produto -->
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
                                <label for="inputEstoqueOrigem" class="form-label">Estoque de Origem</label>
                                <select class="form-select" id="inputEstoqueOrigem" name="estoque_origem" required>
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
                        <div class="col-md-6">
                            <label for="dataRetirada" class="form-label">Data de retirada</label>
                            <input type="date" class="form-control" id="dataRetirada" name="dataRetirada" required>
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

</main>

<script src="/public/lancamento.js"></script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>