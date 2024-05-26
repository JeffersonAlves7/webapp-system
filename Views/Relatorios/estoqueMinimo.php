<?php
$pageTitle = "Relatórios";
ob_start();

require "Components/Header.php"
?>

<main>
    <div class="d-flex w-100 justify-content-between mt-4 mb-3">
        <div class="d-flex gap-4 align-items-center">
            <button id="go-back" class="btn btn-custom">
                <i class="bi bi-arrow-left"></i>
            </button>

            <h1 style="margin: 0;">Relatório de Estoque Mínimo Atingido</h1>
        </div>

        <button class="btn btn-custom" id="btn-saidas-diarias">Exportar em massa</button>
    </div>
    
    <p>
        Porcentagem de alerta: <span id="porcentagem-alerta" style="font-weight: bold;">
            <?= isset($_COOKIE["porcentagemParaAlerta"]) && !empty($_GET["porcentagemParaAlerta"]) ? $_GET["porcentagemParaAlerta"] : "50" ?> %
        </span>
    </p>

    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Quantidade De Entrada</th>
                    <th>Saldo Atual</th>
                    <th>Quantidade de Alerta</th>
                </tr>
            </thead>

            <!-- TODO: Adicionar tbody aqui embaixo -->
            <tbody id="tbody-estoque-minimo">
                <?php while ($row = $dados->fetch_assoc()) : ?>
                    <tr>
                        <td><?= $row["CODIGO"] ?></td>
                        <td><?= $row["ENTRADA"] ?></td>
                        <td><?= $row["SALDO"] ?></td>
                        <td><?= $row["QUANTIDADE DE ALERTA"] ?></td>
                    </tr>
                <?php endwhile; ?> 
        </table>
</main>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>