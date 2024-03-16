<?php
$pageTitle = "Reservas";
ob_start();
?>

<?php require "Components/Header.php" ?>

<div class="container">
    <h1 class="mt-4 mb-3"><?php echo $pageTitle ?></h1>

    <!-- Estoques -->
    <div class="d-flex gap-3">
        <form method="get">
            <button type="submit" class="btn btn-custom <?php echo isset($_GET["estoque"]) ? "" : "active" ?>">Geral</button>
        </form>

        <?php
        if (isset($stocks) && $stocks->num_rows > 0) {
            while ($estoque = $stocks->fetch_assoc()) {
                $name = $estoque["name"];
                $ID = $estoque["ID"];
                $active = (isset($_GET["estoque"]) && $_GET["estoque"] == "$ID" ? "active" : "");

                echo "<form method='get'>
                    <input type='hidden' name='estoque' value='$ID'/>
                    <button type='submit' class='btn btn-custom $active'>$name</button>
                </form>";
            }
        }
        ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>