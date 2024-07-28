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

        <h1 class="mt-4 mb-3"><?= $pageTitle ?> - Transferências</h1>
    </div>

    <div class="d-flex gap-3">
        <a href="/historico/entradas">
            <button class="btn btn-custom">Entradas</button>
        </a>
        <a href="/historico/saidas">
            <button class="btn btn-custom">Saídas</button>
        </a>
        <button class="btn btn-custom active">Transferências</button>
        <a href="/historico/devolucoes">
            <button class="btn btn-custom">Devoluções</button>
        </a>
        <a href="/historico/reservas">
            <button class="btn btn-custom">Reservas</button>
        </a>
    </div>

    <?php require "Views/Historico/Operacoes.php"; ?>
</main>


<?php
$content = ob_get_clean();
include "Components/Template.php";
?>