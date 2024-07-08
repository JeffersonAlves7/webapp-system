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
            <input type="text" id="produto" class="form-control" name="produto" value="<?= $_GET["code"] ?? "" ?>">
        </div>

        <!-- Botões, agora com ícones e texto ajustado -->
        <button type="submit" class="btn btn-custom" title="Pesquisar">
            <i class="bi bi-search"></i>
        </button>

        <button type="button" class="btn btn-custom" id="exportar">
            <i class="bi bi-file-earmark-excel"></i> Exportar
        </button>
    </form>

    <table class="table table-striped">
        <thead class="thead-dark" style="position: sticky; top: 0; z-index: 1000">
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Origem</th>
                <th>Destino</th>
                <th>Data</th>
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
                    <td><?= date("d/m/Y H:i", strtotime($transferencia["created_at"])) ?></td>
                    <td><?= $transferencia["observation"] ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($transferencias)) : ?>
                <tr>
                    <td class="text-center" colspan="6">
                        Nenhuma transferência
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<script>
    const exportar = document.getElementById("exportar");
    exportar.addEventListener("click", () => {

        const dataInicio = document.getElementById("data-inicio").value;
        const dataFim = document.getElementById("data-fim").value;
        const produto = document.getElementById("produto").value;

        const newLink = document.createElement("a");
        newLink.href = `?&action=exportarTransferencias&data-inicio=${dataInicio}&data-fim=${dataFim}&code=${produto}`;
        newLink.target = "_blank";
        newLink.click();
        newLink.remove();
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>