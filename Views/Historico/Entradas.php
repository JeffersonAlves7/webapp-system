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

        <h1 class="mt-4 mb-3"><?= $pageTitle ?> - Entradas</h1>
    </div>

    <div class="d-flex gap-3">
        <button class="btn btn-custom active">Entradas</button>
        <a href="/historico/saidas">
            <button class="btn btn-custom">Saídas</button>
        </a>
        <a href="/historico/transferencias">
            <button class="btn btn-custom">Transferências</button>
        </a>
        <a href="/historico/devolucoes">
            <button class="btn btn-custom">Devoluções</button>
        </a>
        <a href="/historico/reservas">
            <button class="btn btn-custom">Reservas</button>
        </a>
    </div>
</main>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>