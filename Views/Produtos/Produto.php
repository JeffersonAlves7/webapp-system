<?php
$pageTitle = "Produtos";
ob_start();
?>

<?php require "Components/Header.php" ?>

<main>
    <?php
    $quantidade_total = 0;
    $quantidade_reservada = 0;

    // Certifique-se de que $quantidade_em_estoque é um objeto mysqli_result
    if ($quantidade_em_estoque instanceof mysqli_result) {
        while ($dados = $quantidade_em_estoque->fetch_assoc()) {
            $quantidade_total += $dados["quantity"];
            $quantidade_reservada += $dados["quantity_in_reserve"];
        }
        $quantidade_em_estoque->data_seek(0); // Reseta o ponteiro para o início
    } else {
        // Se não for mysqli_result, talvez seja um array já processado.
        // Adapte esta parte se $quantidade_em_estoque for um array direto.
        foreach ($quantidade_em_estoque as $dados) {
            $quantidade_total += $dados["quantity"];
            $quantidade_reservada += $dados["quantity_in_reserve"];
        }
    }
    ?>

    <div class="d-flex gap-4 align-items-center">
        <button id="go-back" class="btn btn-custom">
            <i class="bi bi-arrow-left"></i>
        </button>
        <h1 class="mt-4 mb-3"><?= $produto["code"] ?> - <?= $produto['description'] ?></h1>
    </div>

    <!-- Botão para minimizar/maximizar a tabela -->
    <div class="d-flex gap-4 align-items-center">
        <button id="toggleTableBtn" class="btn btn-custom mb-2">
            <i class="bi bi-arrows-angle-contract"></i> Minimizar
        </button>
        <p>Resumo do estoque</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h2 class="h5 mt-4 mb-3">Resumo do Estoque</h2>

            <div class="table-responsive" id="stockTable">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Estoque</th>
                            <th>Disponível</th>
                            <th>Reservado</th>
                            <th>Total</th>
                            <th>Localização</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Garante que $quantidade_em_estoque seja um array iterável para o loop
                        $estoque_data = [];
                        if ($quantidade_em_estoque instanceof mysqli_result) {
                            $estoque_data = $quantidade_em_estoque->fetch_all(MYSQLI_ASSOC);
                        } else {
                            $estoque_data = $quantidade_em_estoque; // Já é um array
                        }

                        if (!empty($estoque_data)) :
                            foreach ($estoque_data as $dados) : ?>
                                <tr>
                                    <td><?= $dados["stock_name"]; ?></td>
                                    <td><?= $dados["quantity"]; ?></td>
                                    <td><?= $dados["quantity_in_reserve"]; ?></td>
                                    <td><?= $dados["quantity"] + $dados["quantity_in_reserve"]; ?></td>
                                    <td>
                                        <div class="input-group">
                                            <input data-stockId="<?= $dados["stock_ID"]; ?>" data-productId="<?= $produto["ID"]; ?>" type="text" class="form-control" value="<?= htmlspecialchars($dados["location"] ?? '') ?>" />
                                            <button class="btn btn-custom change-location-btn">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="text-center">Nenhum dado de estoque encontrado.</td>
                            </tr>
                        <?php endif; ?>
                        <tr class="font-weight-bold">
                            <td>Total</td>
                            <td><?= $quantidade_total; ?></td>
                            <td><?= $quantidade_reservada; ?></td>
                            <td><?= $quantidade_total + $quantidade_reservada; ?></td>
                            <td></td>
                        </tr>
                        <tr class="font-weight-bold">
                            <td colspan="3">Disponível para venda</td>
                            <td><?= $quantidade_total; ?></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-6">
            <h2 class="h5 mt-4 mb-3">Vendas por Período</h2>
            <div class="p-3 mb-4 border rounded">
                <form method="GET" action="/produtos/byId/<?= $produto['ID'] ?>">
                    <input type="hidden" name="product_ID" value="<?= $produto['ID'] ?>">
                    <div class="row g-2 align-items-end">
                        <div class="col-6"> <label for="startDate" class="form-label mb-1">Data Início</label>
                            <input type="date" class="form-control form-control-sm" id="startDate" name="startDate" value="<?= htmlspecialchars($startDate) ?>" required>
                        </div>
                        <div class="col-6"> <label for="endDate" class="form-label mb-1">Data Fim</label>
                            <input type="date" class="form-control form-control-sm" id="endDate" name="endDate" value="<?= htmlspecialchars($endDate) ?>" required>
                        </div>
                        <div class="col-12 mt-2"> <button type="submit" class="btn btn-custom w-100">
                                <i class="bi bi-search"></i> Buscar Vendas
                            </button>
                        </div>
                    </div>
                </form>
                <div class="mt-3">
                    <h5>Total de Vendas no Período</h5>
                    <div class="row">
                        <div class="col-4">
                            <p>Galpao: <strong><?= htmlspecialchars($totalSalesGalpao) ?></strong></p>
                        </div>
                        <div class="col-4">
                            <p>Loja: <strong><?= htmlspecialchars($totalSalesLoja) ?></strong></p>
                        </div>
                        <div class="col-4">
                            <p>Total: <strong><?= htmlspecialchars($totalSalesLoja+ $totalSalesGalpao) ?></strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Estoques -->
    <div class="d-flex gap-3 mt-3 mb-3">
        <!-- Adicionar lancamento -->
        <form method="get" action="/lancamento" id="form-lancamento">
            <input type="hidden" name="product_ID" value="<?= $produto["ID"] ?>" />
            <input type="hidden" name="product_code" value="<?= $produto["code"] ?>" />
            <input type="hidden" name="product_importer" value="<?= $produto["importer"] ?>" />
            <button type="submit" class="btn btn-custom">
                <i class="bi bi-plus"></i>
                Incluir novo lançamento
            </button>
        </form>

        <form method="get">
            <button type="submit" class="btn btn-custom <?= isset($_GET["estoque"]) && $_GET["estoque"] != '' ? "" : "active" ?>">Geral</button>
        </form>

        <?php
        // Certifique-se de que $stocks é um objeto mysqli_result e resete o ponteiro
        if (isset($stocks) && $stocks instanceof mysqli_result) {
            $stocks->data_seek(0);
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

    <div class="table-responsive" style="max-height: 50vh; min-height: 100px;">
        <table class="table table-striped">
            <thead class="thead-dark" style="position: sticky; top: 0; z-index: 1000">
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
            <?php if (isset($transactions) && count($transactions) > 0) : ?>
                <tbody>
                    <?php foreach ($transactions as $row) : ?>
                        <tr>
                            <td><?= $row["type"]; ?></td>
                            <td><?= $row["quantity"]; ?></td>
                            <td><?= $row["from_stock"]; ?></td>
                            <td><?= $row["to_stock"]; ?></td>
                            <td><?= $row["client_name"]; ?></td>
                            <td><?= $row["observation"]; ?></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($row["updated_at"])) ?></td>
                            <td>
                                <button type='button' class='btn btn-danger delete-transaction' data-id='<?= $row["ID"] ?>' data-bs-toggle='modal' data-bs-target='#cancelModal' class='btn-cancel'>Apagar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php else : ?>
                <tbody>
                    <td colspan="7">Nenhuma transação encontrada.</td>
                </tbody>
            <?php endif; ?>
        </table>
    </div>

    <?php if (isset($pageCount) && $pageCount > 1) : ?>
        <?php
        function isButtonDisabled($condition)
        {
            return $condition ? 'disabled' : '';
        }

        $currentPage = $_GET['page'] ?? 1;
        $prevPage = $currentPage - 1;
        $nextPage = $currentPage + 1;
        $isPrevDisabled = !isset($_GET["page"]) || intval($_GET["page"]) <= 1;
        $isNextDisabled = !isset($transactions) || !count($transactions) > 0 || $currentPage >= $pageCount;
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

    // Script to toggle table visibility
    document.getElementById('toggleTableBtn').addEventListener('click', function() {
        var table = document.getElementById('stockTable');
        const htmlMaximize = '<i class="bi bi-arrows-fullscreen"></i> Maximizar';
        const htmlMinimize = '<i class="bi bi-arrows-angle-contract"></i> Minimizar';

        if (table.style.display === 'none') {
            table.style.display = 'table';
            this.innerHTML = htmlMinimize;
        } else {
            table.style.display = 'none';
            this.innerHTML = htmlMaximize;
        }
    });

    // Ao clicar para alterar a localização do produto
    document.querySelectorAll('.change-location-btn').forEach(function(element) {
        element.addEventListener('click', function() {
            var input = this.previousElementSibling;
            var stockId = input.getAttribute('data-stockId');
            var productId = input.getAttribute('data-productId');
            var location = input.value;

            fetch('/produtos/changeLocation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    stockId: stockId,
                    productId: productId,
                    location: location
                })
            }).then(function(response) {
                if (response.ok) {
                    input.classList.add('is-valid');
                    setTimeout(function() {
                        input.classList.remove('is-valid');
                    }, 2000);
                } else {
                    input.classList.add('is-invalid');
                    setTimeout(function() {
                        input.classList.remove('is-invalid');
                    }, 2000);
                }
            }).catch(function(error) {
                console.error('Erro na requisição de mudança de localização:', error);
                // Opcional: mostrar uma mensagem de erro genérica ao usuário
            });
        });
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>