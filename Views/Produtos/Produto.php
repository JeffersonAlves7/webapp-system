<?php
$pageTitle = "Produtos";
ob_start();
?>

<?php require "Components/Header.php" ?>

<main>
    <?php
    $quantidade_total = 0;
    $quantidade_reservada = 0;

    while ($dados = $quantidade_em_estoque->fetch_assoc()) {
        $quantidade_total += $dados["quantity"];
        $quantidade_reservada += $dados["quantity_in_reserve"];
    }

    $disponivel_para_venda = $quantidade_total - $quantidade_reservada;

    $quantidade_em_estoque->data_seek(0);

    function generateDescription($description)
    {
        if (!isset($description)) {
            return '';
        }
        $len = 20;
        $shortDescription = substr($description, 0, $len);
        if (strlen($description) > $len) {
            $shortDescription .= '... <a href="#" class="toggle-description" data-full-description="' . htmlspecialchars($description) . '">Ver mais</a>';
        }

        return $shortDescription;
    }
    ?>

    <h1 class="mt-4 mb-3"><?= $produto["code"] ?> - <?= generateDescription($produto['description']) ?></h1>

    <div class="" style="max-width: 500px;">
        <table>
            <tbody>
                <?php while ($dados = $quantidade_em_estoque->fetch_assoc()) : ?>
                    <tr>
                        <td><?= $dados["stock_name"]; ?> total: <?= $dados["quantity"]; ?></td>
                        <td><?= $dados["stock_name"]; ?> reservado: <?= $dados["quantity_in_reserve"]; ?></td>
                    </tr>
                <?php endwhile; ?>
                <tr>
                    <td>Total Disponível: <?= $quantidade_total; ?></td>
                    <td>Total Reservado: <?= $quantidade_reservada; ?></td>
                </tr>
                <tr>
                    <td>Disponível para venda:</td>
                    <td><?= $disponivel_para_venda; ?></td>
                </tr>
            </tbody>
        </table>
    </div>

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
</main>

<?php require "Components/StatusMessage.php" ?>

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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.toggle-description').forEach(function(element) {
            element.addEventListener('click', function(event) {
                event.preventDefault();
                var fullDescription = this.getAttribute('data-full-description');
                alert(fullDescription);
            });
        });

        // Adiciona manipuladores de eventos para os botões de cancelamento
        document.querySelectorAll('.delete-transaction').forEach(function(element) {
            element.addEventListener('click', function() {
                var reserveID = this.getAttribute('data-id');
                document.getElementById('deleteTransactionId').value = reserveID;
            });
        });
    })
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>