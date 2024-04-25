<?php
$pageTitle = "Embarques";
ob_start();

require "Components/Header.php";

$search = isset($_GET['search']) ? $_GET['search'] : "";
?>
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

        <div class="col-md-3 d-flex justify-content-start align-items-end">
            <button type="submit" class="btn btn-custom">
                <i class="bi bi-search"></i> Buscar
            </button>
        </div>
    </form>

    <!-- Tabela -->
    <form method="get" class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark" style="position: sticky; top: 0;">
                <tr>
                    <th><i class="bi bi-tag"></i> Código</th>
                    <th><i class="bi bi-check2"></i> Conferidos</th>
                    <th><i class="bi bi-calendar-date"></i> Data</th>
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
                            <td><?= date('d/m/Y h:i:s' , strtotime($row['created_at'])) ?></td>
                            <!-- Importar arquivo -->
                            <td>
                                <a href="/embarques/deletar/<?= $row['ID'] ?>">
                                    <button type='button' class='btn'>
                                        <i class='bi bi-trash text-danger'></i>
                                    </button>
                                </a>
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

    <form method="post" enctype="multipart/form-data" class="mb-3" action="/embarques/importar">
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

    <?php include_once "Components/StatusMessage.php"; ?>
</main>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>