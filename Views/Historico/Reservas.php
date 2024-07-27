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

    <!-- Apresentar reservas, paginacao etc -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <h2>Reservas</h2>

        <a href="/lancamento/reserva">
            <button class="btn btn-custom">Nova Reserva</button>
        </a>
    </div>

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
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isPrevDisabled) ?> title="Voltar">
                    <i class="bi bi-arrow-left"></i>
                </button>
            </form>

            <span class="text-center">Página <?= $currentPage ?> de <?= $pageCount ?></span>

            <form method="GET">
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