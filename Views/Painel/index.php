<?php
$pageTitle = "Painel";

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

    <!-- Link para ir para a pagina painel/usuarios e outra para ir para a pagina painel/grupos, essa aqui eh a /painel -->
    <!-- Faça os links em formato de card -->
    <div class="d-flex gap-4">
        <a href="/painel/usuarios" class="card p-3 bg-secondary" style="width: 18rem; text-decoration: none;">
            <h5 class="card-title text-white">Usuários</h5>
            <p class="card-text text-white">Gerencie os usuários do sistema</p>
        </a>
        <a href="/painel/grupos" class="card p-3 bg-secondary" style="width: 18rem; text-decoration: none;">
            <h5 class="card-title text-white">Grupos</h5>
            <p class="card-text text-white">Gerencie os grupos de usuários do sistema</p>
        </a>
    </div>
</main>

<style>
    .card {
        transition: transform .2s;
    }

    .card:hover {
        transform: scale(1.05);
    }
</style>


<?php
$content = ob_get_clean();
include "Components/Template.php";
?>