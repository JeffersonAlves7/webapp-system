<?php
$pageTitle = "Relatório de Produtos Sem Saída";
ob_start();

require "Components/Header.php"; // Certifique-se de que o caminho para o Header está correto

/**
 * Função para truncar strings e adicionar reticências.
 * @param string $string A string a ser truncada.
 * @param int $limit O limite de caracteres.
 * @return string A string truncada.
 */
function truncateString($string, $limit)
{
    if (strlen($string) > $limit) {
        return substr($string, 0, $limit) . '...';
    }
    return $string;
}
?>
<main>
    <h1 class="mt-4 mb-3">Relatório de Produtos Sem Saída</h1>

    <div style="max-width: 100%; overflow-x: auto;">
        <form method="get" class="d-flex gap-3 mt-3 mb-3 flex-wrap align-items-end" id="reportFiltersForm">
            <label>
                Código:
                <input type="search" class="form-control" name="code" id="inputCodigo" placeholder="Ex.: BT-001"
                    value="<?= htmlspecialchars($_GET["code"] ?? '') ?>">
            </label>

            <label>
                Importadora:
                <select class="form-select" name="importer" id="inputImportadora">
                    <option value="">Selecione uma opção</option>
                    <option value="ATTUS" <?= (isset($_GET["importer"]) && $_GET["importer"] == "ATTUS") ? "selected" : "" ?>>ATTUS</option>
                    <option value="ATTUS_BLOOM" <?= (isset($_GET["importer"]) && $_GET["importer"] == "ATTUS_BLOOM") ? "selected" : "" ?>>ATTUS_BLOOM</option>
                    <option value="ALPHA_YNFINITY" <?= (isset($_GET["importer"]) && $_GET["importer"] == "ALPHA_YNFINITY") ? "selected" : "" ?>>ALPHA_YNFINITY</option>
                </select>
            </label>

            <label>
                Data Início:
                <input type="date" class="form-control" name="startDate" id="inputStartDate"
                    value="<?= htmlspecialchars($startDate) ?>">
            </label>

            <label>
                Data Fim:
                <input type="date" class="form-control" name="endDate" id="inputEndDate"
                    value="<?= htmlspecialchars($endDate) ?>">
            </label>

            <div class="d-flex align-items-end gap-2">
                <button type="submit" class="btn bg-quaternary" title="Filtrar" style="height: 40px;width: 50px;">
                    <i class="bi bi-search"></i>
                </button>
                <a href="/relatorios/sem-saida" class="btn btn-secondary" title="Limpar Filtros"
                    style="height: 40px;width: 50px;">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>

            <div>
                <button type="button" class="btn btn-custom form-control" id="exportar">
                    <i class="bi bi-file-earmark-excel"></i> Exportar
                </button>
            </div>
        </form>
    </div>

    <?php
    // Calcular a representatividade apenas se totalProductsInGalpaoUnfiltered for maior que 0 para evitar divisão por zero
    $representatividade = 0;
    if (isset($totalProdutos) && isset($totalProductsInGalpaoUnfiltered) && $totalProductsInGalpaoUnfiltered > 0) {
        $representatividade = ($totalProdutos / $totalProductsInGalpaoUnfiltered) * 100;
    }
    ?>
    <p>Representatividade dos produtos sem saída:
        <strong><?= number_format($representatividade, 2, ',', '.') ?>%</strong>
    </p>


    <div class="table-responsive" style="max-height: 65vh; min-height: 200px">
        <table class="table table-striped">
            <thead class="thead-dark" style="position: sticky; top: 0; z-index: 1000">
                <tr>
                    <th>CÓDIGO</th>
                    <th>DESCRIÇÃO</th>
                    <th>IMPORTADORA</th>
                    <th>SALDO ATUAL (GALPÃO)</th>
                    <th>ÚLTIMA ENTRADA (QTDE)</th>
                    <th>DATA DA ÚLTIMA ENTRADA</th>
                    <th>DIAS EM ESTOQUE</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($produtos) && count($produtos) > 0): ?>
                    <?php foreach ($produtos as $produto): ?>
                        <tr>
                            <td>
                                <a href='/produtos/byId/<?= htmlspecialchars($produto["ID"] ?? '') ?>' title='Ver mais'>
                                    <?= htmlspecialchars($produto["code"] ?? '') ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars(truncateString($produto["description"] ?? '', 15)) ?></td>
                            <td><?= htmlspecialchars($produto["importer"] ?? '') ?></td>
                            <td>
                                <?php
                                $galpaoQuantity = 0;
                                // Loop through quantity_in_stock to find quantity for stock_ID 1 (galpao)
                                foreach (($produto["quantity_in_stock"] ?? []) as $stockItem) {
                                    if (isset($stockItem['stock_ID']) && $stockItem['stock_ID'] == 1) {
                                        $galpaoQuantity = $stockItem['quantity'];
                                        break;
                                    }
                                }
                                echo htmlspecialchars($galpaoQuantity);
                                ?>
                            </td>
                            <td><?= htmlspecialchars($produto["products_in_container"][0]["quantity"] ?? 0) ?></td>
                            <td>
                                <?php
                                $dataEntrada = $produto["products_in_container"][0]["updated_at"] ?? null;
                                if (!empty($dataEntrada)) {
                                    // Remove a parte dos milissegundos antes de converter para timestamp
                                    $dataEntrada = explode('.', $dataEntrada)[0];
                                    echo date("d/m/Y", strtotime($dataEntrada));
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $diasEmEstoque = 0;
                                if (!empty($dataEntrada)) { // Use the cleaned $dataEntrada from above
                                    $timestampEntrada = strtotime($dataEntrada);
                                    $timestampAtual = time();
                                    $diffSeconds = $timestampAtual - $timestampEntrada;
                                    $diasEmEstoque = floor($diffSeconds / (60 * 60 * 24));
                                }
                                echo htmlspecialchars($diasEmEstoque) . " dia(s)";
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 1rem;">Nenhum produto sem saída encontrado com
                            os filtros aplicados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="bg-light" style="position: sticky; bottom: -5px; z-index: 1000;">
                <tr>
                    <td colspan="7">
                        <?php if (isset($totalProdutos)): ?>
                            <strong>Total de produtos: <?= $totalProdutos ?></strong>
                        <?php endif; ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <?php if ($pageCount > 1): ?>
        <?php
        function isButtonDisabled($condition)
        {
            return $condition ? 'disabled' : '';
        }

        $currentPage = $_GET['page'] ?? 1;
        $prevPage = $currentPage - 1;
        $nextPage = $currentPage + 1;
        $isPrevDisabled = intval($currentPage) <= 1;
        $isNextDisabled = intval($currentPage) >= $pageCount;
        ?>

        <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap mt-2"
            style="max-width: 300px; margin: 0 auto;">
            <form method="GET" class="d-flex align-items-center">
                <input type="hidden" name="page" value="<?= $prevPage ?>">
                <input type="hidden" name="code" value="<?= htmlspecialchars($_GET["code"] ?? '') ?>">
                <input type="hidden" name="importer" value="<?= htmlspecialchars($_GET["importer"] ?? '') ?>">
                <input type="hidden" name="startDate" value="<?= htmlspecialchars($startDate) ?>">
                <input type="hidden" name="endDate" value="<?= htmlspecialchars($endDate) ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isPrevDisabled) ?> title="Voltar">
                    <i class="bi bi-arrow-left"></i>
                </button>
            </form>

            <span class="text-center">Página <?= $currentPage ?> de <?= $pageCount ?></span>

            <form method="GET">
                <input type="hidden" name="page" value="<?= $nextPage ?>">
                <input type="hidden" name="code" value="<?= htmlspecialchars($_GET["code"] ?? '') ?>">
                <input type="hidden" name="importer" value="<?= htmlspecialchars($_GET["importer"] ?? '') ?>">
                <input type="hidden" name="startDate" value="<?= htmlspecialchars($startDate) ?>">
                <input type="hidden" name="endDate" value="<?= htmlspecialchars($endDate) ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isNextDisabled) ?> title="Avançar">
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>
    <?php endif; ?>

    <?php include_once "Components/StatusMessage.php"; ?>
</main>

<style>
    /* Estilos existentes ou específicos para esta view */
    .container-col {
        max-width: 150px;
        overflow: auto;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    /* Estilo para o botão de exportar, se necessário */
    .btn-custom {
        background-color: #28a745;
        /* Um verde bonito, por exemplo */
        color: white;
        height: 40px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .btn-custom:hover {
        background-color: #218838;
        color: white;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const exportarButton = document.getElementById("exportar");
        if (exportarButton) {
            exportarButton.addEventListener("click", () => {
                const inputCodigo = document.getElementById("inputCodigo").value;
                const inputImportadora = document.getElementById("inputImportadora").value;
                const inputStartDate = document.getElementById("inputStartDate").value;
                const inputEndDate = document.getElementById("inputEndDate").value;

                // Construa a URL para o seu endpoint de exportação no controller PHP
                const url = new URL(window.location.origin + "/relatorios/exportarSemSaida");

                // Adicione os parâmetros de filtro à URL
                if (inputCodigo) {
                    url.searchParams.set("code", inputCodigo);
                }
                if (inputImportadora) {
                    url.searchParams.set("importer", inputImportadora);
                }
                if (inputStartDate) {
                    url.searchParams.set("startDate", inputStartDate);
                }
                if (inputEndDate) {
                    url.searchParams.set("endDate", inputEndDate);
                }

                // Abre a URL em uma nova aba/janela para iniciar o download
                window.open(url.href, "_blank").focus();
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php"; // Certifique-se de que o caminho para o Template está correto
?>>