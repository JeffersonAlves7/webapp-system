<?php
$pageTitle = "Embarques";
ob_start();

require "Components/Header.php";

$search = isset($_GET['search']) ? $_GET['search'] : "";
?>

<style>
    table {
        table-layout: fixed;
    }
</style>

<main>
    <h1 class="mt-4 mb-3"><?= $pageTitle ?></h1>

    <form method="get" class="mb-3 row g-3">
        <div class="col-md-3">
            <label for="search" class="form-label sr-only">Código do container</label>
            <input type="text" class="form-control" name="search" placeholder="Ex.: LT-001" value="<?= $search ?>">
        </div>

        <div class="col-md-3">
            <label for="start_date" class="form-label sr-only">Data de início</label>
            <input type="date" class="form-control" name="start_date" placeholder="Data de início" value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : "" ?>">
        </div>

        <div class="col-md-3">
            <label for="end_date" class="form-label sr-only">Data de término</label>
            <input type="date" class="form-control" name="end_date" placeholder="Data de término" value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : "" ?>">
        </div>

        <!-- Filtro por código do produto -->
        <div class="col-md-3">
            <label for="product_code" class="form-label">Código ou Ean do produto</label>
            <input type="text" class="form-control" name="product_code" placeholder="Ex.: AB1445" value="<?= isset($_GET['product_code']) ? $_GET['product_code'] : "" ?>">
        </div>

        <div class="col-md-3 d-flex justify-content-start align-items-end">
            <button type="submit" class="btn btn-custom">
                <i class="bi bi-search"></i> Buscar
            </button>
        </div>
    </form>

    <!-- Tabela -->
    <form method="get" class="table-responsive" style="max-height: 40vh; min-height: 150px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark" style="position: sticky; top: 0; z-index: 1000">
                <tr>
                    <th><i class="bi bi-tag"></i> Código</th>
                    <th><i class="bi bi-check2"></i> Conferidos</th>
                    <th><i class="bi bi-calendar-date"></i> Data</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($containers) && count($containers) > 0) {
                    foreach ($containers as $row) {
                ?>
                        <tr>
                            <td>
                                <a href="/embarques/produtos/<?= $row['ID'] ?>">
                                    <i class='bi bi-zoom-in' style="margin-right: .5rem;"></i> <?= $row['name'] ?>
                                </a>
                            </td>
                            <td><?= $row['conferidos'] ?> / <?= $row['total'] ?></td>
                            <td><?= date('d/m/Y h:i:s', strtotime($row['created_at'])) ?></td>
                            <td>
                                <?php if ($row['conferidos'] == $row['total']) : ?>
                                    Conferido <i class='bi bi-check2 text-success'></i>
                                <?php else : ?>
                                    <a href="/embarques/conferir/<?= $row['ID'] ?>">
                                        <button type='button' class='btn btn-custom'>
                                            Conferir
                                            <i class='bi bi-check2 text-success'></i>
                                        </button>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type='button' class='btn' data-bs-toggle='modal' data-bs-target='#deleteContainerModal' data-id='<?= $row['ID'] ?>'>
                                    <i class='bi bi-trash text-danger'></i>
                                </button>
                            </td>
                        </tr>
                    <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan='10'>Nenhum container encontrado.</td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
        <button type="submit" hidden></button>
    </form>

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
        $isNextDisabled = !isset($containers) || count($containers) <= 0 || $currentPage >= $pageCount;
        ?>

        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap" style="max-width: 250px; margin: 0 auto;">
            <form method="GET" class="d-flex align-items-center">
                <input type="hidden" name="page" value="<?= $prevPage ?>">
                <input type="hidden" name="search" value="<?= $search ?>">
                <input type="hidden" name="start_date" value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : "" ?>">
                <input type="hidden" name="end_date" value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : "" ?>">
                <input type="hidden" name="product_code" value="<?= isset($_GET['product_code']) ? $_GET['product_code'] : "" ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isPrevDisabled) ?> title="Voltar">
                    <i class="bi bi-arrow-left"></i>
                </button>
            </form>

            <span class="text-center">Página <?= $currentPage ?> de <?= $pageCount ?></span>

            <form method="GET">
                <input type="hidden" name="page" value="<?= $nextPage ?>">
                <input type="hidden" name="search" value="<?= $search ?>">
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
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>