<?php
$pageTitle = "Relatórios";
ob_start();
require "Components/Header.php"
?>

<main>
    <div class="d-flex gap-4 align-items-center">
        <button id="go-back" class="btn btn-custom" data-url="/">
            <i class="bi bi-arrow-left"></i>
        </button>

        <h1 class="mt-4 mb-3"><?= $pageTitle ?></h1>
    </div>


    <div class="d-flex gap-4 flex-wrap">
        <div class="d-flex gap-4">
            <a href="/relatorios/saidasDiarias" class="card p-3 bg-secondary" style="width: 18rem; text-decoration: none;">
                <h5 class="card-title text-white">Saídas Diárias</h5>
                <p class="card-text text-white">Acessar Relatório de saídas Diárias</p>
            </a>
        </div>

        <div class="d-flex gap-4">
            <a href="/relatorios/estoqueMinimo" class="card p-3 bg-secondary" style="width: 18rem; text-decoration: none;">
                <h5 class="card-title text-white">Estoque Mínimo</h5>
                <p class="card-text text-white">Acessar Relatório de SKU's com estoque mínimo atingido</p>
            </a>
        </div>

        <div class="d-flex gap-4">
            <a href="/relatorios/movimentacoes" class="card p-3 bg-secondary" style="width: 18rem; text-decoration: none;">
                <h5 class="card-title text-white">Movimentações</h5>
                <p class="card-text text-white">Acessar Relatório de Movimentações</p>
            </a>
        </div>

        <div class="d-flex gap-4">
            <a href="/relatorios/comparativoDeVendas" class="card p-3 bg-secondary" style="width: 18rem; text-decoration: none;">
                <h5 class="card-title text-white">Comparativo de Vendas</h5>
                <p class="card-text text-white">
                    Acessar Relatório de Comparativo de Vendas
                </p>
            </a>
        </div>

        <div class="d-flex gap-4">
            <a href="/relatorios/entradas" class="card p-3 bg-secondary" style="width: 18rem; text-decoration: none;">
                <h5 class="card-title text-white">Entradas</h5>
                <p class="card-text text-white">Acessar Relatório de Entradas dos últimos 12 meses</p>
            </a>
        </div>
    </div>
</main>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>