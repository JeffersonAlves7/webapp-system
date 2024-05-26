<?php
$pageTitle = "Relatórios";
ob_start();

require "Components/Header.php"
?>


<main>


    <div class="d-flex w-100 justify-content-between mt-4 mb-3">
        <div class="d-flex gap-4 align-items-center">
            <button id="go-back" class="btn btn-custom">
                <i class="bi bi-arrow-left"></i>
            </button>

            <h1 style="margin: 0;">Relatório de Movimentações</h1>
        </div>

        <button class="btn btn-custom" id="btn-saidas-diarias">Exportar em massa</button>
    </div>

    <form class="d-flex mb-3 gap-4" style="max-width: 400px;">
        <input type="month" class="form-control" id="data-movimentacao" name="data-movimentacao" value="<?= date("Y-m") ?>">
        <button class="btn btn-custom" id="btn-pesquisar">Pesquisar</button>
    </form>

    <div class="d-flex gap-4 mb-4 mt-4">
        <div class="d-flex gap-2 align-items-center">
            <div class="bg-success rounded-circle" style="width: 20px; height: 20px;"></div>
            <span>Curva A: Percentual acima de 80%</span>
        </div>

        <div class="d-flex gap-2 align-items-center">
            <div class="bg-warning rounded-circle" style="width: 20px; height: 20px;"></div>
            <span>Curva B: Percentual entre 15% e 80%</span>
        </div>

        <div class="d-flex gap-2 align-items-center">
            <div class="bg-danger rounded-circle" style="width: 20px; height: 20px;"></div>
            <span>Curva C: Percentual abaixo de 15%</span>
        </div>
    </div>


    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Saídas</th>
                    <th>Percentual</th>
                    <th>Estoque Atual</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = $dados->fetch_assoc()) {
                ?>
                    <tr>
                        <td><?= $row["CODIGO"] ?></td>
                        <td><?= $row["SAIDAS"] ?></td>
                        <td>
                            <span class="badge bg-<?= $row["PERCENTUAL"] >= 80 ? "success" : ($row["PERCENTUAL"] >= 15 ? "warning" : "danger") ?> p-3">
                                <?= $row["PERCENTUAL"] ?>%
                            </span>
                        </td>
                        <td><?= $row["ESTOQUE"] ?></td>
                    </tr>
                <?php
                }
                ?>
        </table>
    </div>
</main>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>