<?php
$pageTitle = "Reservas";
ob_start();
?>

<?php require "Components/Header.php" ?>

<div class="container">
    <h1 class="mt-4 mb-3"><?php echo $pageTitle ?></h1>
</div>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>

