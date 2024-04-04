<?php
if (isset($sucesso) && $sucesso) {
?>
    <div id="successAlert" class="alert alert-success alert-dismissible fade show fixed-bottom mx-auto my-3" role="alert" style="max-width: 600px;">
        <strong>Sucesso!</strong> ao realizar ação.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
        // Fecha o alerta de sucesso automaticamente após 5 segundos
        setTimeout(function() {
            var successAlert = document.getElementById('successAlert');
            successAlert.remove();
        }, 5000);
    </script>
<?php
} elseif (!empty($mensagem_erro)) { // Verifica se a mensagem de erro não está vazia
?>
    <div id="errorAlert" class="alert alert-danger alert-dismissible fade show fixed-bottom mx-auto my-3" role="alert" style="max-width: 600px;">
        <strong>Erro!</strong> <?php echo $mensagem_erro; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
        // Fecha o alerta de erro automaticamente após 5 segundos
        setTimeout(function() {
            var errorAlert = document.getElementById('errorAlert');
            errorAlert.remove();
        }, 5000);
    </script>
<?php } ?>