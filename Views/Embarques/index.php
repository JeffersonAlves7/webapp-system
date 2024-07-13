<?php
$pageTitle = "Embarques";
ob_start();

require "Components/Header.php";

$search = isset($_GET['search']) ? $_GET['search'] : "";
?>

<main>
    <h1 class="mt-4 mb-3"><?= $pageTitle ?></h1>

    <!-- Botão para minimizar/maximizar o formulário -->
    <div class="d-flex gap-4 align-items-center">
        <button id="toggleFormBtn" class="btn btn-custom mb-2 text-white">
            <i class="bi bi-arrows-angle-contract text-white"></i> Minimizar
        </button>
    </div>


    <form method="get">
        <div class="row g-3 mb-2">
            <div class="col-md-3">
                <label for="search" class="form-label sr-only">Código do container</label>
                <input type="text" class="form-control" name="search" placeholder="Ex.: LT-001" value="<?= $search ?>">
            </div>

            <div class="col-md-3">
                <label for="product_code" class="form-label">Código ou EAN do produto</label>
                <input type="text" class="form-control" name="product_code" placeholder="Ex.: AB1445" value="<?= isset($_GET['product_code']) ? $_GET['product_code'] : "" ?>">
            </div>

            <div class="col-md-3">
                <label for="importer" class="form-label">Importadora</label>
                <select class="form-select" name="importer">
                    <option value="">Todas</option>
                    <option value="ATTUS" <?= isset($_GET['importer']) && $_GET['importer'] == 'ATTUS' ? 'selected' : '' ?>>ATTUS</option>
                    <option value="ATTUS_BLOOM" <?= isset($_GET['importer']) && $_GET['importer'] == 'ATTUS_BLOOM' ? 'selected' : '' ?>>ATTUS_BLOOM</option>
                    <option value="ALPHA_YNFINITY" <?= isset($_GET['importer']) && $_GET['importer'] == 'ALPHA_YNFINITY' ? 'selected' : '' ?>>ALPHA_YNFINITY</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">Todos</option>
                    <option value="1" <?= isset($_GET['status']) && $_GET['status'] == 'in_stock' ? 'selected' : '' ?>>Em Estoque</option>
                    <option value="0" <?= isset($_GET['status']) && $_GET['status'] == 'in_transit' ? 'selected' : '' ?>>Em Trânsito</option>
                </select>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <label for="start_date" class="form-label sr-only">Data de início</label>
                <input type="date" class="form-control" name="start_date" placeholder="Data de início" value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : "" ?>">
            </div>

            <div class="col-md-3">
                <label for="end_date" class="form-label sr-only">Data de término</label>
                <input type="date" class="form-control" name="end_date" placeholder="Data de término" value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : "" ?>">
            </div>

            <div class="col-md-3 d-flex justify-content-start align-items-end">
                <button type="submit" class="btn btn-custom">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
        </div>
    </form>

    <!-- Tabela -->
    <div class="table-responsive" style="max-height: 40vh; min-height: 100px">
        <table class="table table-striped" style="min-width: max-content">
            <thead class="thead-dark" style="position: sticky; top: 0; z-index: 1000">
                <tr>
                    <th>EAN</th>
                    <th>Código</th>
                    <th>Importadora</th>
                    <th>Container</th>
                    <th>Quantidade de Caixas</th>
                    <th>Status</th>
                    <th>Data de Embarque</th>
                    <th>Previsão de Chegada</th>
                    <th>Dias para Chegar</th>
                    <th>Data de Chegada</th>
                    <th>Editar</th>
                    <th>Apagar</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($products) && $products->num_rows > 0) : ?>
                    <?php while ($row = $products->fetch_assoc()) : ?>
                        <tr>
                            <td><?= $row['ean'] ?></td>
                            <td><?= $row['code'] ?></td>
                            <td><?= $row['importer'] ?></td>
                            <td>
                                <!-- Link para conferir o container a partir do container_ID -->
                                <a href="/embarques/conferir/<?= $row['container_ID'] ?>">
                                    <?= $row['container_name'] ?>
                                </a>
                            </td>
                            <td>
                                <p class="text-center">
                                    <?= $row['in_stock'] ?  $row['quantity'] : ($row['quantity_expected'] ? $row['quantity_expected'] : '-') ?>
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
                                                echo "Chegou dia " . $row['arrival_date'] . " - <b>" . $days_late . " dia(s)</b> atrasado.";
                                            } else {
                                                // echo "Chegou dia " . $row['arrival_date'] . " - <b>" . abs($days_late) . " dia(s)</b> adiantado.";
                                                echo "Chegou dia " . $row['arrival_date'];
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

            <tfoot style="position: sticky; bottom: -5px; z-index: 1000" class="bg-light">
                <!-- Aqui vai ter informacoes do total de embarques retornados e tambem do total de caixas -->
                <tr>
                    <td colspan="4" class="text-center">Total de Embarques: <?= $totalProducts ?></td>
                    <td class="text-center">Total de Caixas: <?= $totalBoxes ?></td>
                    <td colspan="6"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <form method="post" enctype="multipart/form-data" class="mb-3 mt-3" action="/embarques/importar">
        <div class="row g-3">
            <div class="col-md-6">
                <input type="file" name="file" class="form-control" accept=".xlsx, .csv" required>
            </div>
            <div class="col-md-6">
                <button type="submit" class="btn btn-custom">
                    <i class="bi bi-file-earmark-arrow-up"></i> Importar
                </button>
            </div>
        </div>
    </form>

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

        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap" style="max-width: 250px; margin: 0 auto;">
            <form method="GET" class="d-flex align-items-center">
                <input type="hidden" name="page" value="<?= $prevPage ?>">
                <input type="hidden" name="search" value="<?= $search ?>">
                <input type="hidden" name="start_date" value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : "" ?>">
                <input type="hidden" name="end_date" value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : "" ?>">
                <input type="hidden" name="product_code" value="<?= isset($_GET['product_code']) ? $_GET['product_code'] : "" ?>">
                <input type="hidden" name="status" value="<?= isset($_GET['status']) ? $_GET['status'] : "" ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isPrevDisabled) ?> title="Voltar">
                    <i class="bi bi-arrow-left"></i>
                </button>
            </form>

            <span class="text-center">Página <?= $currentPage ?> de <?= $pageCount ?></span>

            <form method="GET">
                <input type="hidden" name="page" value="<?= $nextPage ?>">
                <input type="hidden" name="search" value="<?= $search ?>">
                <input type="hidden" name="status" value="<?= isset($_GET['status']) ? $_GET['status'] : "" ?>">
                <input type="hidden" name="start_date" value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : "" ?>">
                <input type="hidden" name="end_date" value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : "" ?>">
                <input type="hidden" name="product_code" value="<?= isset($_GET['product_code']) ? $_GET['product_code'] : "" ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isNextDisabled) ?> title="Avançar">
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Modal para confirmar delecao do container -->
    <div class="modal fade" id="deleteContainerModal" tabindex="-1" aria-labelledby="deleteContainerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteContainerModalLabel">Deletar container</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja deletar este container?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="" id="deleteContainerLink">
                        <button type="button" class="btn btn-danger">Deletar</button>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include_once "Components/StatusMessage.php"; ?>
</main>

<script>
    // Adiciona o ID do container ao link de delete
    var deleteContainerModal = document.getElementById('deleteContainerModal');
    deleteContainerModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var container_ID = button.getAttribute('data-id');
        var deleteContainerLink = document.getElementById('deleteContainerLink');
        deleteContainerLink.href = "/embarques/deletar/" + container_ID;
    });

    // Script to toggle form visibility
    document.getElementById('toggleFormBtn').addEventListener('click', function() {
        var form = document.querySelector('form');
        const htmlMaximize = '<i class="bi bi-arrows-fullscreen text-white"></i> Maximizar';
        const htmlMinimize = '<i class="bi bi-arrows-angle-contract text-white"></i> Minimizar';

        if (form.style.display === 'none') {
            form.style.display = 'block';
            this.innerHTML = htmlMinimize;
            document.querySelector('.table-responsive').style.maxHeight = '40vh';
        } else {
            form.style.display = 'none';
            this.innerHTML = htmlMaximize;
            // Mudar height da tabela para 70vh
            document.querySelector('.table-responsive').style.maxHeight = '60vh';
        }
    });

    // Adiciona o ID do container ao link de delete
    var deleteContainerModal = document.getElementById('deleteContainerModal');
    deleteContainerModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var container_ID = button.getAttribute('data-id');
        var deleteContainerLink = document.getElementById('deleteContainerLink');
        deleteContainerLink.href = "/embarques/deletar/" + container_ID;
    });
</script>


<?php
$content = ob_get_clean();
include "Components/Template.php";
?>