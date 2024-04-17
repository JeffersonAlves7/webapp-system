<?php
$pageTitle = "Painel";

ob_start();

require "Components/Header.php";
?>

<main>
    <div class="d-flex gap-4 align-items-center">
        <button id="go-back" class="btn btn-custom">
            <i class="bi bi-arrow-left"></i>
        </button>

        <h1 class="mt-4 mb-3"><?= $pageTitle ?> - Grupos</h1>
    </div>


    <?php include "Components/StatusMessage.php"; ?>
</main>


<?php
$content = ob_get_clean();
include "Components/Template.php";
?>