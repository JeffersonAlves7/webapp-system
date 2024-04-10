<?php
$extraHeadContent = '<link rel="stylesheet" href="/public/lancamento.css">';
$pageTitle = "Incluir Lançamento";
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
        <button class="btn btn-custom active">Transferência</button>
        <a href="/lancamento/devolucao">
            <button class="btn btn-custom">Devolução</button>
        </a>
        <a href="/lancamento/reserva">
            <button class="btn btn-custom">Reservas</button>
        </a>
    </div>

    <?php require "Components/StatusMessage.php" ?>

    <div class="d-flex justify-content-center align-items-center mt-3">
        <div class="card border rounded shadow p-2" style="max-width: 600px; width: 100%;">
            <div class="card-header bg-transparent border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Transferência</h5>
                    <a class="nav-link" href="/historico/transferencias">Histórico de transferências</a>
                </div>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inputProduto" class="form-label">Produto</label>
                                <?php if (isset($_GET['product_ID']) && !empty($_GET['product_ID'])) : ?>
                                    <input type="text" class="form-control" value="<?= $_GET['product_code'] . '-' . $_GET['product_importer'] ?>" id="inputProduto" name="produto" placeholder="Insira o código ou EAN" required>
                                    <input type="hidden" id="inputProdutoId" name="produto_id" value="<?= $_GET['product_ID'] ?>" required readonly>
                                <?php else : ?>
                                    <input type="text" class="form-control" id="inputProduto" name="produto" placeholder="Insira o código ou EAN" required>
                                    <input type="hidden" id="inputProdutoId" name="produto_id" required readonly>
                                <?php endif; ?>
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
                                <label for="inputEstoqueDestino" class="form-label">Estoque de Destino</label>
                                <select class="form-select" id="inputEstoqueDestino" name="estoque_destino" required>
                                    <option value="">Selecione um estoque</option>
                                    <?php
                                    if ($stocks->num_rows > 0) {
                                        $stocks->data_seek(0);

                                        while ($stock = $stocks->fetch_assoc()) {
                                            echo '<option value="' . $stock['ID'] . '">' . $stock['name'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="localizacao" class="form-label">Localização</label>
                                <input type="text" class="form-control" name="localizacao" id="localizacao">
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

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn btn-custom">Enviar</button>
                        <a class="nav-link" href="/lancamento/conferirTransferencias">Conferir transferências</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="/public/lancamento.js"></script>

<script>
    // Função para verificar se os valores selecionados nos dois selects são diferentes
    function verificarEstoquesDiferentes() {
        var estoqueOrigem = document.getElementById('inputEstoqueOrigem').value;
        var estoqueDestino = document.getElementById('inputEstoqueDestino').value;

        if (estoqueOrigem === estoqueDestino) {
            alert('Os estoques de origem e destino não podem ser iguais. Por favor, selecione estoques diferentes.');
            return false;
        }
        return true;
    }

    // Adiciona um listener ao formulário para chamar a função antes de enviar
    document.querySelector('form').addEventListener('submit', function(event) {
        console.log("submit")
        if (!verificarEstoquesDiferentes()) {
            try {
                event.target.reset()
            } catch {}
            event.preventDefault(); // Impede o envio do formulário se os estoques forem iguais
        }
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>