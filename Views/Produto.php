<?php
$pageTitle = "Home";
ob_start();
?>

<?php require "Components/Header.php" ?>

<div class="container">
    <h1 class="mt-4 mb-3"><?php echo $produto["code"] ?></h1>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="descricao-tab" data-bs-toggle="tab" data-bs-target="#descricao" type="button" role="tab" aria-controls="descricao" aria-selected="true">Descrição</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="estoque-tab" data-bs-toggle="tab" data-bs-target="#estoque" type="button" role="tab" aria-controls="estoque" aria-selected="false">Estoque</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="transacoes-tab" data-bs-toggle="tab" data-bs-target="#transacoes" type="button" role="tab" aria-controls="transacoes" aria-selected="false">Transações</button>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="descricao" role="tabpanel" aria-labelledby="descricao-tab">
            <?php
            if (isset($produto["description"])) {
                $description = htmlspecialchars($produto["description"]);
                echo "<p class='description'>" . $description . "</p>";
            } else {
                echo "<p>Nenhuma informação de descrição disponível.</p>";
            }
            ?>
        </div>
        <div class="tab-pane fade" id="estoque" role="tabpanel" aria-labelledby="estoque-tab">
            <?php if ($quantidade_em_estoque->num_rows > 0) { ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nome do Estoque</th>
                                <th>Quantidade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($dados = $quantidade_em_estoque->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $dados["stock_name"]; ?></td>
                                    <td><?php echo $dados["quantity"]; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <p>Nenhuma informação de estoque disponível.</p>
            <?php } ?>
        </div>
        <div class="tab-pane fade" id="transacoes" role="tabpanel" aria-labelledby="transacoes-tab">
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>

<script>
    var tabs = new bootstrap.Tab(document.getElementById('myTab'));
    var tabContent = new bootstrap.TabContent(document.getElementById('myTabContent'));
</script>