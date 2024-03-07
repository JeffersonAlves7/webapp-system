<?php
$pageTitle = "Home";
ob_start();
?>

<?php require "Components/Header.php" ?>

<div class="container">
    <h1 class="mt-4 mb-3"><?php echo $product["code"] ?> - <?php echo $product["description"] ?></h1>
    <div class="d-flex gap-3">
        <p>Importadora: <?php echo $product["importer"] ?></p>
        <?php if (!empty($product["ean"])) {
            echo "<p>EAN: " . $product["ean"] . "</p>";
        } ?>
    </div>

</div>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>