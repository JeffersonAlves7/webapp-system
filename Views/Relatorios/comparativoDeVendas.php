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

            <h1 style="margin: 0;">Relatório de Comparativo de Vendas</h1>
        </div>

        <button class="btn btn-custom" id="btn-saidas-diarias">Exportar em massa</button>
    </div>

    <div class="d-flex gap-4 mb-4 mt-4">
        <div class="d-flex gap-4" style="max-width: 400px; max-height: 50px">
            <input type="month" class="form-control" id="mes" value="<?= date("Y-m") ?>">
            <button class="btn btn-custom" id="btn-pesquisar">Adicionar</button>
        </div>

        <div class="d-flex gap-4 flex-wrap" id="meses-selecionados">
        </div>
    </div>

    <div class="d-flex gap-4" id="chartSpace">
        <canvas id="myChart" width="400" height="150"></canvas>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById("myChart").getContext("2d");
    const mesesSelecionados = document.getElementById("meses-selecionados");
    let labels = Array.from({
        length: 31
    }, (_, i) => (i + 1).toString());
    let datasets = [];

    let myChart = new Chart(ctx, {
        type: "line",
        data: {
            labels,
            datasets,
        },
    });

    function getMonths() {
        let meses = document.querySelectorAll("#meses-selecionados span");
        let mesesArray = [];

        meses.forEach(mes => {
            mesesArray.push(mes.textContent);
        });

        return mesesArray;
    }

    function insertMonth(date) {
        if (getMonths().includes(date)) {
            return;
        }

        let div = document.createElement("div");
        div.classList.add("d-flex", "gap-2", "align-items-center");

        let span = document.createElement("span");
        span.textContent = date;

        let button = document.createElement("button");
        button.classList.add("btn", "btn-danger");
        button.innerHTML = "<i class='bi bi-x'></i>";

        div.appendChild(span);
        div.appendChild(button);

        mesesSelecionados.appendChild(div);

        button.addEventListener("click", function() {
            removeMonth(date);
        });
    }

    function loadMonthsData() {
        let meses = getMonths();

        fetch("/relatorios/comparativoDeVendas", {
                method: "POST",
                body: JSON.stringify({
                    meses: meses
                }),
            })
            .then(response => response.json())
            .then(data => {
                const dates = Object.keys(data);

                let dias = Array.from({
                    length: 31
                }, (_, i) => i + 1);
                let datasets = [];

                dates.forEach(date => {
                    let dataset = {
                        label: date,
                        data: [],
                        fill: false,
                        borderColor: "rgb(75, 192, 192)",
                        tension: 0.1,
                    };

                    dias.forEach(dia => {
                        dataset.data.push(data[date][dia]);
                    });

                    datasets.push(dataset);
                });

                myChart.destroy();

                myChart = new Chart(ctx, {
                    type: "line",
                    data: {
                        labels,
                        datasets,
                    },
                });
            });
    }

    function removeMonth(date) {
        let meses = document.querySelectorAll("#meses-selecionados span");

        meses.forEach(mes => {
            if (mes.textContent == date) {
                mes.parentElement.remove();
            }
        });

        loadMonthsData();
    }

    window.onload = function() {
        // Mes atual
        insertMonth("<?= date("Y-m") ?>");

        // Mes anterior
        insertMonth("<?= date("Y-m", strtotime("-1 month")) ?>");

        loadMonthsData();
    }

    document.getElementById("btn-pesquisar").addEventListener("click", function() {
        let mes = document.getElementById("mes").value;
        insertMonth(mes);
        loadMonthsData();
    });
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>