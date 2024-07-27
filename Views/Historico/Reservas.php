<?php
$pageTitle = "Histórico";
ob_start();

require "Components/Header.php";
?>

<main>
    <div class="d-flex gap-4 align-items-center">
        <button id="go-back" class="btn btn-custom">
            <i class="bi bi-arrow-left"></i>
        </button>

        <h1 class="mt-4 mb-3"><?= $pageTitle ?> - Reservas</h1>
    </div>

    <div class="d-flex gap-3">
        <a href="/historico/entradas">
            <button class="btn btn-custom">Entradas</button>
        </a>
        <a href="/historico/saidas">
            <button class="btn btn-custom">Saídas</button>
        </a>
        <a href="/historico/transferencias">
            <button class="btn btn-custom">Transferências</button>
        </a>
        <a href="/historico/devolucoes">
            <button class="btn btn-custom">Devoluções</button>
        </a>
        <button class="btn btn-custom active">Reservas</button>
    </div>

    <!-- Adicionando uma classe adicional para controle de responsividade -->
    <form class="d-flex flex-wrap gap-4 my-4" id="form-filtro">
        <!-- Campos de entrada -->

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

        <!-- Botões, agora com ícones e texto ajustado -->
        <button type="submit" class="btn btn-custom" title="Pesquisar">
            <i class="bi bi-search"></i>
        </button>

        <button type="button" class="btn btn-custom" id="exportar">
            <i class="bi bi-file-earmark-excel"></i> Exportar
        </button>
    </form>

    <div class="table-responsive" style="max-height: 60vh; min-height: 100px;">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Estoque</th>
                    <th>Data</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $reserva) : ?>
                    <tr>
                        <td><?= $reserva["code"] ?></td>
                        <td><?= $reserva["quantity"] ?></td>
                        <td><?= $reserva["stock_name"] ?></td>
                        <td><?= date("d/m/Y H:i", strtotime($reserva["created_at"])) ?></td>
                        <td><?= $reserva["observation"] ?></td>
                    </tr>
                <?php endforeach; ?>
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
        $isNextDisabled = !isset($reservas) || !count($reservas) || $currentPage >= $pageCount;
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
</main>

<script>
    const exportar = document.getElementById("exportar");
    exportar.addEventListener("click", () => {
        const dataInicio = document.getElementById("data-inicio").value;
        const dataFim = document.getElementById("data-fim").value;
        const produto = document.getElementById("produto").value;

        const url = new URL(window.location.href);
        url.searchParams.set("action", "exportarReservas");
        url.searchParams.set("data-inicio", dataInicio);
        url.searchParams.set("data-fim", dataFim);
        url.searchParams.set("code", produto);

        window
            .open(url.href, "_blank")
            .focus();
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>