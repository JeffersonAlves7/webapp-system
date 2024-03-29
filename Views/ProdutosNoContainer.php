<?php
$pageTitle = "Containers";
ob_start();

require "Components/Header.php";
?>
<div class="container">
    <h1><?php echo $pageTitle ?> - Produtos no Container</h1>

    <!-- Tabela -->
    <!-- <form method="get" id="products-form" style="max-height: 400px; overflow-y: auto;">
    </form> -->

    <table class="table table-striped">
        <thead class="thead-dark" style="position: sticky; top: 0;">
            <tr>
                <th>Código</th>
                <th>Quantidade</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($products_in_container) && $products_in_container->num_rows > 0) {
                while ($row = $products_in_container->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['code'] ?></td>
                        <td><?php echo $row['quantity'] ?></td>
                        <td>
                            <form method="post" class="deleteProductForm" id="<?php echo "formProduct" . $row['product_ID'] ?>">
                                <input type="hidden" name="product_ID" value="<?php echo $row['product_ID'] ?>">
                                <input type="hidden" name="container_ID" value="<?php echo $row['container_ID'] ?>">
                                <button type='submit' class='btn'>
                                    <i class='bi bi-trash text-danger'></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan='3'>Nenhum produto encontrado neste container.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
    window.onload = () => {
        // Obtém a URL atual
        var currentUrl = window.location.href;

        // Adiciona a URL atual como parâmetro query
        var formAction = "/containers/deletarProduto?redirect=" + encodeURIComponent(currentUrl);

        // Define a ação do formulário
        document.querySelectorAll('.deleteProductForm').forEach((e) => e.setAttribute('action', formAction));
    }
</script>


<?php
$content = ob_get_clean();
include "Components/Template.php";
?>