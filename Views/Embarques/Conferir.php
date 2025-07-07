<?php
$pageTitle = "Embarques";
ob_start();

require "Components/Header.php";
?>
<main>
    <div class="d-flex gap-4 align-items-center">
        <button id="go-back" class="btn btn-custom">
            <i class="bi bi-arrow-left"></i>
        </button>
        <h1 class="mb-3">
            <?= $pageTitle ?> - Conferência do Container <?= $container['name'] ?>
        </h1>
    </div>

    <!-- Data de chegada: Aqui o usuario vai poder customizar a data de chegada dos produtos no container -->
    <div class="mb-3" style="max-width: 300px;">
        <label for="arrival_date" class="form-label">Data de chegada</label>
        <!-- Data no horario de brasilia -->
        <input type="date" class="form-control" id="arrival_date" value="<?= date('Y-m-d') ?>">
    </div>

    <!-- Mostrar total selecionado pelo checkbox (atraves da soma do campo quantidade entregue) -->
    <div class="mb-3">
        <p>Total selecionado: <span id="total">0</span></p>
    </div>

    <div class="table-responsive" style="max-height: 60vh; min-height: 100px">
        <table class="table table-striped" style="min-width:max-content">
            <thead class="thead-dark" style="position: sticky; top: 0; z-index: 1000;">
                <tr>
                    <th>
                        <label>
                            <input type="checkbox" id="selectAll" class="form-check-input">
                            Selecionar
                        </label>
                    </th>
                    <th>Código</th>
                    <th>Importadora</th>
                    <th>Quantidade Esperada</th> <!-- Agora será editável -->
                    <th style="max-width: 200px;">
                        <div class="d-flex">
                            Quantidade <br /> Entregue

                            <button type="button" id="completar-todos" class="btn btn-sm btn-custom ms-2" title="Completar todos">
                                <i class="bi bi-check2-all"></i>
                            </button>
                        </div>
                    </th>

                    <th>Observações</th>
                    <th>Ações</th> <!-- Nova coluna para o botão de remover -->
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($products) && $products->num_rows > 0) : ?>
                    <?php while ($row = $products->fetch_assoc()) : ?>
                        <tr data-id="<?= $row['product_ID'] ?>">
                            <td>
                                <input type="checkbox" class="form-check-input product-checkbox" name="selected_product_ids[]" value="<?= $row['product_ID'] ?>">
                            </td>
                            <td><?= $row['code'] ?></td>
                            <td><?= $row['importer'] ?></td>
                            <td style="max-width: 200px;">
                                <!-- Quantidade Esperada agora é um input editável -->
                                <input type="number" class="form-control quantity-expected-input"
                                    data-product-id="<?= $row['product_ID'] ?>"
                                    value="<?= $row['quantity_expected'] ?? 0 ?>" min="0">
                            </td>
                            <td style="max-width: 200px;">
                                <div class="input-group" style="max-width: 200px;">
                                    <input type="number" class="form-control quantity-delivered-input"
                                        data-expect="<?= $row['quantity_expected'] ?? 0 ?>"
                                        data-product-id="<?= $row['product_ID'] ?>"
                                        name="quantity_delivered[]" min="0">

                                    <button class="btn btn-custom completar" type="button">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control observation-input"
                                    data-product-id="<?= $row['product_ID'] ?>"
                                    name="observations[]">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-product" data-product-id="<?= $row['product_ID'] ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan='7' class="text-center" style="padding: 1rem;">Nenhum produto para conferir neste container.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pageCount > 1) : ?>
        <?php
        function isButtonDisabled($condition)
        {
            return $condition ? 'disabled' : '';
        }

        $currentPage = $_GET['page'] ?? 1;
        $prevPage = $currentPage - 1;
        $nextPage = $currentPage + 1;
        $isPrevDisabled = intval($currentPage) <= 1;
        $isNextDisabled = !isset($products) || $products->num_rows === 0 || intval($currentPage) >= $pageCount;
        ?>

        <div class="d-flex justify-content-center align-items-center gap-2 flex-wraps">
            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap" style="max-width: 300px;">
                <form method="GET" class="d-flex align-items-center">
                    <input type="hidden" name="page" value="<?= $prevPage ?>">
                    <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isPrevDisabled) ?> title="Voltar">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                </form>

                <span class="text-center">Página <?= $currentPage ?> de <?= $pageCount ?></span>

                <form method="GET">
                    <input type="hidden" name="page" value="<?= $nextPage ?>">
                    <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isNextDisabled) ?> title="Avançar">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Formulário principal para submissão dos dados -->
    <form method="POST" action="/embarques/conferir/<?= $container_ID ?>" id="main-form" class="mt-3">
        <input type="hidden" name="container_ID" value="<?= $container_ID ?>">
        <input type="hidden" name="arrival_date" id="form-arrival-date">
        <!-- Hidden inputs para os dados dos produtos serão adicionados via JS -->
        <button type="submit" class="btn btn-custom">
            Confirmar Conferência
        </button>
    </form>

    <?php include_once "Components/StatusMessage.php"; ?>
</main>

<script>
    const selectAll = document.getElementById('selectAll');
    const totalSpan = document.getElementById('total');
    const mainForm = document.getElementById('main-form');
    const arrivalDateInput = document.getElementById('arrival_date');

    // Função para obter todos os checkboxes visíveis (não removidos)
    function getProductCheckboxes() {
        return document.querySelectorAll('tbody .product-checkbox');
    }

    // Função para obter todos os inputs de quantidade entregue visíveis
    function getQuantityDeliveredInputs() {
        return document.querySelectorAll('tbody .quantity-delivered-input');
    }

    // Função para obter todos os inputs de quantidade esperada visíveis
    function getQuantityExpectedInputs() {
        return document.querySelectorAll('tbody .quantity-expected-input');
    }

    // Função para obter todos os inputs de observação visíveis
    function getObservationInputs() {
        return document.querySelectorAll('tbody .observation-input');
    }

    // Atualiza o total selecionado
    function updateTotalSelected() {
        let totalValue = 0;
        const checkboxes = getProductCheckboxes();
        const quantityDeliveredInputs = getQuantityDeliveredInputs();

        checkboxes.forEach((checkbox, index) => {
            if (checkbox.checked) {
                const quantityInput = quantityDeliveredInputs[index]; // Assume a mesma ordem
                totalValue += Number(quantityInput.value || 0);
            }
        });
        totalSpan.textContent = totalValue;
    }

    // Event listener para o checkbox "Selecionar Todos"
    selectAll.addEventListener('change', () => {
        getProductCheckboxes().forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        updateTotalSelected(); // Atualiza o total após selecionar/desselecionar todos
    });

    // Event listener para checkboxes individuais
    document.querySelector('tbody').addEventListener('change', (e) => {
        if (e.target.classList.contains('product-checkbox')) {
            updateTotalSelected();
            // Verifica se todos os checkboxes individuais estão marcados para atualizar o "Selecionar Todos"
            const allChecked = Array.from(getProductCheckboxes()).every(cb => cb.checked);
            selectAll.checked = allChecked;
        }
    });

    // Função para completar a quantidade entregue com a quantidade esperada
    function completar(e) {
        const tr = e.currentTarget.closest('tr');
        const inputDelivered = tr.querySelector('.quantity-delivered-input');
        const inputExpected = tr.querySelector('.quantity-expected-input'); // Pega o input de quantidade esperada

        // Usa o valor ATUAL do input de quantidade esperada
        const expectedQuantity = Number(inputExpected.value || 0);

        inputDelivered.value = expectedQuantity;
        inputDelivered.dispatchEvent(new Event('input')); // Dispara o evento 'input' para atualizar a cor e o total
    }

    // Adiciona event listeners aos botões "Completar" (usando delegação de eventos)
    document.querySelector('tbody').addEventListener('click', (e) => {
        if (e.target.closest('.completar')) {
            completar(e);
        }
    });

    // Função para mudar a cor da linha com base na quantidade entregue vs esperada
    function onChangeQuantidade(inputElement) {
        const tr = inputElement.closest('tr');
        // Pega o valor ATUAL do input de quantidade esperada da mesma linha
        const expectedInput = tr.querySelector('.quantity-expected-input');
        const expected = Number(expectedInput.value || 0);

        const delivered = Number(inputElement.value || 0);
        const columns = tr.querySelectorAll('td');

        if (delivered === expected) {
            columns.forEach(col => {
                col.style.backgroundColor = 'rgba(0, 255, 0, 0.2)'; // Verde
            });
        } else if (delivered > 0) { // Se algo foi entregue, mas não é igual ao esperado
            columns.forEach(col => {
                col.style.backgroundColor = 'rgba(255, 0, 0, 0.2)'; // Vermelho
            });
        } else { // Se o campo está vazio ou 0
            columns.forEach(col => {
                col.style.backgroundColor = 'transparent';
            });
        }
    }

    // Adiciona event listeners aos inputs de quantidade entregue (usando delegação de eventos)
    document.querySelector('tbody').addEventListener('input', (e) => {
        if (e.target.classList.contains('quantity-delivered-input')) {
            onChangeQuantidade(e.target);
            updateTotalSelected();
        }
    });

    // Adiciona event listeners aos inputs de quantidade esperada (usando delegação de eventos)
    // Isso é importante para que a cor da linha seja atualizada se a quantidade esperada for alterada
    document.querySelector('tbody').addEventListener('input', (e) => {
        if (e.target.classList.contains('quantity-expected-input')) {
            // Quando a quantidade esperada muda, precisamos reavaliar a cor da linha
            // com base na quantidade entregue atual
            const tr = e.target.closest('tr');
            const quantityDeliveredInput = tr.querySelector('.quantity-delivered-input');
            onChangeQuantidade(quantityDeliveredInput); // Reavalia a cor
        }
    });


    // Função para remover um produto da lista
    document.querySelector('tbody').addEventListener('click', (e) => {
        if (e.target.closest('.remove-product')) {
            const button = e.target.closest('.remove-product');
            const row = button.closest('tr');
            const productId = row.dataset.id;

            // Remove a linha da tabela
            row.remove();

            // Atualiza o total selecionado (se o produto removido estava selecionado)
            updateTotalSelected();

            // Opcional: Você pode adicionar uma confirmação antes de remover
            // if (confirm(`Tem certeza que deseja remover o produto ${productId} da lista?`)) {
            //     row.remove();
            //     updateTotalSelected();
            // }
        }
    });

    // Lógica de submissão do formulário principal
    mainForm.addEventListener('submit', (e) => {
        e.preventDefault(); // Previne a submissão padrão do formulário

        const selectedProductsData = [];
        const productRows = document.querySelectorAll('tbody tr'); // Pega todas as linhas visíveis

        productRows.forEach(row => {
            const checkbox = row.querySelector('.product-checkbox');
            const quantityDeliveredInput = row.querySelector('.quantity-delivered-input');
            const quantityExpectedInput = row.querySelector('.quantity-expected-input');
            const observationInput = row.querySelector('.observation-input');

            // Apenas inclua produtos que estão selecionados
            if (checkbox && checkbox.checked) {
                selectedProductsData.push({
                    product_ID: row.dataset.id,
                    quantity_delivered: Number(quantityDeliveredInput.value || 0),
                    quantity_expected: Number(quantityExpectedInput.value || 0), // Usa o nome "quantity_expected"
                    observation: observationInput.value || ''
                });
            }
        });

        // Limpa os inputs hidden antigos antes de adicionar os novos
        const oldHiddenInputs = mainForm.querySelectorAll('input[type="hidden"][name^="products_data"]');
        oldHiddenInputs.forEach(input => input.remove());

        // Adiciona os dados dos produtos selecionados como inputs hidden
        selectedProductsData.forEach((data, index) => {
            for (const key in data) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `products_data[${index}][${key}]`;
                input.value = data[key];
                mainForm.appendChild(input);
            }
        });

        // Adiciona a data de chegada ao formulário
        document.getElementById('form-arrival-date').value = arrivalDateInput.value;

        // Finalmente, submete o formulário
        mainForm.submit();
    });


    // Inicializa o total ao carregar a página
    window.addEventListener('load', () => {
        // Função para completar todos os produtos com a quantidade esperada
        document.getElementById('completar-todos').addEventListener('click', () => {
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(tr => {
                const inputExpected = tr.querySelector('.quantity-expected-input');
                const inputDelivered = tr.querySelector('.quantity-delivered-input');

                if (inputExpected && inputDelivered) {
                    inputDelivered.value = inputExpected.value;
                    inputDelivered.dispatchEvent(new Event('input')); // Atualiza a cor e total
                }
            });

            updateTotalSelected(); // Atualiza o total geral
        });

        updateTotalSelected();
        // Garante que as cores das linhas sejam aplicadas no carregamento
        getQuantityDeliveredInputs().forEach(input => onChangeQuantidade(input));
    });
</script>

<style>
    /* Estilos existentes */
    .container-col {
        max-width: 150px;
        overflow: auto;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
</style>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>