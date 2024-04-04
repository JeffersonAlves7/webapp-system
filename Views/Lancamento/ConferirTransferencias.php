<?php
$pageTitle = "Incluir Lançamento";
ob_start();

require "Components/Header.php";
?>

<main class="container position-relative">
    <div class="d-flex gap-4 align-items-center">
        <button id="go-back" class="btn btn-custom">
            <i class="bi bi-arrow-left"></i>
        </button>

        <h1 class="mt-4 mb-3">Conferir transferências</h1>
    </div>

    <?php require "Components/StatusMessage.php" ?>

    <table class="table table-striped">
        <thead class="thead-dark" style="position: sticky; top: 0;">
            <tr>
                <th>
                    <input type="checkbox" id="selecionar-todos">
                    <label for="selecionar-todos">Selecionar</label>
                </th>
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
                    <td><input type="checkbox" name="transference-id" value="<?= $transferencia["ID"] ?>"></td>
                    <td><?= $transferencia["code"] ?></td>
                    <td><?= $transferencia["quantity"] ?></td>
                    <td><?= $transferencia["from_stock_name"] ?></td>
                    <td><?= $transferencia["to_stock_name"] ?></td>
                    <td><?= $transferencia["observation"] ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($transferencias)) : ?>
                <tr>
                    <td colspan="6" class="text-center">Nenhuma transferência pendente</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($transferencias)) : ?>
        <div class="d-flex gap-4">
            <form action="/lancamento/confirmarTransferencias" method="post">
                <button type="submit" class="btn btn-primary">Confirmar</button>
            </form>
            <form action="/lancamento/cancelarTransferencias" method="post">
                <button type="submit" class="btn btn-danger">Cancelar</button>
            </form>
        </div>
    <?php endif; ?>
</main>

<script>
    window.addEventListener('load', (event) => {
        const checkboxes = document.querySelectorAll('input[name="transference-id"]');
        const selectAllCheckbox = document.getElementById('selecionar-todos');

        const selectedIds = [];

        // Selecionar todos os checkboxes ao clicar no checkbox de seleção mas ignorar duplicadas ao dar push
        selectAllCheckbox.addEventListener('change', (event) => {
            checkboxes.forEach(checkbox => {
                checkbox.checked = event.target.checked;
                if (event.target.checked) {
                    if (!selectedIds.includes(checkbox.value)) {
                        selectedIds.push(checkbox.value);
                    }
                } else {
                    const index = selectedIds.indexOf(checkbox.value);
                    selectedIds.splice(index, 1);
                }
            });
        });


        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selectedIds.push(checkbox.value);
            }

            checkbox.addEventListener('change', (event) => {
                const id = event.target.value;
                if (event.target.checked) {
                    selectedIds.push(id);
                } else {
                    const index = selectedIds.indexOf(id);
                    selectedIds.splice(index, 1);
                }
            });
        });

        const form = document.querySelector('form[action="/lancamento/confirmarTransferencias"]');
        form.addEventListener('submit', (event) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'transference-ids';
            input.value = JSON.stringify(selectedIds);
            form.appendChild(input);
        });

        const cancelForm = document.querySelector('form[action="/lancamento/cancelarTransferencias"]');
        cancelForm.addEventListener('submit', (event) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'transference-ids';
            input.value = JSON.stringify(selectedIds);
            cancelForm.appendChild(input);
        });
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>