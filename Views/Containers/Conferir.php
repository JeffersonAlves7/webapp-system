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
        <input type="date" class="form-control" id="arrival_date" value="<?=  date('Y-m-d') ?>">
    </div>

    <!-- Mostrar total selecionado pelo checkbox (atraves da soma do campo quantidade entregue) -->
    <div class="mb-3">
        <p>Total selecionado: <span id="total">0</span></p>
    </div>

    <div class="table-responsive" style="max-height: 60vh; min-height: 100px">
        <table class="table table-striped" style="min-width:max-content">
            <thead class="thead-dark" style="position: sticky; top: 0;">
                <tr>
                    <th>
                        <label>
                            <input type="checkbox" id="selectAll" class="form-check-input">
                            Selecionar
                        </label>
                    </th>
                    <th>Código</th>
                    <th>Importadora</th>
                    <th>Quantidade Esperada</th>
                    <th style="max-width: 200px;">
                        Quantidade Entregue</th>
                    <th>Observações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($products) && $products->num_rows > 0) : ?>
                    <?php while ($row = $products->fetch_assoc()) : ?>
                        <tr data-id="<?= $row['product_ID'] ?>">
                            <td>
                                <input type="checkbox" class="form-check-input" name="selected[]" value="<?= $row['product_ID'] ?>">
                            </td>
                            <td><?= $row['code'] ?></td>
                            <td><?= $row['importer'] ?></td>
                            <td style="max-width: 200px;">
                                <p class="text-center" style="max-width: 200px;">
                                    <?= $row['quantity_expected'] ? $row['quantity_expected'] : '-' ?>
                                </p>
                            </td>
                            <td style="max-width: 200px;">
                                <div class="input-group" style="max-width: 200px;">
                                    <input type="number" class="form-control" data-expect="<?= $row['quantity_expected'] ?>" name="quantity_delivered[]">
                                    <button class="btn btn-custom completar">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="observations[]">
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan='6' class="text-center" style="padding: 1rem;">Nenhum produto para conferir neste container.</td>
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
        $isPrevDisabled = !isset($_GET["page"]) || intval($_GET["page"]) <= 1;
        $isNextDisabled = !isset($products) || !$products->num_rows || $currentPage >= $pageCount;
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

            <a href="/embarques/conferir/<?= $container_ID ?>">Conferir embarque</a>
        </div>
    <?php endif; ?>

    <form method="POST" action="/embarques/conferir/<?= $container_ID ?>" id="form">
        <input type="hidden" name="container_ID" value="<?= $container_ID ?>">
        <button type="submit" class="btn btn-custom">
            Confirmar
        </button>
    </form>

    <?php include_once "Components/StatusMessage.php"; ?>
</main>

<script>
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
    const quantidades = document.querySelectorAll('tbody input[type="number"]');
    const total = document.getElementById('total');
    const form = document.getElementById('form');

    selectAll.addEventListener('change', () => {
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    });

    function getSelecteds() {
        const selecteds = [];

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selecteds.push(checkbox.closest('tr').dataset.id);
            }
        });

        return selecteds;
    }

    function completar(e) {
        //  Funcao sera acionada quando o botao de completar for clicado
        //  Ao clicar nesse botao o valor de quantity_expected deve ser aplicado no input de quantidade entregue
        const input = e.currentTarget.previousElementSibling;
        const expect = input.dataset.expect;

        input.value = expect;
        input.dispatchEvent(new Event('input'));
    }

    document.querySelectorAll('.completar').forEach(button => {
        button.addEventListener('click', completar);
    });

    function onChangeQuantidade(e) {
        const input = e.currentTarget;
        const expect = input.dataset.expect;
        const tr = input.closest('tr');
        const value = input.value;

        // Mudar a cor de fundo de todas as colunas ao inves da linha
        const colunas = tr.querySelectorAll('td');

        if (value == expect) {
            colunas.forEach(coluna => {
                coluna.style.backgroundColor = 'rgba(0, 255, 0, 0.2)';
            });
        } else if (value) {
            colunas.forEach(coluna => {
                coluna.style.backgroundColor = 'rgba(255, 0, 0, 0.2)';
            });
        } else {
            colunas.forEach(coluna => {
                coluna.style.backgroundColor = 'transparent';
            });
        }
    }

    function getTotalSelected() {
        const selecteds = getSelecteds();
        const totalValue = [...quantidades].reduce((acc, input) => {
            if (selecteds.includes(input.closest('tr').dataset.id)) {
                return acc + Number(input.value);
            }

            return acc;
        }, 0);

        return totalValue;
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            total.textContent = getTotalSelected();
        })
    });

    quantidades.forEach(input => {
        input.addEventListener('input', (e) => {
            onChangeQuantidade(e);
            total.textContent = getTotalSelected();
        });
    });

    function onSubmit(e) {
        // Funcao sera acionada quando o formulario for submetido
        // Vai capturar a quantidade dos produtos com checkbox selecionado e enviar para o servidor
        // Vai também passar ao servidor a observacao de cada produto
        // Adicionar campos ao proprio formulario para enviar esses dados
        e.preventDefault();

        const selecteds = getSelecteds();
        // Se nao tiver nenhum produto selecionado, nao faz nada
        if (!selecteds.length) {
            return false;
        }

        const quantities = [...quantidades].map(input => input.value);
        const observations = [...document.querySelectorAll('tbody input[type="text"]')].map(input => input.value);

        selecteds.forEach((selected, index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `selected[]`;
            input.value = selected;
            form.appendChild(input);

            const quantity = document.createElement('input');
            quantity.type = 'hidden';
            quantity.name = `quantity_delivered[]`;
            quantity.value = quantities[index];
            form.appendChild(quantity);

            const observation = document.createElement('input');
            observation.type = 'hidden';
            observation.name = `observations[]`;
            observation.value = observations[index];
            form.appendChild(observation);
        });

        const arrival_date = document.getElementById('arrival_date').value;
        const arrivalDateInput = document.createElement('input');
        arrivalDateInput.type = 'hidden';
        arrivalDateInput.name = 'arrival_date';
        arrivalDateInput.value = arrival_date;
        form.appendChild(arrivalDateInput);

        form.submit();

        return false;
    }

    form.addEventListener('submit', onSubmit);
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>