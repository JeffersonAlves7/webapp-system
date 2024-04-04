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
                <tr>
                    <th>CÓDIGO </th>
                    <th>QUANTIDADE DE ENTRADA</th>
                    <th>SALDO ATUAL </th>
                    <th>CONTAINER DE ORIGEM </th>
                    <th>IMPORTADORA </th>
                    <th>DATA DE ENTRADA </th>
                    <th>DIAS EM ESTOQUE </th>
                    <th>GIRO</th>
                    <th>QUANTIDADE PARA ALERTA </th>
                    <th>OBSERVAÇÃO</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($products) && $products->num_rows > 0) {
                    while ($row = $products->fetch_assoc()) {
                        echo "
                        <tr>
                        </tr>
                        ";
                    }
                } else {
                    echo "<tr><td colspan='10'>Nenhum produto encontrado.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <button type="submit" hidden></button>
    </form>
</main>

<!-- <div class="modal fade" id="newStock" tabindex="-1" aria-labelledby="newStockLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newStockLabel">Novo Estoque</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" id="newStockForm">
                    <input type="hidden" name="_method" value="post" />
                    <div class="mb-3">
                        <label for="stock-name" class="form-label">Nome do Estoque</label>
                        <input type="text" name="name" class="form-control" id="stock-name" required>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="button" class="btn btn-primary" onclick="confirmNewStock()">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> -->

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