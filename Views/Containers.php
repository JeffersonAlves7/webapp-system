<?php
$pageTitle = "Containers";
ob_start();

require "Components/Header.php";
?>
<div class="container">
    <h1 class="mt-4 mb-3"><?php echo $pageTitle ?></h1>

    <!-- Tabela -->
    <form method="get" class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-striped">
            <thead class="thead-dark" style="position: sticky; top: 0;">
                <tr>
                    <th><i class="bi bi-info-circle"></i></th>
                    <th><i class="bi bi-tag"></i> Código</th>
                    <th><i class="bi bi-calendar-date"></i> Data</th>
                    <th><i class='bi bi-trash'></i></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($containers) && $containers->num_rows > 0) {
                    while ($row = $containers->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <a href="/containers/produtos/<?php echo $row['ID'] ?>">
                                    <i class='bi bi-zoom-in'></i>
                                </a>
                            </td>
                            <td><?php echo $row['name'] ?></td>
                            <td><?php echo $row['created_at'] ?></td>
                            <td>
                                <a href="/containers/deletar/<?php echo $row['ID'] ?>">
                                    <button type='button' class='btn'>
                                        <i class='bi bi-trash text-danger'></i>
                                    </button>
                                </a>
                            </td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr>
                        <td colspan='10'>Nenhum container encontrado.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <button type="submit" hidden></button>
    </form>
</div>

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