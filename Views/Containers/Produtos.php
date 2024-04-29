<?php
$pageTitle = "Embarques";
ob_start();

require "Components/Header.php";
?>
<main>
    <div class="d-flex gap-4 align-items-center">
        <button id="go-back" class="btn btn-custom">
            <i class="bi bi-arrow-left"></i>
        </button>
        <h1 class="mb-3"><?= $pageTitle ?> - Produtos</h1>
    </div>

    <div class="table-responsive">
        <table class="table table-striped" style="min-width:max-content">
            <thead class="thead-dark" style="position: sticky; top: 0;">
                <tr>
                    <th>Código</th>
                    <th>Importadora</th>
                    <th>Quantidade Esperada</th>
                    <th>Quantidade Entregue</th>
                    <th>Status</th>
                    <th>Data de Embarque</th>
                    <th>Previsão de Chegada</th>
                    <th>Dias para Chegar</th>
                    <th>Data de Chegada</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($products) && $products->num_rows > 0) : ?>
                    <?php while ($row = $products->fetch_assoc()) : ?>
                        <tr>
                            <td><?= $row['code'] ?></td>
                            <td><?= $row['importer'] ?></td>
                            <td>
                                <p class="text-center">
                                    <?= $row['quantity_expected'] ? $row['quantity_expected'] : '-' ?>
                                </p>
                            </td>
                            <td>
                                <p class="text-center">
                                    <?= $row['in_stock'] ?  $row['quantity'] : '-' ?>
                                </p>
                            </td>
                            <td>
                                <p class="text-center">
                                    <?= $row['in_stock'] ? 'Em Estoque' : 'Em Trânsito' ?>
                                </p>
                            </td>
                            <td>
                                <p class="text-center">
                                    <?= $row['departure_date'] ?  date('d/m/Y', strtotime($row['departure_date'])) : '-' ?>
                                </p>
                            </td>
                            <td>
                                <p class="text-center">
                                    <?= $row['departure_date'] ? date('d/m/Y', strtotime($row['departure_date'] . ' + 30 days')) : '-' ?>
                                </p>
                            </td>
                            <td>
                                <p class="text-center">
                                    <?php
                                    if ($row['in_stock'] && !$row['departure_date']) {
                                        echo "Chegou no prazo.";
                                    } else {
                                        $expected_arrival_date = new DateTime(date('Y-m-d', strtotime($row['departure_date'] . ' + 30 days')));
                                        $days_late = (new DateTime())->diff($expected_arrival_date)->days;

                                        if ($row['arrival_date']) {
                                            if ($days_late > 0) {
                                                echo "Chegou dia " . $row['arrival_date'] . " " . $days_late . " dias atrasado.";
                                            } else {
                                                echo "Chegou dia " . $row['arrival_date'] . " " . abs($days_late) . " dias adiantado.";
                                            }
                                        } else {
                                            if ($days_late == 0) {
                                                echo "<span class='text-warning'>Previsto para chegar hoje!</span>";
                                            } else if ($days_late > 0) {
                                                echo "<span class='text-danger'>" . $days_late . " dias atrasado!</span>";
                                            } else {
                                                echo "<span class='text-success'>Previsto para chegar em " . abs($days_late) . " dias!</span>";
                                            }
                                        }
                                    }
                                    ?>
                                </p>
                            </td>
                            <td><?= $row['arrival_date'] ?></td>
                            <td>
                                <!-- <form method="post" class="deleteProductForm" id="<?= "formProduct" . $row['product_ID'] ?>">
                                    <input type="hidden" name="product_ID" value="<?= $row['product_ID'] ?>">
                                    <input type="hidden" name="container_ID" value="<?= $row['container_ID'] ?>">
                                    <button type='submit' class='btn'>
                                        <i class='bi bi-trash text-danger'></i>
                                    </button>
                                </form> -->
                                <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#deleteProductModal" data-id="<?= $row['product_ID'] ?>">
                                    <i class='bi bi-trash text-danger'></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan='9' class="text-center" style="padding: 1rem;">Nenhum produto encontrado neste container.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
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
        $isNextDisabled = !isset($products) || !$products->num_rows || $currentPage >= $pageCount;
        ?>

        <div class="d-flex justify-content-center align-items-center gap-2 flex-wraps">
            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap" style="max-width: 300px;">
                <form method="GET" class="d-flex align-items-center">
                    <input type="hidden" name="page" value="<?= $prevPage ?>">
                    <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isPrevDisabled) ?> title="Voltar">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                </form>

                <span class="text-center">Página <?= $currentPage ?> de <?= $pageCount ?></span>

                <form method="GET">
                    <input type="hidden" name="page" value="<?= $nextPage ?>">
                    <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isNextDisabled) ?> title="Avançar">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>

            <a href="/embarques/conferir/<?= $container_ID ?>">Conferir embarque</a>
        </div>
    <?php endif; ?>

    <!-- Modal para confirmar o delete do produto -->
    <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteProductModalLabel">Deletar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja deletar este produto?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="post" class="deleteProductForm" id="formProduct">
                        <input type="hidden" name="product_ID" value="">
                        <input type="hidden" name="container_ID" value="<?= $container_ID ?>">
                        <button type="submit" class="btn btn-danger">Deletar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include_once "Components/StatusMessage.php"; ?>
</main>

<script>
    window.onload = () => {
        // Obtém a URL atual
        var currentUrl = window.location.href;

        // Adiciona a URL atual como parâmetro query
        var formAction = "/embarques/deletarProduto?redirect=" + encodeURIComponent(currentUrl);

        // Define a ação do formulário
        document.querySelectorAll('.deleteProductForm').forEach((e) => e.setAttribute('action', formAction));
    }

    // Adiciona o ID do produto ao formulário de delete
    var deleteProductModal = document.getElementById('deleteProductModal');
    deleteProductModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var product_ID = button.getAttribute('data-id');
        var formProduct = document.getElementById('formProduct');
        formProduct.querySelector('input[name="product_ID"]').value = product_ID;
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>