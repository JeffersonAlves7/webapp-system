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
</main>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>