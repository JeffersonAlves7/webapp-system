<?php
$pageTitle = "Home";
ob_start();
?>

<?php require "Components/Header.php" ?>

<div class="container">
    <h1 class="mt-4 mb-3">Produtos</h1>

    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newProductModal">
            Novo Produto
        </button>
    </div>

    <form method="get" class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark" style="position: sticky; top: 0;">
                <tr>
                    <th><i class="bi bi-info-circle"></i></th>
                    <th><i class="bi bi-tag"></i> Código</th>
                    <th><i class="bi bi-barcode"></i> EAN</th>
                    <th><i class="bi bi-shop"></i> Importadora</th>
                    <th><i class="bi bi-file-text"></i> Descrição</th>
                    <th><i class="bi bi-file-earmark-text"></i> Descrição em Chinês</th>
                    <th>
                        <!-- <i class="bi bi-pencil-square"></i> -->
                    </th>
                    <th>
                        <!-- <i class='bi bi-trash'></i> -->
                    </th>
                </tr>
                <tr>
                    <th></th>
                    <th><input type="search" class="form-control" name="code" placeholder="Filtrar por código" value="<?php echo isset($_GET["code"]) ? $_GET["code"] : "" ?>"></th>
                    <th><input type="search" class="form-control" name="ean" placeholder="Filtrar por EAN" value="<?php echo isset($_GET["ean"]) ? $_GET["ean"] : "" ?>"></th>
                    <th><input type="search" class="form-control" name="importer" placeholder="Filtrar por importadora" value="<?php echo isset($_GET["importer"]) ? $_GET["importer"] : "" ?>"></th>
                    <th><input type="search" class="form-control" name="description" placeholder="Filtrar por descrição" value="<?php echo isset($_GET["description"]) ? $_GET["description"] : "" ?>"></th>
                    <th colspan="3"><input type="search" class="form-control" name="chinese_description" placeholder="Filtrar por descrição em Chinês" value="<?php echo isset($_GET["chinese_description"]) ? $_GET["chinese_description"] : "" ?>"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($products) && $products->num_rows > 0) {
                    while ($row = $products->fetch_assoc()) {
                        echo "
                        <tr>
                            <td>
                                <a href='/product/index/" . $row["ID"] . "' title='Ver mais'>
                                    <i class='bi bi-zoom-in'></i>
                                </a>
                            </td>
                            <td><i class='bi bi-tag'></i> " . $row["code"] . "</td>
                            <td><i class='bi bi-barcode'></i> " . $row["ean"] . "</td>
                            <td><i class='bi bi-shop'></i> " . $row["importer"] . "</td>
                            <td>" . (
                            isset($row["description"]) ?
                            (substr($row["description"], 0, 30) . (strlen($row["description"]) > 30 ?
                                '... <a href="#" class="toggle-description" data-full-description="' . htmlspecialchars($row["description"]) . '">Ver mais</a>' :
                                '')
                            ) :
                            ''
                        ) . "</td>
                            <td>" . (
                            isset($row["chinese_description"]) ?
                            (
                                substr($row["chinese_description"], 0, 30) . (strlen($row["chinese_description"]) > 30 ?
                                    ' <a href="#" class="toggle-description" data-full-description="' . htmlspecialchars($row["chinese_description"]) . '">Ver mais</a>' :
                                    ''
                                )
                            ) :
                            ''
                        ) . "</td>
                            <td>
                                <button type='button' class='btn btn-edit' data-bs-toggle='modal' data-bs-target='#updateProductModal' 
                                    data-id='" . $row["ID"] . "' 
                                    data-code='" . $row["code"] . "' 
                                    data-ean='" . $row["ean"] . "' 
                                    data-importer='" . $row["importer"] . "' 
                                    data-description='" . $row["description"] . "' 
                                    data-chinese-description='" . $row["chinese_description"] . "'>
                                    <i class='bi bi-pencil-square text-primary'></i>
                                </button>
                            </td>
                            <td>
                                <a href='/product/delete/" . $row["ID"] . "' title='Apagar'>
                                    <button type='button' class='btn'>
                                        <i class='bi bi-trash text-danger'></i>
                                    </button>
                                </a>
                            </td>
                        </tr>
                        ";
                    }
                } else {
                    echo "<tr><td colspan='8'>Nenhum produto encontrado.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <button type="submit" hidden></button>
    </form>

    <div class="d-flex justify-content-between mt-3">
        <form method="GET">
            <input type="hidden" name="code" value="<?php echo isset($_GET["code"]) ? $_GET["code"] : "" ?>">
            <input type="hidden" name="ean" value="<?php echo isset($_GET["ean"]) ? $_GET["ean"] : "" ?>">
            <input type="hidden" name="importer" value="<?php echo isset($_GET["importer"]) ? $_GET["importer"] : "" ?>">
            <input type="hidden" name="description" value="<?php echo isset($_GET["description"]) ? $_GET["description"] : "" ?>">
            <input type="hidden" name="chinese_description" value="<?php echo isset($_GET["chinese_description"]) ? $_GET["chinese_description"] : "" ?>">
            <input type="hidden" name="page" value="<?php echo ($_GET['page'] ?? 1) - 1; ?>">
            <button class="btn btn-primary" <?php if (!isset($_GET["page"]) || intval($_GET["page"]) <= 1) {
                                                echo "disabled";
                                            } ?>>Voltar</button>
        </form>
        <form method="GET">
            <input type="hidden" name="code" value="<?php echo isset($_GET["code"]) ? $_GET["code"] : "" ?>">
            <input type="hidden" name="ean" value="<?php echo isset($_GET["ean"]) ? $_GET["ean"] : "" ?>">
            <input type="hidden" name="importer" value="<?php echo isset($_GET["importer"]) ? $_GET["importer"] : "" ?>">
            <input type="hidden" name="description" value="<?php echo isset($_GET["description"]) ? $_GET["description"] : "" ?>">
            <input type="hidden" name="chinese_description" value="<?php echo isset($_GET["chinese_description"]) ? $_GET["chinese_description"] : "" ?>">
            <input type="hidden" name="page" value="<?php echo ($_GET['page'] ?? 1) + 1; ?>">
            <button class="btn btn-primary" <?php if (!isset($products) || !$products->num_rows > 0) {
                                                echo "disabled";
                                            } ?>>Próxima</button>
        </form>
    </div>
</div>

<div class="modal fade" id="newProductModal" tabindex="-1" aria-labelledby="newProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newProductModalLabel">Novo Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="_method" value="post" />
                    <div class="mb-3">
                        <label for="code" class="form-label">Código</label>
                        <input type="text" name="code" class="form-control" id="code" required>
                    </div>
                    <div class="mb-3">
                        <label for="ean" class="form-label">EAN</label>
                        <input type="text" name="ean" class="form-control" id="ean" maxlength="13">
                    </div>
                    <div class="mb-3">
                        <label for="importer" class="form-label">Importadora</label>
                        <select class="form-select" name="importer" id="importer" required>
                            <option value="">Selecione uma opção</option>
                            <option value="ATTUS">ATTUS</option>
                            <option value="ATTUS_BLOOM">ATTUS_BLOOM</option>
                            <option value="ALPHA_YNFINITY">ALPHA_YNFINITY</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <input type="text" name="description" class="form-control" id="description">
                    </div>
                    <div class="mb-3">
                        <label for="chineseDescription" class="form-label">Descrição em Chinês</label>
                        <input type="text" name="chinese_description" class="form-control" id="chineseDescription">
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateProductModal" tabindex="-1" aria-labelledby="updateProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateProductModalLabel">Atualizar Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="_method" value="put" />
                    <input type="hidden" name="id" id="productId">
                    <div class="mb-3">
                        <label for="update-code" class="form-label">Código</label>
                        <input type="text" name="code" class="form-control" id="update-code">
                    </div>
                    <div class="mb-3">
                        <label for="update-ean" class="form-label">EAN</label>
                        <input type="text" name="ean" class="form-control" id="update-ean" maxlength="13">
                    </div>
                    <div class="mb-3">
                        <label for="update-importer" class="form-label">Importadora</label>
                        <select class="form-select" name="importer" id="update-importer">
                            <option value="">Selecione uma opção</option>
                            <option value="ATTUS">ATTUS</option>
                            <option value="ATTUS_BLOOM">ATTUS_BLOOM</option>
                            <option value="ALPHA_YNFINITY">ALPHA_YNFINITY</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="update-description" class="form-label">Descrição</label>
                        <input type="text" name="description" class="form-control" id="update-description">
                    </div>
                    <div class="mb-3">
                        <label for="update-chineseDescription" class="form-label">Descrição em Chinês</label>
                        <input type="text" name="chinese_description" class="form-control" id="update-chineseDescription">
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.toggle-description').forEach(function(element) {
            element.addEventListener('click', function(event) {
                event.preventDefault();
                var fullDescription = this.getAttribute('data-full-description');
                alert(fullDescription);
            });
        });
    });

    document.querySelectorAll('.btn-edit').forEach(function(button) {
        button.addEventListener('click', function() {
            var productId = this.getAttribute('data-id');
            var code = this.getAttribute('data-code');
            var ean = this.getAttribute('data-ean');
            var importer = this.getAttribute('data-importer');
            var description = this.getAttribute('data-description');
            var chineseDescription = this.getAttribute('data-chinese-description');

            document.getElementById('productId').value = productId;
            document.getElementById('update-code').value = code;
            document.getElementById('update-ean').value = ean;
            document.getElementById('update-importer').value = importer;
            document.getElementById('update-description').value = description;
            document.getElementById('update-chineseDescription').value = chineseDescription;

            document.querySelectorAll('.form-control').forEach(function(input) {
                input.classList.remove('modified');
            });
        });
    });

    document.querySelectorAll('#updateProductModal .form-control').forEach(function(input) {
        input.addEventListener('input', function() {
            this.classList.add('modified');
        });
    });
</script>
<?php
$content = ob_get_clean();
include "Components/Template.php";
?>