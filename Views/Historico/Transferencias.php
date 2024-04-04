<?php
$pageTitle = "Histórico";
ob_start();

require "Components/Header.php";
?>

<main>
    <h1 class="mt-4 mb-3"><?= $pageTitle ?> - Transferências</h1>

    <table class="table table-striped">
        <thead class="thead-dark" style="position: sticky; top: 0;">
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Origem</th>
                <th>Destino</th>
                <th>Observação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transferencias as $transferencia) : ?>
                <tr>
                    <td><?= $transferencia["code"] ?></td>
                    <td><?= $transferencia["quantity"] ?></td>
                    <td><?= $transferencia["from_stock_name"] ?></td>
                    <td><?= $transferencia["to_stock_name"] ?></td>
                    <td><?= $transferencia["observation"] ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($transferencias)) : ?>
                <tr>
                    <td colspan="5" class="text-center">Nenhuma transferência</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>