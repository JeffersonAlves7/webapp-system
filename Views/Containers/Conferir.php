<?php
$pageTitle = "Embarques";
ob_start();

require "Components/Header.php";
?>
<main>
    <h1><?= $pageTitle ?> - Produtos no Container</h1>

    <div class="table-responsive">
        <table class="table table-striped" style="min-width:max-content">
            <thead class="thead-dark" style="position: sticky; top: 0;">
                <tr>
                    <th><input type="checkbox" id="select-all" /> <label for="select-all">Selecionar</label></th>
                    <th>Código</th>
                    <th>Importadora</th>
                    <th>Quantidade Esperada</th>
                    <th>Quantidade Entregue</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($products_in_container) && count($products_in_container) > 0) : ?>
                    <?php foreach ($products_in_container as $row) : ?>
                        <tr id=<?= "row-" . $row['ID'] ?>>
                            <td>
                                <input type="checkbox" name="is-checked" id="<?= $row['ID'] ?>">
                            </td>
                            <td><?= $row['code'] ?></td>
                            <td><?= $row['importer'] ?></td>
                            <td>
                                <p class="text-center">
                                    <?= $row['quantity_expected'] ? $row['quantity_expected'] : '-' ?>
                                </p>
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="number" class="form-control" value="0" min="0" max="<?= $row['quantity_expected'] ?>" name="quantity_received" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary fill-button" title="Preencher com a quantidade esperada" data-id="<?= $row['ID'] ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M8 3.5a.5.5 0 0 1 .5.5v2.793l1.354-1.353a.5.5 0 0 1 .708.708l-2.147 2.146a.5.5 0 0 1-.708 0L5.94 6.646a.5.5 0 0 1 .708-.708L7.5 6.793V4a.5.5 0 0 1 .5-.5z" />
                                                <path fill-rule="evenodd" d="M8 4.5a.5.5 0 0 0-.5.5V7.207l-1.354-1.353a.5.5 0 0 0-.708.708l2.147 2.146a.5.5 0 0 0 .708 0l2.147-2.146a.5.5 0 0 0-.708-.708L8.5 7.207V5a.5.5 0 0 0-.5-.5z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                            </td>
                            <td>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan='5'>Nenhum produto encontrado neste container.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (isset($products_in_container) && count($products_in_container) > 0) : ?>
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary" id="button-embarque">Realizar Embarque</button>
        </div>
    <?php endif; ?>
</main>

<script>
    // Selecionar todos os checkboxes no caso de clicar no elemento #select-all e salvar o estado do checkbox dentro de um array
    const selectAll = document.getElementById('select-all');
    const productCheckboxes = document.querySelectorAll('input[name=is-checked]');

    const checkAll = () => {
        productCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }

    selectAll.addEventListener('click', checkAll);

    // Adicionar cores de status para os inputs de quantidade. Verde para quantidade esperada e vermelho para quantidade recebida
    const quantityInputs = document.querySelectorAll('input[name=quantity_received]');
    quantityInputs.forEach(input => {
        input.addEventListener('input', () => {
            if (input.value == input.max) {
                input.classList.remove('border-danger');
                input.classList.add('border-success');
            } else {
                input.classList.remove('border-success');
                input.classList.add('border-danger');
            }
        });
    });

    function fillWithExpectedQuantity(event) {
        const id = event.target.dataset.id;

        const quantityInput = document.querySelector(`#row-${id} input[name=quantity_received]`);
        quantityInput.value = <?= $row['quantity_expected'] ?>;
        quantityInput.dispatchEvent(new Event('input'));
    }

    const fillButtons = document.querySelectorAll('.fill-button');
    fillButtons.forEach(button => {
        button.addEventListener('click', fillWithExpectedQuantity);
    });

    const buttonEmbarque = document.getElementById('button-embarque');

    buttonEmbarque.addEventListener('click', () => {
        const selectedProducts = Array.from(document.querySelectorAll('input[name=is-checked]:checked')).map(checkbox => checkbox.id);
        const products = selectedProducts.map(id => {
            console.log(`row-${id}`)
            const row = document.getElementById(`row-${id}`);
            const code = row.children[1].innerText;
            const quantity = row.querySelector('input[name=quantity_received]').value;
            return {
                id,
                code,
                quantity
            };
        });


        // fetch('http://localhost:8000/Embarque/Realizar', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json'
        //     },
        //     body: JSON.stringify(products)
        // }).then(response => {
        //     if (response.ok) {
        //         window.location.href = 'http://localhost:8000/Embarque';
        //     }
        // });
    });
</script>


<?php
$content = ob_get_clean();
include "Components/Template.php";
?>