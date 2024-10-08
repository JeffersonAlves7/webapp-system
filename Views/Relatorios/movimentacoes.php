<?php
$pageTitle = "Relatórios";
ob_start();

require "Components/Header.php"
?>

<main>
    <div class="d-flex w-100 justify-content-between mt-4 mb-3">
        <div class="d-flex gap-4 align-items-center">
            <button id="go-back" class="btn btn-custom" data-url="/relatorios">
                <i class="bi bi-arrow-left"></i>
            </button>

            <h1 style="margin: 0;">Relatório de Movimentações</h1>
        </div>

        <form action="/relatorios/exportarMovimentacoes" method="POST" id="formExportar" target="_blank">
            <button class="btn btn-custom" id="btn-saidas-diarias" type="submit">Exportar em massa</button>
        </form>
    </div>

    <form class="d-flex mb-3 gap-4" style="max-width: 400px;">
        <input type="month" class="form-control" id="data-movimentacao" name="dataMovimentacao" value="<?= $_GET["dataMovimentacao"] ?? date("Y-m") ?>" required>
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

    <div class="table-responsive" style="max-height: 60vh; min-height: 100px">
        <table class="table table-striped table-hover">
            <thead class="thead-dark" style="position: sticky; top: 0; z-index: 1000">
                <tr>
                    <th>Código</th>
                    <th>Saídas</th>
                    <th>Percentual</th>
                    <th>Estoque Atual</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dados)) : ?>
                    <tr>
                        <td colspan="4" class="text-center">Nenhum dado encontrado</td>
                    <?php else : ?>
                        <?php foreach ($dados as $row) : ?>
                    <tr>
                        <td><?= $row["CODIGO"] ?></td>
                        <td><?= $row["SAIDAS"] ?></td>
                        <td>
                            <span class="badge bg-<?= $row["PERCENTUAL"] >= 80 ? "success" : ($row["PERCENTUAL"] >= 15 ? "warning" : "danger") ?> " style="color: black; width: 70px; height: 30px; display: flex; align-items: center; justify-content: center">
                                <?= $row["PERCENTUAL"] ?>%
                            </span>
                        </td>
                        <td><?= $row["ESTOQUE"] ?></td>
                    </tr>
                <?php endforeach; ?>
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
        $isNextDisabled = !isset($dados) || !(count($dados) > 0) || $currentPage >= $pageCount;
        ?>

        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap" style="max-width: 250px; margin: 0 auto;">
            <form method="GET" class="d-flex align-items-center">
                <input type="hidden" name="page" value="<?= $prevPage ?>">
                <input type="hidden" name="dataMovimentacao" value="<?= $_GET["dataMovimentacao"] ?? "" ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isPrevDisabled) ?> title="Voltar">
                    <i class="bi bi-arrow-left"></i>
                </button>
            </form>

            <span class="text-center">Página <?= $currentPage ?> de <?= $pageCount ?></span>

            <form method="GET">
                <input type="hidden" name="page" value="<?= $nextPage ?>">
                <input type="hidden" name="dataMovimentacao" value="<?= $_GET["dataMovimentacao"] ?? "" ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isNextDisabled) ?> title="Avançar">
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>
    <?php endif; ?>
</main>

<script>
    const formExportar = document.getElementById("formExportar");
    const dataMovimentacao = document.getElementById("data-movimentacao");

    formExportar.addEventListener("submit", (event) => {
        event.preventDefault();

        if (!formExportar.querySelector("input[name='dataMovimentacao']")) {
            const input = document.createElement("input");
            input.type = "hidden";
            input.name = "dataMovimentacao";
            input.value = dataMovimentacao.value;

            formExportar.appendChild(input);
        }

        formExportar.submit();
    });
</script>
<?php
$content = ob_get_clean();
include "Components/Template.php";
?>