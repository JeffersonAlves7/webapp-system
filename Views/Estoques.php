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
            <button type="submit" class="btn btn-custom <?= isset($_GET["estoque"]) && $_GET["estoque"] != '' ? "" : "active" ?>">Geral</button>
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

    <!-- Filtros - adicionar inputs aqui -->
    <div style="max-width: 100%;overflow-x: auto;">
        <form method="get" class="d-flex gap-3 mt-3 mb-3 flex-wrap" style="width: 1000px; ">
            <input type="hidden" name="estoque" value="<?= $_GET["estoque"] ?? "" ?>">
            <label>
                Código:
                <input type="search" class="form-control" name="codigo" placeholder="Ex.: BT-001" id="input-codigo" value="<?= isset($_COOKIE["codigo"]) ? $_COOKIE["codigo"] : "" ?>">
            </label>
            <label>
                Importadora:
                <input type="search" class="form-control" name="importadora" placeholder="Ex.: ATTUS" value="<?= isset($_GET["importadora"]) ? $_GET["importadora"] : "" ?>">
            </label>

            <label>
                Data de Entrada:
                <input type="date" class="form-control" name="data_de_entrada" value="<?= isset($_GET["data_de_entrada"]) ? $_GET["data_de_entrada"] : "" ?>">
            </label>

            <label>
                Porcentagem para alerta
                <div class="input-group" style="max-width: 200px;">
                    <input type="number" max="100" min="1" placeholder="Ex.: 20" class="form-control" id="input-alerta" value="<?= isset($_COOKIE["alerta"]) ? $_COOKIE["alerta"] : 20 ?>" name="alerta" aria-describedby="alerta-addon">
                    <span class="input-group-text" id="alerta-addon">%</span>
                </div>
            </label>

            <div class="d-flex align-items-end gap-2">
                <button type="submit" class="btn bg-quaternary">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>


    <!-- Tabela -->
    <form method="get" class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark" style="position: sticky; top: 0;">
                <?php
                // Se o nome do estoque for Galpão
                if (!isset($_GET["estoque"]) || !$_GET["estoque"] || $_GET["estoque"] == 1) {
                    echo "<tr>
                    <th>CÓDIGO </th>
                    <th>QUANTIDADE</br> DE ENTRADA</th>
                    <th>SALDO </br>ATUAL</th>
                    <th>CONTAINER</br> DE ORIGEM </th>
                    <th>IMPORTADORA </th>
                    <th>DATA </br>DE ENTRADA</th>
                    <th>DIAS </br>EM ESTOQUE</th>
                    <th>GIRO</th>
                    <th>QUANTIDADE </br>PARA ALERTA</th>
                    <th>OBSERVAÇÃO</th>
                    </tr>";
                } else {
                    echo "<tr>
                        <th>CÓDIGO </th>
                        <th>QUANTIDADE</br> DE ENTRADA</th>
                        <th>SALDO </br>ATUAL</th>
                        <th>IMPORTADORA </th>
                        <th>DATA </br>DE ENTRADA</th>
                        <th>DIAS </br>EM ESTOQUE</th>
                        <th>GIRO</th>
                        <th>QUANTIDADE </br>PARA ALERTA</th>
                        <th>OBSERVAÇÃO</th>
                    </tr>";
                }
                ?>
            </thead>
            <tbody>
                <?php if (isset($produtos) && count($produtos) > 0) :
                    foreach ($produtos as $produto) {
                        if (!isset($_GET["estoque"]) || !$_GET["estoque"] || $_GET["estoque"] == 1) {
                            $codigo = $produto["codigo"];
                            $quantidade_entrada = $produto["quantidade_entrada"] ?? 0;
                            $saldo = $produto["saldo_atual"] ?? 0;
                            $container = $produto["container_de_origem"] ?? "";
                            $importadora = $produto["importadora"] ?? "";
                            $data = $produto["data_de_entrada"]  ?? "";
                            $dias = $produto["dias_em_estoque"] ?? 0;
                            $giro = $produto["giro"] ?? 0;
                            $alerta = $produto["quantidade_para_alerta"] ?? 0;
                            $observacao = $produto["observation"] ?? "";

                            echo "<tr>
                            <td>
                                <a href='/produtos/byId/" . htmlspecialchars($produto["ID"]) . "' title='Ver mais'>
                                    $codigo
                                </a>
                            </td>
                            <td>$quantidade_entrada</td>";

                            // Adicionar background ao saldo, verde se for maior que o alerta, vermelho se for menor e amarelo se for igual
                            if ($saldo > $alerta) {
                                echo "<td class='bg-quaternary'>$saldo</td>";
                            } elseif ($saldo < $alerta) {
                                echo "<td class='bg-danger'>$saldo</td>";
                            } else {
                                echo "<td class='bg-warning'>$saldo</td>";
                            }

                            echo "<td>$container</td>
                            <td>$importadora</td>
                            <td>$data</td>
                            <td>$dias dia(s)</td>
                            <td>$giro%</td>
                            <td>$alerta</td>
                            <td>$observacao</td>
                        </tr>";
                        } else {
                            $codigo = $produto["codigo"];
                            $quantidade_entrada = $produto["quantidade_entrada"] ?? 0;
                            $saldo = $produto["saldo_atual"] ?? 0;
                            $importadora = $produto["importadora"] ?? "";
                            $data = $produto["data_de_entrada"]  ?? "";
                            $dias = $produto["dias_em_estoque"] ?? 0;
                            $giro = $produto["giro"] ?? 0;
                            $alerta = $produto["quantidade_para_alerta"] ?? 0;
                            $observacao = $produto["observation"] ?? "";

                            echo "<tr>
                            <td>
                                <a href='/produtos/byId/" . htmlspecialchars($produto["ID"]) . "' title='Ver mais'>
                                    $codigo
                                </a>
                            </td>
                            <td>$quantidade_entrada</td>";

                            if ($saldo > $alerta) {
                                echo "<td class='bg-quaternary'>$saldo</td>";
                            } elseif ($saldo < $alerta) {
                                echo "<td class='bg-danger'>$saldo</td>";
                            } else {
                                echo "<td class='bg-warning'>$saldo</td>";
                            }

                            echo "
                            <td>$importadora</td>
                            <td>$data</td>
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
        $isNextDisabled = !isset($produtos) || !count($produtos) > 0 || $currentPage >= $pageCount;
        ?>

        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap" style="max-width: 250px; margin: 0 auto;">
            <form method="GET" class="d-flex align-items-center">
                <input type="hidden" name="page" value="<?= $prevPage ?>">
                <input type="hidden" name="estoque" value="<?= $_GET["estoque"] ?? "" ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isPrevDisabled) ?> title="Voltar">
                    <i class="bi bi-arrow-left"></i>
                </button>
            </form>

            <span class="text-center">Página <?= $currentPage ?> de <?= $pageCount ?></span>

            <form method="GET">
                <input type="hidden" name="page" value="<?= $nextPage ?>">
                <input type="hidden" name="estoque" value="<?= $_GET["estoque"] ?? "" ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isNextDisabled) ?> title="Avançar">
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>
    <?php endif; ?>
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

    const inputAlerta = document.getElementById("input-alerta");
    const inputCodigo = document.getElementById("input-codigo");

    inputAlerta.addEventListener("input", () => {
        document.cookie = `alerta=${inputAlerta.value}`;
    });

    inputCodigo.addEventListener("input", () => {
        document.cookie = `codigo=${inputCodigo.value}`;
    });

    // Ao apagar o input, apaga o cookie
    inputAlerta.addEventListener("change", () => {
        if (inputAlerta.value === "") {
            document.cookie = "alerta=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        }
    });

    inputCodigo.addEventListener("change", () => {
        if (inputCodigo.value === "") {
            document.cookie = "codigo=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        }
    });
</script>


<?php
$content = ob_get_clean();
include "Components/Template.php";
?>