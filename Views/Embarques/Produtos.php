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

    <div class="table-responsive" style="max-height: 60vh; min-height: 100px">
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
                    <th>Editar</th>
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

                                        if ($row['arrival_date']) {
                                            $arrival_date = new DateTime($row['arrival_date']);
                                        } else {
                                            $arrival_date = new DateTime(date('Y-m-d'));
                                        }

                                        $diffInSeconds = $arrival_date->getTimestamp() - $expected_arrival_date->getTimestamp();
                                        $days_late = round($diffInSeconds / (60 * 60 * 24));

                                        if ($row['arrival_date']) {
                                            if ($days_late > 0) {
                                                echo "Chegou dia " . $row['arrival_date'] . " " . $days_late . " dia(s) atrasado.";
                                            } else {
                                                echo "Chegou dia " . $row['arrival_date'] . " " . abs($days_late) . " dia(s) adiantado.";
                                            }
                                        } else {
                                            if ($days_late == 0) {
                                                echo "<span class='text-warning'>Previsto para chegar hoje!</span>";
                                            } else if ($days_late > 0) {
                                                echo "<span class='text-danger'>" . $days_late . " dia(s) atrasado!</span>";
                                            } else {
                                                echo "<span class='text-success'>Previsto para chegar em " . abs($days_late) . " dia(s)!</span>";
                                            }
                                        }
                                    }
                                    ?>
                                </p>
                            </td>
                            <td><?= $row['arrival_date'] ?></td>
                            <td>
                                <?php if ($row['in_stock']) : ?>
                                    <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#editProductModal" data-id="<?= $row['product_ID'] ?>">
                                        <i class='bi bi-pencil'></i>
                                    </button>
                                <?php else : ?>
                                    <button type="button" class="btn" disabled>
                                        <i class='bi bi-pencil'></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
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

    <!-- Modal para editar o produto -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" id="editProductForm">
                    <input type="hidden" name="action" value="editProduct">

                    <div class="modal-header">
                        <h5 class="modal-title" id="editProductModalLabel">Editar Produto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body d-flex flex-column gap-3">
                        <input type="hidden" name="product_ID" value="">
                        <input type="hidden" name="container_ID" value="<?= $container_ID ?>">

                        <label for="quantity" class="form-label">Quantidade Entregue</label>
                        <input type="number" name="quantity" class="form-control" required>

                        <label for="arrival_date" class="form-label">Data de Chegada</label>
                        <input type="date" name="arrival_date" class="form-control" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                    <form method="post" class="deleteProductForm" id="deleteProductForm">
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
    const deleteProductForm = document.getElementById('deleteProductForm');
    const deleteProductModal = document.getElementById('deleteProductModal');
    const editProductModal = document.getElementById('editProductModal');
    const editProductForm = document.getElementById('editProductForm');

    window.onload = () => {
        // Obtém a URL atual
        var currentUrl = window.location.href;

        var deleteAction = "/embarques/deletarProduto?redirect=" + encodeURIComponent(currentUrl);
        document.querySelectorAll('.deleteProductForm').forEach((e) => e.setAttribute('action', deleteAction));

        var editAction = "/embarques/editarProduto?redirect=" + encodeURIComponent(currentUrl);
        editProductForm.setAttribute('action', editAction);
    }

    // Adiciona o ID do produto ao formulário de delete
    deleteProductModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var product_ID = button.getAttribute('data-id');
        var deleteProductForm = document.getElementById('deleteProductForm');
        deleteProductForm.querySelector('input[name="product_ID"]').value = product_ID;
    });

    // Adiciona o ID do produto ao formulário de edição
    editProductModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var product_ID = button.getAttribute('data-id');
        var editProductForm = document.getElementById('editProductForm');
        editProductForm.querySelector('input[name="product_ID"]').value = product_ID;

        var product = button.parentElement.parentElement;
        var quantity = product.children[3].children[0].innerText;
        var arrival_date = product.children[8].innerText;

        editProductForm.querySelector('input[name="quantity"]').value = quantity;
        editProductForm.querySelector('input[name="arrival_date"]').value = arrival_date;
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>