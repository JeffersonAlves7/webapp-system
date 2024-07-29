<?php
$pageTitle = "Produtos";
ob_start();

require "Components/Header.php"
?>

<main>
    <h1 class="mt-4 mb-3"><?= $pageTitle ?> Arquivados</h1>

    <form method="get">
        <div class="d-flex gap-3 align-items-end">
            <div>
                <label for="importer" class="form-label">Importadora</label>
                <select class="form-select" name="importer">
                    <option value="">Todas</option>
                    <option value="ATTUS" <?= isset($_GET['importer']) && $_GET['importer'] == 'ATTUS' ? 'selected' : '' ?>>ATTUS</option>
                    <option value="ATTUS_BLOOM" <?= isset($_GET['importer']) && $_GET['importer'] == 'ATTUS_BLOOM' ? 'selected' : '' ?>>ATTUS_BLOOM</option>
                    <option value="ALPHA_YNFINITY" <?= isset($_GET['importer']) && $_GET['importer'] == 'ALPHA_YNFINITY' ? 'selected' : '' ?>>ALPHA_YNFINITY</option>
                </select>
            </div>

            <div>
                <label for="code" class="form-label">Código</label>
                <input type="text" class="form-control" name="code" placeholder="Ex.: BT-001" value="<?= isset($_GET['code']) ? $_GET['code'] : '' ?>">
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-custom" title="Pesquisar">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </form>

    <div class="table-responsive" style="max-height: 40vh; min-height: 100px">
        <table class="table table-striped" style="min-width: max-content">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Saldo Atual</th>
                    <th>Container de Origem</th>
                    <th>Importadora</th>
                    <th>Data de Entrada</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($products as $product) {
                ?>
                    <tr>
                        <td><?= $product["code"] ?></td>
                        <td></td>
                        <td><?= $product["container_name"] ?></td>
                        <td><?= $product["importer"] ?></td>
                        <td><?= date("d/m/Y", strtotime($product["entry_date"])) ?></td>
                    </tr>
                <?php
                }
                ?>
                <?php if (count($products) == 0) { ?>
                    <tr>
                        <td colspan="5" class="text-center">Nenhum produto encontrado</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</main>


<script>
    document.querySelector("select[name='importer']").addEventListener("change", function() {
        this.form.submit();
    }); 
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>