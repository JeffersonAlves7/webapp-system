<?php
$pageTitle = "Estoques";
ob_start();

require "Components/Header.php";
?>
<main>
    <h1 class="mt-4 mb-3"> <?= $pageTitle ?></h1>

    <!-- Estoques -->
    <div class="d-flex gap-3">
        <form method="get">
            <button type="submit" class="btn btn-custom <?= isset($_GET["estoque"]) ? "" : "active" ?>">Geral</button>
        </form>

        <?php
        if (isset($stocks) && $stocks->num_rows > 0) {
            while ($estoque = $stocks->fetch_assoc()) {
                $name = $estoque["name"];
                $ID = $estoque["ID"];
                $active = (isset($_GET["estoque"]) && $_GET["estoque"] == "$ID" ? "active" : "");

                echo "<form method='get'>
                    <input type='hidden' name='estoque' value='$ID'/>
                    <button type='submit' class='btn btn-custom $active'>$name</button>
                </form>";
            }
        }
        ?>
    </div>

    <!-- Filtros -->


    <!-- Tabela -->
    <form method="get" class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark" style="position: sticky; top: 0;">
                <tr>
                    <th colspan="3"><input type="search" class="form-control" name="code" placeholder="Filtrar por código" value="<?= isset($_GET["code"]) ? $_GET["code"] : "" ?>"></th>
                    <th colspan="3"><input type="search" class="form-control" name="container" placeholder="Filtrar por container de origem" value="<?= isset($_GET["importer"]) ? $_GET["importer"] : "" ?>"></th>
                    <th colspan="3"><input type="search" class="form-control" name="importer" placeholder="Filtrar por importadora" value="<?= isset($_GET["importer"]) ? $_GET["importer"] : "" ?>"></th>
                    <th></th>
                </tr>
                <?php
                // Se o nome do estoque for Galpão
                if (!isset($_GET["estoque"]) || $_GET["estoque"] == 1) {
                    echo "<tr>
                    <th>CÓDIGO </th>
                    <th>IMPORTADORA </th>
                    <th>QUANTIDADE DE ENTRADA</th>
                    <th>SALDO ATUAL </th>
                    <th>CONTAINER DE ORIGEM </th>
                    <th>DATA DE ENTRADA</th>
                    <th>DIAS EM ESTOQUE</th>
                    <th>GIRO</th>
                    <th>QUANTIDADE PARA ALERTA</th>
                    <th>OBSERVAÇÃO</th>
                    </tr>";
                } else {
                    echo "<tr>
                        <th>CÓDIGO </th>
                        <th>IMPORTADORA </th>
                        <th>QUANTIDADE DE ENTRADA</th>
                        <th>SALDO ATUAL</th>
                        <th>DATA DE ENTRADA</th>
                        <th>DIAS EM ESTOQUE</th>
                        <th>GIRO</th>
                        <th>QUANTIDADE PARA ALERTA</th>
                        <th>OBSERVAÇÃO</th>
                    </tr>";
                }
                ?>
            </thead>
            <tbody>
                <?php if (isset($produtos) && count($produtos) > 0) :
                    foreach ($produtos as $produto) {
                        if (!isset($_GET["estoque"]) || $_GET["estoque"] == 1) {
                            $codigo = $produto["code"];
                            $quantidade_entrada = $produto["entry_quantity"] ?? 0;
                            $saldo = $produto["quantity"] ?? 0;
                            $container = $produto["container"] ?? "";
                            $importadora = $produto["importer"] ?? "";
                            $data = $produto["entry_date"]  ?? "";
                            $dias = $produto["days_in_stock"] ?? 0;
                            $giro = $produto["giro"] ?? 0;
                            $alerta = $produto["alerta"] ?? 0;
                            $observacao = $produto["observation"] ?? "";

                            echo "<tr>
                            <td>
                                <a href='/produtos/byId/" . htmlspecialchars($produto["ID"]) . "' title='Ver mais'>
                                    $codigo
                                </a>
                            </td>
                            <td>$importadora</td>
                            <td>$quantidade_entrada</td>";

                            // Adicionar background ao saldo, verde se for maior que o alerta, vermelho se for menor e amarelo se for igual
                            if ($saldo > $alerta) {
                                echo "<td class='bg-success'>$saldo</td>";
                            } elseif ($saldo < $alerta) {
                                echo "<td class='bg-danger'>$saldo</td>";
                            } else {
                                echo "<td class='bg-warning'>$saldo</td>";
                            }

                            echo "<td>$container</td>
                            <td>$data</td>
                            <td>$dias</td>
                            <td>$giro%</td>
                            <td>$alerta</td>
                            <td>$observacao</td>
                        </tr>";
                        } else {
                            $codigo = $produto["code"];
                            $quantidade_entrada = $produto["entry_quantity"] ?? 0;
                            $saldo = $produto["quantity"] ?? 0;
                            $importadora = $produto["importer"] ?? "";
                            $data = $produto["entry_date"]  ?? "";
                            $dias = $produto["days_in_stock"] ?? 0;
                            $giro = $produto["giro"] ?? 0;
                            $alerta = $produto["alerta"] ?? 0;
                            $observacao = $produto["observation"] ?? "";

                            echo "<tr>
                            <td>
                                <a href='/produtos/byId/" . htmlspecialchars($produto["ID"]) . "' title='Ver mais'>
                                    $codigo
                                </a>
                            </td>
                            <td>$importadora</td>
                            <td>$quantidade_entrada</td>";

                            if ($saldo > $alerta) {
                                echo "<td class='bg-success'>$saldo</td>";
                            } elseif ($saldo < $alerta) {
                                echo "<td class='bg-danger'>$saldo</td>";
                            } else {
                                echo "<td class='bg-warning'>$saldo</td>";
                            }

                            echo "<td>$data</td>
                            <td>$dias</td>
                            <td>$giro%</td>
                            <td>$alerta</td>
                            <td>$observacao</td>
                        </tr>";
                        }
                    }
                endif; ?>
            </tbody>
        </table>
        <button type="submit" hidden></button>
    </form>
</main>

<?php
// <div class="modal fade" id="newStock" tabindex="-1" aria-labelledby="newStockLabel" aria-hidden="true">
//     <div class="modal-dialog">
//         <div class="modal-content">
//             <div class="modal-header">
//                 <h5 class="modal-title" id="newStockLabel">Novo Estoque</h5>
//                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
//             </div>
//             <div class="modal-body">
//                 <form method="post" id="newStockForm">
//                     <input type="hidden" name="_method" value="post" />
//                     <div class="mb-3">
//                         <label for="stock-name" class="form-label">Nome do Estoque</label>
//                         <input type="text" name="name" class="form-control" id="stock-name" required>
//                     </div>
//                     <div class="modal-footer">
//                         <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
//                         <button type="button" class="btn btn-primary" onclick="confirmNewStock()">Salvar</button>
//                     </div>
//                 </form>
//             </div>
//         </div>
//     </div>
// </div>

?>
<script>
    // function confirmNewStock() {
    //     if (confirm("Tem certeza de que deseja adicionar este novo estoque?")) {
    //         document.getElementById("newStockForm").submit();
    //     }
    // }
</script>


<?php
$content = ob_get_clean();
include "Components/Template.php";
?>