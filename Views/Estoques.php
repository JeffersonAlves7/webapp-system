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
        if (isset($estoques) && $estoques->num_rows > 0) {
            while ($estoque = $estoques->fetch_assoc()) {
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
                <select class="form-select" name="importadora" id="importadora">
                    <option value="">Selecione uma opção</option>
                    <option value="ATTUS" <?= (isset($_GET["importadora"]) && $_GET["importadora"] == "ATTUS") ? "selected" : "" ?>>ATTUS</option>
                    <option value="ATTUS_BLOOM" <?= (isset($_GET["importadora"]) && $_GET["importadora"] == "ATTUS_BLOOM") ? "selected" : "" ?>>ATTUS_BLOOM</option>
                    <option value="ALPHA_YNFINITY" <?= (isset($_GET["importadora"]) && $_GET["importadora"] == "ALPHA_YNFINITY") ? "selected" : "" ?>>ALPHA_YNFINITY</option>
                </select>
            </label>

            <label>
                Porcentagem para alerta
                <div class="input-group" style="max-width: 200px;">
                    <input type="number" max="100" min="1" placeholder="Ex.: 20" class="form-control" id="input-alerta" value="<?= isset($_COOKIE["alerta"]) ? $_COOKIE["alerta"] : 20 ?>" name="alerta" aria-describedby="alerta-addon">
                    <span class="input-group-text" id="alerta-addon">%</span>
                </div>
            </label>

            <div class="d-flex align-items-end gap-2">
                <button type="submit" class="btn bg-quaternary" title="Filtrar" style="height: 40px;width: 50px;">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela -->
    <div class="table-responsive" style="max-height: 55vh; min-height: 200px">
        <table class="table table-striped">
            <thead class="thead-dark" style="position: sticky; top: 0; z-index: 1000">
                <?php if (!isset($_GET["estoque"]) || !$_GET["estoque"] || $_GET["estoque"] == 1) : ?>
                    <tr>
                        <th>
                            <div class="d-flex align-items-center">
                                CÓDIGO
                                <form method="get" id="filter-codigo">
                                    <input type="hidden" name="estoque" value="<?= $_GET["estoque"] ?? "" ?>">
                                    <input type="hidden" name="orderType" value="<?= $_GET["orderType"] ?? "" ?>">
                                    <input type="hidden" name="orderBy" value="codigo">
                                    <button class="d-flex gap-1 btn" type="submit" id="filter-codigo">
                                        <?php if (isset($_GET["orderType"]) && $_GET["orderType"] == "asc") : ?>
                                            <i class="bi bi-sort-down filter" data-value="asc"></i>
                                        <?php elseif (isset($_GET["orderType"]) && $_GET["orderType"] == "desc") : ?>
                                            <i class="bi bi-sort-up filter" data-value="desc"></i>
                                        <?php else : ?>
                                            <i class="bi bi-filter filter" data-value=""></i>
                                        <?php endif; ?>
                                    </button>
                                </form>
                            </div>
                        </th>
                        <th>QUANTIDADE</br> DE ENTRADA</th>
                        <th>SALDO </br>ATUAL</th>
                        <th>CONTAINER</br> DE ORIGEM </th>
                        <th>IMPORTADORA </th>
                        <th>DATA DE </br>ENTRADA</th>
                        <th>DIAS </br>EM ESTOQUE</th>
                        <th>GIRO</th>
                        <th>QUANTIDADE </br>PARA ALERTA</th>
                        <th>OBSERVAÇÃO</th>
                    </tr>
                <?php else : ?>
                    <tr>
                        <th>CÓDIGO </th>
                        <th>QUANTIDADE</br> DE ENTRADA</th>
                        <th>SALDO </br>ATUAL</th>
                        <th>IMPORTADORA </th>
                        <th>DATA DE </br>ENTRADA</th>
                        <th>DIAS </br>EM ESTOQUE</th>
                        <th>GIRO</th>
                        <th>QUANTIDADE </br>PARA ALERTA</th>
                        <th>OBSERVAÇÃO</th>
                    </tr>
                <?php endif; ?>
            </thead>
            <tbody>
                <?php if (isset($produtos) && count($produtos) > 0) :
                    foreach ($produtos as $produto) :
                        $codigo = $produto["codigo"];
                        $quantidade_entrada = $produto["quantidade_entrada"] ?? 0;
                        $saldo = $produto["saldo_atual"] ?? 0;
                        if (isset($produto["data_de_entrada"])) {
                            $data = $produto["data_de_entrada"]  ?
                                date("d/m/Y", strtotime($produto["data_de_entrada"]))
                                : "";
                        } else {
                            $data = "-";
                        }
                        $dias = $produto["dias_em_estoque"] ?? 0;
                        $giro = $produto["giro"] ?? 0;
                        $alerta = $produto["quantidade_para_alerta"] ?? 0;
                        $observacao = $produto["observation"] ?? "";
                        $importadora = $produto["importadora"] ?? "";
                        $container = isset($produto["container_de_origem"]) ? $produto["container_de_origem"] : "";
                ?>
                        <tr>
                            <td>
                                <a href='/produtos/byId/<?= htmlspecialchars($produto["ID"]) ?>' title='Ver mais'>
                                    <?= $codigo ?>
                                </a>
                            </td>
                            <td><?= $quantidade_entrada ?></td>

                            <?php if ($saldo > $alerta) : ?>
                                <td class='bg-quaternary'><?= $saldo ?></td>
                            <?php elseif ($saldo < $alerta) : ?>
                                <td class='bg-danger'><?= $saldo ?></td>
                            <?php else : ?>
                                <td class='bg-warning'><?= $saldo ?></td>
                            <?php endif; ?>

                            <?php if (!isset($_GET["estoque"]) || !$_GET["estoque"] || $_GET["estoque"] == 1) : ?>
                                <td>
                                    <div class="container-col">
                                        <p><?= $container ?></p>
                                    </div>
                                </td>
                            <?php endif; ?>

                            <td><?= $importadora ?></td>
                            <td style='min-width: 100px;'>
                                <?= $data ?>
                            </td>
                            <td><?= $dias ?> dia(s)</td>
                            <td><?= $giro ?>%</td>
                            <td><?= $alerta ?></td>
                            <td><?= $observacao ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="9" class="text-center">Nenhum produto encontrado</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <button type="submit" hidden></button>

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
                <input type="hidden" name="alerta" value="<?= $_COOKIE["alerta"] ?? 20 ?>">
                <input type="hidden" name="codigo" value="<?= $_COOKIE["codigo"] ?? "" ?>">
                <input type="hidden" name="importadora" value="<?= $_GET["importadora"] ?? "" ?>">
                <input type="hidden" name="orderBy" value="<?= $_GET["orderBy"] ?? "" ?>">
                <input type="hidden" name="orderType" value="<?= $_GET["orderType"] ?? "" ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isPrevDisabled) ?> title="Voltar">
                    <i class="bi bi-arrow-left"></i>
                </button>
            </form>

            <span class="text-center">Página <?= $currentPage ?> de <?= $pageCount ?></span>

            <form method="GET">
                <input type="hidden" name="page" value="<?= $nextPage ?>">
                <input type="hidden" name="estoque" value="<?= $_GET["estoque"] ?? "" ?>">
                <input type="hidden" name="alerta" value="<?= $_COOKIE["alerta"] ?? 20 ?>">
                <input type="hidden" name="codigo" value="<?= $_COOKIE["codigo"] ?? "" ?>">
                <input type="hidden" name="importadora" value="<?= $_GET["importadora"] ?? "" ?>">
                <input type="hidden" name="orderBy" value="<?= $_GET["orderBy"] ?? "" ?>">
                <input type="hidden" name="orderType" value="<?= $_GET["orderType"] ?? "" ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isNextDisabled) ?> title="Avançar">
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>
    <?php endif; ?>

    <?php include "Components/StatusMessage.php"; ?>
</main>

<style>
    .container-col {
        max-width: 150px;
        overflow: auto;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
</style>

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
//                         <button type="button" class="btn btn-custom" onclick="confirmNewStock()">Salvar</button>
//                     </div>
//                 </form>
//             </div>
//         </div>
//     </div>
// </div>
?>

<script>
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

    const filterCodigo = document.getElementById("filter-codigo");

    filterCodigo.addEventListener("submit", (e) => {
        e.preventDefault();
        const orderTypeEl = filterCodigo.querySelector("input[name='orderType']")
        const orderType = orderTypeEl.value;
        if (orderType == "desc") {
            orderTypeEl.value = "";
            filterCodigo.querySelector("input[name='orderBy']").value = "";
        } else if (orderType == "asc") {
            orderTypeEl.value = "desc";
        } else {
            orderTypeEl.value = "asc";
        }
        filterCodigo.submit();
    });
</script>


<?php
$content = ob_get_clean();
include "Components/Template.php";
?>