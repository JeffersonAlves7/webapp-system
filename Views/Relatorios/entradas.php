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

            <h1 style="margin: 0;">Relatório de Entradas dos Últimos 12 meses</h1>
        </div>

        <button class="btn btn-custom" id="btn-saidas-diarias">Exportar em massa</button>
    </div>

    <div class="d-flex gap-4" id="chartSpace">
        <canvas id="myChart" width="400" height="150"></canvas>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById("myChart").getContext("2d");

    let labels = [];
    let datasets = [];

    function loadData() {
        fetch("/relatorios/entradas", {
                method: "POST",
            })
            .then(response => response.json())
            .then(data => {
                labels = Object.keys(data).reverse();
                const type = "bar";

                datasets = [{
                    label: "Entradas",
                    data: Object.values(data).reverse(),
                    backgroundColor: "rgb(75, 192, 192)",
                }];

                let myChart = new Chart(ctx, {
                    type,
                    data: {
                        labels,
                        datasets,
                    },
                });
            });
    }

    window.onload = function() {
        loadData();
    }
</script>

<?php
$content = ob_get_clean();
include "Components/Template.php";
?>