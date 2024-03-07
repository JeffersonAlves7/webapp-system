<?php
$pageTitle = "Estoques";
ob_start();

require "Components/Header.php";
?>
<div class="container">
    <h1><?php echo $pageTitle ?></h1>
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-primary" href="/products/criar">Novo Produto</a>
    </div>
</div>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>