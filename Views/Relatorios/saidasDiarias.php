<?php
$pageTitle = "Relatórios";
ob_start();

require "Components/Header.php"
?>

<main>
    <div class="d-flex w-100 justify-content-between mt-4 mb-3">
        <div class="d-flex gap-4 align-items-center">
            <button id="go-back" class="btn btn-custom" data-url="/relatorios">
                <i class="bi bi-arrow-left"></i>
            </button>

            <h1 style="margin: 0;">Relatório de Saídas Diárias</h1>
        </div>

        <form action="/relatorios/exportarSaidasDiarias" method="POST" id="formExportar" target="_blank">
            <button class="btn btn-custom" id="btn-saidas-diarias" type="submit">Exportar em massa</button>
        </form>
    </div>

    <form class="d-flex mb-3 gap-4" style="max-width: 800px;">
        <input type="date" id="data-inicio" class="form-control" value="<?= isset($_GET["dataInicio"]) && !empty($_GET["dataInicio"]) ? $_GET["dataInicio"] : date("Y-m-d") ?>" name="dataInicio">
        <input type="date" id="data-fim" class="form-control" value="<?= isset($_GET["dataFim"]) && !empty($_GET["dataFim"]) ? $_GET["dataFim"] : date("Y-m-d") ?>" name="dataFim">

        <input type="text" id="pesquisa-cliente" class="form-control" value="<?= isset($_GET["cliente"]) && !empty($_GET["cliente"]) ? $_GET["cliente"] : "" ?>" placeholder="Pesquisar por cliente" name="cliente">
        <button class="btn btn-custom" id="btn-pesquisar">Pesquisar</button>
    </form>

    <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Quantidade</th>
                    <th>Operação</th>
                    <th>Cliente</th>
                    <th>Operador</th>
                    <th>Origem</th>
                    <th>Data</th>
                    <th>Observação</th>
                </tr>
            </thead>

            <tbody id="tbody-saidas-diarias">
                <?php while ($row = $dados->fetch_assoc()) : ?>
                    <tr>
                        <td><?= $row["code"] ?></td>
                        <td><?= $row["QUANTIDADE"] ?></td>
                        <td><?= $row["TIPO"] ?></td>
                        <td><?= $row["CLIENTE"] ?></td>
                        <td><?= $row["OPERADOR"] ?></td>
                        <td><?= $row["ORIGEM"] ?></td>
                        <td><?= $row["DATA"] ?></td>
                        <td><?= $row["OBSERVACAO"] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    const formExportar = document.getElementById("formExportar");
    const dataSaida = document.getElementById("data-saida");
    const pesquisaCliente = document.getElementById("pesquisa-cliente");

    formExportar.addEventListener("submit", (e) => {
        e.preventDefault();

        if (!formExportar.querySelector("input[name='dataSaida']")) {
            const dataSaidaInput = document.createElement("input");
            dataSaidaInput.type = "hidden";
            dataSaidaInput.name = "dataSaida";
            dataSaidaInput.value = dataSaida.value;

            formExportar.appendChild(dataSaidaInput);
        }

        if (!formExportar.querySelector("input[name='cliente']")) {
            const clienteInput = document.createElement("input");
            clienteInput.type = "hidden";
            clienteInput.name = "cliente";
            clienteInput.value = pesquisaCliente.value;

            formExportar.appendChild(clienteInput);
        }

        formExportar.submit();
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>