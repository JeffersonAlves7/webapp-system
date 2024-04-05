<?php
$pageTitle = "Embarques";
ob_start();

require "Components/Header.php";
?>
<main>
    <h1><?= $pageTitle ?> - Produtos no Container</h1>

</main>

<script>

</script>


<?php
$content = ob_get_clean();
include "Components/Template.php";
?>