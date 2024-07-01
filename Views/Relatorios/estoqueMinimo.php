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

            <h1 style="margin: 0;">Relatório de Estoque Mínimo Atingido</h1>
        </div>

        <button class="btn btn-custom" id="btn-saidas-diarias">Exportar em massa</button>
    </div>

    <p>
        Porcentagem de alerta: <span id="porcentagem-alerta" style="font-weight: bold;">
            <?= isset($_COOKIE["porcentagemParaAlerta"]) && !empty($_GET["porcentagemParaAlerta"]) ? $_GET["porcentagemParaAlerta"] : "50" ?> %
        </span>
    </p>

    <div class="table-responsive mb-3" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Quantidade De Entrada</th>
                    <th>Saldo Atual</th>
                    <th>Quantidade de Alerta</th>
                </tr>
            </thead>

            <!-- TODO: Adicionar tbody aqui embaixo -->
            <tbody id="tbody-estoque-minimo">
                <?php foreach ($dados as $row) : ?>
                    <tr>
                        <td><?= $row["CODIGO"] ?></td>
                        <td><?= $row["ENTRADA"] ?></td>
                        <td><?= $row["SALDO"] ?></td>
                        <td><?= $row["QUANTIDADE DE ALERTA"] ?></td>
                    </tr>
                <?php endforeach; ?>
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
        $isNextDisabled = !isset($dados) || !count($dados) > 0 || $currentPage >= $pageCount;
        ?>

        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap" style="max-width: 250px; margin: 0 auto;">
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
    <?php endif; ?>
</main>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>