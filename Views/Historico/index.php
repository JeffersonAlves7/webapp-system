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

        <h1 class="mt-4 mb-3"><?= $pageTitle ?></h1>
    </div>

    <div class="d-flex gap-4">
        <a href="/historico/transferencias" class="card p-3 bg-secondary" style="width: 18rem; text-decoration: none;">
            <h5 class="card-title text-white">Transferências</h5>
            <p class="card-text text-white">Histórico de transferências</p>
        </a>
    </div>
</main>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>