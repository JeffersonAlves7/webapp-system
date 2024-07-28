<form class="d-flex flex-wrap gap-4 my-4" id="form-filtro">
    <div class="input-group" style="max-width: 300px;">
        <label for="data-inicio" class="input-group-text">Data de início</label>
        <input type="date" id="data-inicio" class="form-control" name="data-inicio" value="<?= $_GET["data-inicio"] ?? "" ?>">
    </div>

    <div class="input-group" style="max-width: 300px;">
        <label for="data-fim" class="input-group-text">Data de fim</label>
        <input type="date" id="data-fim" class="form-control" name="data-fim" value="<?= $_GET["data-fim"] ?? "" ?>">
    </div>

    <div class="input-group" style="max-width: 300px;">
        <label for="produto" class="input-group-text">Produto</label>
        <input type="text" id="produto" class="form-control" name="code" value="<?= $_GET["code"] ?? "" ?>">
    </div>

    <button type="submit" class="btn btn-custom" title="Pesquisar">
        <i class="bi bi-search"></i>
    </button>

    <button type="button" class="btn btn-custom" id="exportar">
        <i class="bi bi-file-earmark-excel"></i> Exportar
    </button>
</form>

<div class="table-responsive" style="max-height: 60vh; min-height: 100px;">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Origem</th>
                <th>Destino</th>
                <th>Data</th>
                <th>Observação</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($transactions as $devolucao) : ?>
                <tr>
                    <td><?= $devolucao["code"] ?></td>
                    <td><?= $devolucao["quantity"] ?></td>
                    <td><?= $devolucao["from_stock_name"] ?></td>
                    <td><?= $devolucao["to_stock_name"] ?></td>
                    <td><?= date("d/m/Y H:i", strtotime($devolucao["created_at"])) ?></td>
                    <td style="max-width: 20vw; overflow: hidden; text-overflow: ellipsis; white-space: wrap;">
                        <?= $devolucao["observation"] ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($transactions)) : ?>
                <tr>
                    <td colspan="6" class="text-center">Nenhuma devolução encontrada</td>
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
    $isNextDisabled = !isset($transactions) || !count($transactions) || $currentPage >= $pageCount;
    ?>

    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap" style="max-width: 250px; margin: 0 auto;">
        <form method="GET" class="d-flex align-items-center">
            <input type="hidden" name="page" value="<?= $prevPage ?>">
            <input type="hidden" name="code" value="<?= $_GET["code"] ?? "" ?>">
            <input type="hidden" name="data-inicio" value="<?= $_GET["data-inicio"] ?? "" ?>">
            <input type="hidden" name="data-fim" value="<?= $_GET["data-fim"] ?? "" ?>">
            <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isPrevDisabled) ?> title="Voltar">
                <i class="bi bi-arrow-left"></i>
            </button>
        </form>

        <span class="text-center">Página <?= $currentPage ?> de <?= $pageCount ?></span>

        <form method="GET">
            <input type="hidden" name="page" value="<?= $nextPage ?>">
            <input type="hidden" name="code" value="<?= $_GET["code"] ?? "" ?>">
            <input type="hidden" name="data-inicio" value="<?= $_GET["data-inicio"] ?? "" ?>">
            <input type="hidden" name="data-fim" value="<?= $_GET["data-fim"] ?? "" ?>">
            <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isNextDisabled) ?> title="Avançar">
                <i class="bi bi-arrow-right"></i>
            </button>
        </form>
    </div>
<?php endif; ?>

<script>
    const exportar = document.getElementById("exportar");
    exportar.addEventListener("click", () => {
        const dataInicio = document.getElementById("data-inicio").value;
        const dataFim = document.getElementById("data-fim").value;
        const produto = document.getElementById("produto").value;

        const url = new URL(window.location.href);
        url.searchParams.set("action", "exportar");
        url.searchParams.set("data-inicio", dataInicio);
        url.searchParams.set("data-fim", dataFim);
        url.searchParams.set("code", produto);

        window
            .open(url.href, "_blank")
            .focus();

        url.searchParams.delete("action");
        url.searchParams.delete("data-inicio");
        url.searchParams.delete("data-fim");
        url.searchParams.delete("code");
    });
</script>