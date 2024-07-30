<?php
$pageTitle = "Produtos";
ob_start();
?>

<?php require "Components/Header.php" ?>

<main>
    <h1 class="mt-4 mb-3">Produtos</h1>

    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#newProductModal">
            Novo Produto <i class="bi bi-plus-lg"></i>
        </button>
    </div>

    <?php $productsExist = isset($products) && $products->num_rows > 0; ?>

    <form method="get" class="table-responsive" style="max-height: 60vh; min-height: 200px">
        <table class="table table-striped">
            <thead class="thead-dark" style="position: sticky; top: 0; z-index: 1000">
                <tr>
                    <th>
                        <div class="d-flex gap-2">
                            <i class="bi bi-box"></i> Container
                        </div>
                    </th>
                    <th><i class="bi bi-shop"></i> Importadora</th>
                    <th><i class="bi bi-tag"></i> Código</th>
                    <th><i class="bi bi-file-text"></i> Descrição</th>
                    <th><i class="bi bi-file-earmark-text"></i> Descrição em Chinês</th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>
                        <input type="search" class="form-control" name="container" placeholder="Container" value="<?= isset($_GET["container"]) ? $_GET["container"] : "" ?>">
                    </th>
                    <th>
                        <select class="form-select" name="importer" id="importer" style="min-width: 220px;">
                            <option value="">Todos</option>
                            <option value="ATTUS" <?= (isset($_GET["importer"]) && $_GET["importer"] == "ATTUS") ? "selected" : "" ?>>ATTUS</option>
                            <option value="ATTUS_BLOOM" <?= (isset($_GET["importer"]) && $_GET["importer"] == "ATTUS_BLOOM") ? "selected" : "" ?>>ATTUS_BLOOM</option>
                            <option value="ALPHA_YNFINITY" <?= (isset($_GET["importer"]) && $_GET["importer"] == "ALPHA_YNFINITY") ? "selected" : "" ?>>ALPHA_YNFINITY</option>
                        </select>
                    </th>
                    <th><input type="search" class="form-control" name="code" placeholder="Código" value="<?= isset($_GET["code"]) ? $_GET["code"] : "" ?>"></th>
                    <th>
                        <input type="search" class="form-control" name="description" placeholder="Descrição" value="<?= isset($_GET["description"]) ? $_GET["description"] : "" ?>">
                    </th>
                    <th><input type="search" class="form-control" name="chinese_description" placeholder="Descrição em Chinês" value="<?= isset($_GET["chinese_description"]) ? $_GET["chinese_description"] : "" ?>"></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php if ($productsExist) : ?>
                    <?php while ($row = $products->fetch_assoc()) : ?>
                        <tr>
                            <td>
                                <?= $row["container_name"] ?? '-' ?>
                            </td>
                            <td><i class='bi bi-shop'></i> <?= htmlspecialchars($row["importer"] ?? '-') ?></td>
                            <td>
                                <a href='/produtos/byId/<?= htmlspecialchars($row["ID"]) ?>' title='Ver mais' class="d-flex gap-2">
                                    <i class='bi bi-tag'></i> <?= htmlspecialchars($row["code"]) ?>
                                </a>
                            </td>
                            <td><?= $row["description"] ?></td>
                            <td><?= $row["chinese_description"] ?></td>
                            <td>
                                <button type='button' class='btn btn-edit' data-bs-toggle='modal' data-bs-target='#updateProductModal' data-id='<?= htmlspecialchars($row["ID"]) ?>' data-code='<?= htmlspecialchars($row["code"]) ?>' data-ean='<?= $row["ean"] ? htmlspecialchars($row["ean"]) : '' ?>' data-importer='<?= htmlspecialchars($row["importer"]) ?>' data-description='<?= $row["description"] ? htmlspecialchars($row["description"]) : '' ?>' data-chinese-description='<?= $row["chinese_description"] ? htmlspecialchars($row["chinese_description"]) : '' ?>'>
                                    <i class='bi bi-pencil-square text-primary'></i>
                                </button>
                            </td>
                            <!-- Arquivar produtos -->
                            <td>
                                <button type='button' class='btn btn-archive' data-bs-toggle='modal' data-bs-target='#archiveProductModal' data-id='<?= htmlspecialchars($row["ID"]) ?>'>
                                    <i class='bi bi-archive text-warning'></i>
                                </button>
                            </td>
                            <td>
                                <button type='button' class='btn btn-delete' data-bs-toggle='modal' data-bs-target='#deleteProductModal' data-id='<?= htmlspecialchars($row["ID"]) ?>'>
                                    <i class='bi bi-trash text-danger'></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan='8'>Nenhum produto encontrado.</td>
                    </tr>
                <?php endif; ?>
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
        $isNextDisabled = !isset($products) || $products->num_rows <= 0 || $currentPage >= $pageCount;
        ?>

        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap" style="max-width: 250px; margin: 0 auto;">
            <form method="GET" class="d-flex align-items-center">
                <input type="hidden" name="code" value="<?= isset($_GET["code"]) ? $_GET["code"] : "" ?>">
                <input type="hidden" name="ean" value="<?= isset($_GET["ean"]) ? $_GET["ean"] : "" ?>">
                <input type="hidden" name="importer" value="<?= isset($_GET["importer"]) ? $_GET["importer"] : "" ?>">
                <input type="hidden" name="description" value="<?= isset($_GET["description"]) ? $_GET["description"] : "" ?>">
                <input type="hidden" name="chinese_description" value="<?= isset($_GET["chinese_description"]) ? $_GET["chinese_description"] : "" ?>">
                <input type="hidden" name="page" value="<?= $prevPage ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isPrevDisabled) ?> title="Voltar">
                    <i class="bi bi-arrow-left"></i>
                </button>
            </form>

            <span class="text-center">Página <?= $currentPage ?> de <?= $pageCount ?></span>

            <form method="GET">
                <input type="hidden" name="code" value="<?= isset($_GET["code"]) ? $_GET["code"] : "" ?>">
                <input type="hidden" name="ean" value="<?= isset($_GET["ean"]) ? $_GET["ean"] : "" ?>">
                <input type="hidden" name="importer" value="<?= isset($_GET["importer"]) ? $_GET["importer"] : "" ?>">
                <input type="hidden" name="description" value="<?= isset($_GET["description"]) ? $_GET["description"] : "" ?>">
                <input type="hidden" name="chinese_description" value="<?= isset($_GET["chinese_description"]) ? $_GET["chinese_description"] : "" ?>">
                <input type="hidden" name="page" value="<?= $nextPage ?>">
                <button class="btn bg-quaternary text-white" <?= isButtonDisabled($isNextDisabled) ?> title="Avançar">
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>
    <?php endif; ?>
</main>

<?php require "Components/StatusMessage.php" ?>

<div class="modal fade" id="archiveProductModal" tabindex="-1" aria-labelledby="archiveProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveProductModalLabel">Arquivar produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="archiveForm" method="post">
                <input type="hidden" name="_method" value="put">
                <input type="hidden" name="action" value="archive">
                <div class="modal-body">
                    <input type="hidden" name="ID" id="archiveProductId" value="">
                    <p>Tem certeza de que deseja arquivar este produto?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning" id="confirmArchiveBtn">Confirmar</button>
                </div>
            </form>
        </div>
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
                        <button type="submit" class="btn btn-custom">Salvar</button>
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
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="ID" id="productId">
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
                        <button type="submit" class="btn btn-custom">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProductLabel">Apagar produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelForm" method="post">
                <input type="hidden" name="_method" value="delete">
                <div class="modal-body">
                    <input type="hidden" name="ID" id="deleteProductId" value="">
                    <p>Tem certeza de que deseja apagar este produto?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="confirmCancelBtn">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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

    document.querySelectorAll('.btn-delete').forEach(function(button) {
        button.addEventListener('click', function() {
            var productId = this.getAttribute('data-id');
            document.getElementById('deleteProductId').value = productId;
        });
    });

    document.getElementById('importer').addEventListener('change', function() {
        this.form.submit();
    });

    document.querySelectorAll('.btn-archive').forEach(function(button) {
        button.addEventListener('click', function() {
            var productId = this.getAttribute('data-id');
            document.getElementById('archiveProductId').value = productId;
        });
    });
</script>
<?php
$content = ob_get_clean();
include "Components/Template.php";
?>