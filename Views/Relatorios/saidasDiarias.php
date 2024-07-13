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

    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
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

            <tfoot class="table-light" style="position: sticky; bottom: 0;">
                <tr>
                    <td>
                        <p>Total de Produtos: <span id="total-produtos"></span></p>
                    </td>
                    <td>
                        <p>Total de Saídas: <span id="total-saidas"></span></p>
                    </td>
                    <td>
                        <p>Total de Devoluções: <span id="total-devolucoes"></span></p>
                    </td>
                    <td colspan="5"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</main>

<script>
    const formExportar = document.getElementById("formExportar");
    const dataSaida = document.getElementById("data-saida");
    const pesquisaCliente = document.getElementById("pesquisa-cliente");

    formExportar.addEventListener("submit", (e) => {
        e.preventDefault();

        // Repita a logica mas agora para dataInicio e dataFim
        const dataInicio = document.getElementById("data-inicio");
        if (!formExportar.querySelector("input[name='dataInicio']")) {
            const dataInicioInput = document.createElement("input");
            dataInicioInput.type = "hidden";
            dataInicioInput.name = "dataInicio";
            dataInicioInput.value = dataInicio.value;

            formExportar.appendChild(dataInicioInput);
        }

        const dataFim = document.getElementById("data-fim");
        if (!formExportar.querySelector("input[name='dataFim']")) {
            const dataFimInput = document.createElement("input");
            dataFimInput.type = "hidden";
            dataFimInput.name = "dataFim";
            dataFimInput.value = dataFim.value;

            formExportar.appendChild(dataFimInput);
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

    // window.onload(() => {
    document.addEventListener("DOMContentLoaded", () => {
        // Carregar os totais
        const totalProdutos = document.getElementById("total-produtos");
        const totalSaidas = document.getElementById("total-saidas");
        const totalDevolucoes = document.getElementById("total-devolucoes");

        // Pegar todos os valores da tbody onde estão as saídas
        const tbodySaidas = document.getElementById("tbody-saidas-diarias");
        const saidas = tbodySaidas.querySelectorAll("tr");
        // Pegar todas as colunas e transformar em um json
        const saidasJson = Array.from(saidas).map(saida => {
            const colunas = saida.querySelectorAll("td");
            return {
                codigo: colunas[0].innerText,
                quantidade: colunas[1].innerText,
                tipo: colunas[2].innerText,
                cliente: colunas[3].innerText,
                operador: colunas[4].innerText,
                origem: colunas[5].innerText,
                data: colunas[6].innerText,
                observacao: colunas[7].innerText
            }
        });

        // Calcular os totais
        const totalSaidasValue = saidasJson.reduce((acc, saida) => {
            if (saida.tipo === "Saída") {
                acc += parseInt(saida.quantidade);
            }

            return acc;
        }, 0);

        const totalDevolucoesValue = saidasJson.reduce((acc, saida) => {
            if (saida.tipo === "Devolução") {
                acc += parseInt(saida.quantidade);
            }

            return acc;
        }, 0);

        const totalProdutosValue = saidasJson.reduce((acc, saida) => {
            if (!acc.includes(saida.codigo)) {
                acc.push(saida.codigo);
            }

            return acc;
        }, []).length;


        totalProdutos.innerText = totalProdutosValue;
        totalSaidas.innerText = totalSaidasValue;
        totalDevolucoes.innerText = totalDevolucoesValue;
    })
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>