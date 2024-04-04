<?php
$pageTitle = "Embarques";
ob_start();

require "Components/Header.php";
?>
<main>
    <h1><?= $pageTitle ?> - Produtos no Container</h1>

    <!-- Tabela -->
    <!-- <form method="get" id="products-form" style="max-height: 400px; overflow-y: auto;">
    </form> -->
    <div style="max-width: 500px; overflow-x: auto;">
        <table class="table table-striped">
            <thead class="thead-dark" style="position: sticky; top: 0;">
                <tr>
                    <th>Código</th>
                    <th>Quantidade</th>
                    <th>Status</th>
                    <th>Data de Embarque</th>
                    <th>Previsão de Chegada</th>
                    <th>Dias para Chegar</th>
                    <th>Data de Chegada</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($products_in_container) && $products_in_container->num_rows > 0) {
                    while ($row = $products_in_container->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $row['code'] ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td><?= $row['in_stock'] ? 'Em Estoque' : 'Em Trânsito' ?></td>
                            <td><?= $row['departure_date'] ? $row['departure_date'] : '-' ?></td>
                            <td><?= $row['departure_date'] ? date('Y-m-d', strtotime($row['departure_date'] . ' + 30 days')) : '-' ?></td>
                            <td><?php
                                if ($row['in_stock'] && !$row['arrival_date']) {
                                    echo "Chegou no prazo.";
                                } else {
                                    if ($row['arrival_date']) {
                                        $arrival_date = new DateTime($row['arrival_date']);
                                        $expected_arrival_date = new DateTime(date('Y-m-d', strtotime($row['departure_date'] . ' + 30 days')));
                                        $days_late = $arrival_date->diff($expected_arrival_date)->days;
                                        if ($days_late > 0) {
                                            echo "Chegou dia " . $row['arrival_date'] . " " . $days_late . " dias atrasado.";
                                        } else {
                                            echo "Chegou dia " . $row['arrival_date'] . " " . abs($days_late) . " dias adiantado.";
                                        }
                                    } else {
                                        $expected_arrival_date = new DateTime(date('Y-m-d', strtotime($row['departure_date'] . ' + 30 days')));
                                        $days_late = (new DateTime())->diff($expected_arrival_date)->days;
                                        if ($days_late > 0) {
                                            echo "<span class='text-danger'>" . $days_late . " dias atrasado!</span>";
                                        } else {
                                            echo "<span class='text-success'>Previsto para chegar em " . abs($days_late) . " dias!</span>";
                                        }
                                    }
                                }
                                ?>
                            </td>
                            <td><?= $row['arrival_date'] ?></td>
                            <td>
                                <form method="post" class="deleteProductForm" id="<?= "formProduct" . $row['product_ID'] ?>">
                                    <input type="hidden" name="product_ID" value="<?= $row['product_ID'] ?>">
                                    <input type="hidden" name="container_ID" value="<?= $row['container_ID'] ?>">
                                    <button type='submit' class='btn'>
                                        <i class='bi bi-trash text-danger'></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                <?php }
                }
                ?>
            </tbody>
        </table>
    </div>
</main>

<script>

</script>


<?php
$content = ob_get_clean();
include "Components/Template.php";
?>