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

        $size = 15;

        $shortDescription = substr($description, 0, $size);
        if (strlen($description) > $size) {
            $shortDescription .= '... <a href="#" class="toggle-description" data-full-description="' . htmlspecialchars($description) . '">Ver mais</a>';
        }

        return $shortDescription;
    }
    ?>

    <div class="d-flex gap-4 align-items-center">
        <button id="go-back" class="btn btn-custom">
            <i class="bi bi-arrow-left"></i>
        </button>
        <h1 class="mt-4 mb-3"><?= $produto["code"] ?> - <?= generateDescription($produto['description']) ?></h1>
    </div>

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
                    <td>Disponível para venda: <?= $disponivel_para_venda; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Adicionar lancamento -->
    <form method="get" action="/lancamento" class="mt-3" id="form-lancamento">
        <input type="hidden" name="product_ID" value="<?= $produto["ID"] ?>" />
        <input type="hidden" name="product_code" value="<?= $produto["code"] ?>" />
        <input type="hidden" name="product_importer" value="<?= $produto["importer"] ?>" />
        <!-- Button submit com um icone de + e um texto aoo lado escrito "Incluir novo lancamento -->
        <button type="submit" class="btn btn-custom">
            <i class="bi bi-plus"></i>
            Incluir novo lançamento
        </button>
    </form>
    </td>

    <!-- Estoques -->
    <div class="d-flex gap-3 mt-3 mb-3">
        <form method="get">
            <button type="submit" class="btn btn-custom <?= isset($_GET["estoque"]) && $_GET["estoque"] != '' ? "" : "active" ?>">Geral</button>
        </form>

        <?php
        if (isset($stocks) && $stocks->num_rows > 0) {
            while ($estoque = $stocks->fetch_assoc()) {
                $name = $estoque["name"];
                $ID = $estoque["ID"];
                $active = (isset($_GET["estoque"]) && $_GET["estoque"] == "$ID" ? "active" : "");

                echo "<form method='get'>
                    <input type='hidden' name='estoque' value='$ID'/>
                    <button type='submit' class='btn btn-custom $active'>$name</button>
                </form>";
            }
        }
        ?>
    </div>


    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Estoque Origem</th>
                    <th>Estoque Destino</th>
                    <th>Cliente</th>
                    <th>Observação</th>
                    <th>Data</th>
                    <th></th>
                </tr>
            </thead>
            <?php if ($transacoes->num_rows > 0) : ?>
                <tbody>
                    <?php while ($transacao = $transacoes->fetch_assoc()) : ?>
                        <tr>
                            <td><?= $transacao["type"]; ?></td>
                            <td><?= $transacao["quantity"]; ?></td>
                            <td><?= $transacao["from_stock"]; ?></td>
                            <td><?= $transacao["to_stock"]; ?></td>
                            <td><?= $transacao["client_name"]; ?></td>
                            <td><?= $transacao["observation"]; ?></td>
                            <td><?= $transacao["updated_at"]; ?></td>
                            <td>
                                <button type='button' class='btn btn-danger delete-transaction' data-id='<?= $transacao["ID"] ?>' data-bs-toggle='modal' data-bs-target='#cancelModal' class='btn-cancel'>Apagar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            <?php else : ?>
                <tbody>
                    <td colspan="7">Nenhuma transação encontrada.</td>
                </tbody>
            <?php endif; ?>
        </table>
    </div>

    <?php if ($pageCount > 1) : ?>
        <?php
        function isButtonDisabled($condition)
        {
            return $condition ? 'disabled' : '';
        }

        $currentPage = $_GET['page'] ?? 1;
        $prevPage = $currentPage - 1;
        $nextPage = $currentPage + 1;
        $isPrevDisabled = !isset($_GET["page"]) || intval($_GET["page"]) <= 1;
        $isNextDisabled = !isset($transacoes) || !$transacoes->num_rows > 0 || $currentPage >= $pageCount;
        ?>

        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap" style="max-width: 250px; margin: 0 auto;">
            <form method="GET" class="d-flex align-items-center">
                <input type="hidden" name="page" value="<?= $prevPage ?>">
                <input type="hidden" name="estoque" value="<?= $_GET["estoque"] ?? "" ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isPrevDisabled) ?> title="Voltar">
                    <i class="bi bi-arrow-left"></i>
                </button>
            </form>

            <span class="text-center">Página <?= $currentPage ?> de <?= $pageCount ?></span>

            <form method="GET">
                <input type="hidden" name="page" value="<?= $nextPage ?>">
                <input type="hidden" name="estoque" value="<?= $_GET["estoque"] ?? "" ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isNextDisabled) ?> title="Avançar">
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>
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

<div class="modal fade" id="lancamentoModal" tabindex="-1" aria-labelledby="lancamentoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lancamentoModalLabel">Escolha o tipo de lançamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <button class="btn btn-custom lancamento-type" data-type="entrada">Entrada</button>
                    </li>
                    <li class="list-group-item">
                        <button class="btn btn-custom lancamento-type" data-type="saida">Saída</button>
                    </li>
                    <li class="list-group-item">
                        <button class="btn btn-custom lancamento-type" data-type="transferencia">Transferência</button>
                    </li>
                    <li class="list-group-item">
                        <button class="btn btn-custom lancamento-type" data-type="devolucao">Devolução</button>
                    </li>
                    <li class="list-group-item">
                        <button class="btn btn-custom lancamento-type" data-type="reserva">Reserva</button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.delete-transaction').forEach(function(element) {
        element.addEventListener('click', function() {
            var reserveID = this.getAttribute('data-id');
            document.getElementById('deleteTransactionId').value = reserveID;
        });
    });

    document.getElementById('form-lancamento').addEventListener('submit', function(event) {
        event.preventDefault();
        var form = event.target;
        var productID = form.querySelector('input[name="product_ID"]').value;
        var productCode = form.querySelector('input[name="product_code"]').value;
        var productImporter = form.querySelector('input[name="product_importer"]').value;

        var modal = new bootstrap.Modal(document.getElementById('lancamentoModal'));
        modal.show();

        document.getElementById('lancamentoModal').querySelectorAll('.lancamento-type').forEach(function(element) {
            element.addEventListener('click', function() {
                var type = this.getAttribute('data-type');
                window.location.href = `/lancamento/${type}?product_ID=${productID}&product_code=${productCode}&product_importer=${productImporter}`;
            });
        });
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>