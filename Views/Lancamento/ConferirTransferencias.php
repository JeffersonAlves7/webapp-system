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


    <?php
    function generateDescription($description)
    {
        if (!isset($description)) {
            return '';
        }

        $size = 20;

        $shortDescription = substr($description, 0, $size);
        if (strlen($description) > $size) {
            $shortDescription .= '... <a href="#" class="toggle-description" data-full-description="' . htmlspecialchars($description) . '">Ver mais</a>';
        }

        return $shortDescription;
    }
    ?>


    <div class="table-responsive">
        <table class="table table-striped">
            <thead class="thead-dark" style="position: sticky; top: 0;">
                <tr>
                    <th>
                        <input type="checkbox" id="selecionar-todos">
                        <label for="selecionar-todos">Selecionar</label>
                    </th>
                    <th>Produto</th>
                    <th>Descrição</th>
                    <th>Quantidade</th>
                    <th>Origem</th>
                    <th>Destino</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transferencias as $transferencia) : ?>
                    <tr id=<?= "row-" . $transferencia['ID'] ?>>
                        <td><input type="checkbox" name="is-checked" value="<?= $transferencia["ID"] ?>"></td>
                        <td><?= $transferencia["code"] ?></td>
                        <td><?= generateDescription($transferencia["description"]) ?></td>
                        <td><?= $transferencia["quantity"] ?></td>
                        <td>
                            <div class="d-flex" style="width: 150px;">
                                <input type="number" class="form-control" placeholder="0" min="0" name="quantity_received" required data-quantity-expected="<?= $transferencia['quantity'] ?>" data-id="<?= $transferencia['ID'] ?>">
                                <button class="btn btn-outline-secondary fill-button" title="Preencher com a quantidade esperada" data-id="<?= $transferencia['ID'] ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 3.5a.5.5 0 0 1 .5.5v2.793l1.354-1.353a.5.5 0 0 1 .708.708l-2.147 2.146a.5.5 0 0 1-.708 0L5.94 6.646a.5.5 0 0 1 .708-.708L7.5 6.793V4a.5.5 0 0 1 .5-.5z" />
                                        <path fill-rule="evenodd" d="M8 4.5a.5.5 0 0 0-.5.5V7.207l-1.354-1.353a.5.5 0 0 0-.708.708l2.147 2.146a.5.5 0 0 0 .708 0l2.147-2.146a.5.5 0 0 0-.708-.708L8.5 7.207V5a.5.5 0 0 0-.5-.5z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
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
    </div>

    <?php if (!empty($transferencias)) : ?>
        <div class="d-flex gap-4">
            <form action="/lancamento/confirmarTransferencias" method="post" id="form-confirm">
                <button type="submit" class="btn btn-primary">Confirmar</button>
            </form>
            <form action="/lancamento/cancelarTransferencias" method="post" id="form-cancel">
                <button type="submit" class="btn btn-danger">Cancelar</button>
            </form>
        </div>
    <?php endif; ?>
</main>

<script>
    window.onload = () => {
        const checkboxes = document.querySelectorAll('input[name="is-checked"]');
        const selectAllCheckbox = document.getElementById('selecionar-todos');

        const checkAll = () => {
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        selectAllCheckbox.addEventListener('change', checkAll);

        const quantityInputs = document.querySelectorAll('input[name=quantity_received]');
        quantityInputs.forEach(input => {
            input.addEventListener('input', () => {
                if (input.value == input.dataset.quantityExpected) {
                    input.classList.remove('border-danger');
                    input.classList.add('border-success');
                } else {
                    input.classList.remove('border-success');
                    input.classList.add('border-danger');
                }
            });
        });

        function fillWithExpectedQuantity(event) {
            const id = event.currentTarget.dataset.id;

            const quantityInput = document.querySelector(`#row-${id} input[name=quantity_received]`);
            quantityInput.value = quantityInput.dataset.quantityExpected;

            quantityInput.dispatchEvent(new Event('input'));
        }

        const fillButtons = document.querySelectorAll('.fill-button');
        fillButtons.forEach(button => {
            button.addEventListener('click', fillWithExpectedQuantity);
        });


        const confirmForm = document.getElementById('form-confirm');
        confirmForm.addEventListener('submit', (event) => {
            event.preventDefault(); // Adicionei so para poder testar

            const selectedIds = getTransferencesData();

            const invalidInputs = selectedIds.filter(
                v => v.quantity == 0
            )

            if (invalidInputs.length > 0) {
                alert('Preencha a quantidade corretamente');
                return;
            }

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'transference-ids';
            input.value = JSON.stringify(selectedIds);
            confirmForm.appendChild(input);

            // confirmForm.submit();
        });

        const cancelForm = document.getElementById('form-cancel');
        cancelForm.addEventListener('submit', (event) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'transference-ids';
            input.value = JSON.stringify(selectedIds);
            cancelForm.appendChild(input);
        });
    }

    function getTransferencesData() {
        const selectedCheckboxes = document.querySelectorAll('input[name="is-checked"]:checked');
        const selectedIds = [];

        selectedCheckboxes.forEach(checkbox => {
            selectedIds.push({
                id: checkbox.value,
                quantity: document.querySelector(`#row-${checkbox.value} input[name=quantity_received]`).value || 0
            });
        });

        return selectedIds;
    }
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>