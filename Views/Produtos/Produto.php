<?php
$pageTitle = "Produtos";
ob_start();
?>

<?php require "Components/Header.php" ?>

<div class="container">
    <h1 class="mt-4 mb-3"><?= $produto["code"] ?></h1>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="descricao-tab" data-bs-toggle="tab" data-bs-target="#descricao" type="button" role="tab" aria-controls="descricao" aria-selected="true">Descrição</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="estoque-tab" data-bs-toggle="tab" data-bs-target="#estoque" type="button" role="tab" aria-controls="estoque" aria-selected="false">Estoque</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="transacoes-tab" data-bs-toggle="tab" data-bs-target="#transacoes" type="button" role="tab" aria-controls="transacoes" aria-selected="false">Transações</button>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="descricao" role="tabpanel" aria-labelledby="descricao-tab">
            <?php
            if (isset($produto["description"])) {
                echo "<p class='description'>" . htmlspecialchars($produto["description"]) . "</p>";
            } else {
                echo "<p>Nenhuma informação de descrição disponível.</p>";
            }
            ?>
        </div>
        <div class="tab-pane fade" id="estoque" role="tabpanel" aria-labelledby="estoque-tab">
            <?php if ($quantidade_em_estoque->num_rows > 0) : ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nome do Estoque</th>
                                <th>Quantidade</th>
                                <th>Quantidade Reservada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($dados = $quantidade_em_estoque->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= $dados["stock_name"]; ?></td>
                                    <td><?= $dados["quantity"]; ?></td>
                                    <td><?= $dados["quantity_in_reserve"]; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <p>Nenhuma informação de estoque disponível.</p>
            <?php endif ?>
        </div>
        <div class="tab-pane fade" id="transacoes" role="tabpanel" aria-labelledby="transacoes-tab">
            <?php if ($transacoes->num_rows > 0) : ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                                <th>Estoque Origem</th>
                                <th>Estoque Destino</th>
                                <th>Observação</th>
                                <th>Data</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($transacao = $transacoes->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= $transacao["type"]; ?></td>
                                    <td><?= $transacao["quantity"]; ?></td>
                                    <td><?= $transacao["from_stock"]; ?></td>
                                    <td><?= $transacao["to_stock"]; ?></td>
                                    <td><?= $transacao["observation"]; ?></td>
                                    <td><?= $transacao["updated_at"]; ?></td>
                                    <td>
                                        <button type='button' class='btn btn-danger delete-transaction' data-id='<?= $transacao["ID"] ?>' data-bs-toggle='modal' data-bs-target='#cancelModal' class='btn-cancel'>Apagar</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <p>Nenhuma transação encontrada.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Apagar transação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelForm" method="post">
                <input type="hidden" name="_method" value="delete">
                <div class="modal-body">
                    <input type="hidden" name="ID" id="deleteTransactionId" value="">
                    <p>Tem certeza de que deseja apagar esta transação?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="confirmCancelBtn">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php
$content = ob_get_clean();
include "Components/Template.php";
?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var tabs = new bootstrap.Tab(document.getElementById('myTab'));
        // var tabContent = new bootstrap.TabContent(document.getElementById('myTabContent'));

        // Adiciona manipuladores de eventos para os botões de cancelamento
        document.querySelectorAll('.delete-transaction').forEach(function(element) {
            element.addEventListener('click', function() {
                var reserveID = this.getAttribute('data-id');
                document.getElementById('deleteTransactionId').value = reserveID;
            });
        });
    })
</script>