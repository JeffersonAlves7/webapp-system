<?php
require_once "Controllers/_Controller.php";
require_once "Utils/PhpExporter.php";
require_once "Models/Relatorios.php";

class RelatoriosController extends _Controller
{
    private $relatorios;

    public function __construct()
    {
        parent::__construct("Relatorios");
        $this->verifyReadPermission();
        $this->relatorios = new Relatorios();
    }

    public function index()
    {
        $this->view("Relatorios/index");
    }

    public function saidasDiarias()
    {
        $dataInicio = null;
        $dataFim = null;
        $where = "1";

        if (!empty($dataInicio) && !empty($dataFim)) {
            $where .= " AND DATE(t.created_at) BETWEEN '$dataInicio' AND '$dataFim'";
        } else if (isset($_GET["dataInicio"]) || isset($_GET["dataFim"])) {
            if (isset($_GET["dataInicio"]) && !empty($_GET["dataInicio"])) {
                $dataInicio = $_GET["dataInicio"];
                $where .= " AND DATE(t.created_at) >= '$dataInicio'";
            }
            if (isset($_GET["dataFim"]) && !empty($_GET["dataFim"])) {
                $dataFim = $_GET["dataFim"];
                $where .= " AND DATE(t.created_at) <= '$dataFim'";
            }
        } else {
            $dataInicio = date("Y-m-d");

            $where .= " AND DATE(t.created_at) = '$dataInicio'";
        }

        if (isset($_GET["cliente"]) && !empty($_GET["cliente"])) {
            $cliente = $_GET["cliente"];
            $where .= " AND t.client_name LIKE '%$cliente%'";
        }

        $dados = $this->relatorios->saidasDiarias($where);

        $this->view("Relatorios/saidasDiarias", ["dados" => $dados]);
    }

    public function exportarSaidasDiarias()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            header("Content-Type: application/json");

            $dataInicio = $_POST["dataInicio"];
            $dataFim = $_POST["dataFim"];
            $cliente = $_POST["cliente"];

            $where = "1";


            if (!empty($dataInicio) && !empty($dataFim)) {
                $where .= " AND DATE(t.created_at) BETWEEN '$dataInicio' AND '$dataFim'";
            } else if (isset($_GET["dataInicio"]) || isset($_GET["dataFim"])) {
                if (isset($_GET["dataInicio"]) && !empty($_GET["dataInicio"])) {
                    $dataInicio = $_GET["dataInicio"];
                    $where .= " AND DATE(t.created_at) >= '$dataInicio'";
                }
                if (isset($_GET["dataFim"]) && !empty($_GET["dataFim"])) {
                    $dataFim = $_GET["dataFim"];
                    $where .= " AND DATE(t.created_at) <= '$dataFim'";
                }
            } else {
                $dataInicio = date("Y-m-d");

                $where .= " AND DATE(t.created_at) = '$dataInicio'";
            }

            if (isset($_GET["cliente"]) && !empty($_GET["cliente"])) {
                $cliente = $_GET["cliente"];
                $where .= " AND t.client_name LIKE '%$cliente%'";
            }

            $dados = $this->relatorios->saidasDiarias($where);
            $dados = $dados->fetch_all(MYSQLI_ASSOC);

            if (empty($dados)) {
                echo json_encode(["erro" => "Nenhum dado encontrado"]);
                exit;
            }

            $pdf = PhpExporter::exportToExcel(
                ['Código', 'Quantidade', 'Operação', 'Cliente', 'Operador', 'Origem', 'Data', 'Observação'],
                array_map(function ($saida) {
                    return [
                        $saida["code"],
                        $saida["QUANTIDADE"],
                        $saida["TIPO"],
                        $saida["CLIENTE"],
                        $saida["OPERADOR"],
                        $saida["ORIGEM"],
                        $saida["DATA"],
                        $saida["OBSERVACAO"]
                    ];
                }, $dados),
                "SaidasDiarias-$dataInicio-$dataFim"
            );

            return;
        }
    }

    public function estoqueMinimo()
    {
        $page = 1;
        $limit = 60;
        $porcentagem = 0.20;

        if (isset($_GET["page"]) && !empty($_GET["page"])) {
            $page = $_GET["page"];
        }

        if (isset($_COOKIE["alerta"]) && !empty($_COOKIE["alerta"])) {
            $porcentagem = $_COOKIE["alerta"];
        }

        $dados = $this->relatorios->estoqueMinimo($page, $limit, $porcentagem);

        $this->view("Relatorios/estoqueMinimo", [
            "dados" => $dados["dados"],
            "page" => $page,
            "pageCount" => $dados["pageCount"],
            "porcentagem" => $porcentagem
        ]);
    }

    public function exportarEstoqueMinimo()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            header("Content-Type: application/json");

            $page = 1;
            $limit = 100000000000000000000;

            $porcentagem = 0.20;
            if (isset($_COOKIE["alerta"]) && !empty($_COOKIE["alerta"])) {
                $porcentagem = $_COOKIE["alerta"];
            }

            if (isset($_COOKIE["porcentagemParaAlerta"]) && !empty($_GET["porcentagemParaAlerta"])) {
                $porcentagem = $_COOKIE["porcentagemParaAlerta"];
            }

            $dados = $this->relatorios->estoqueMinimo($page, $limit, $porcentagem);
            $dados = $dados["dados"];

            if (empty($dados)) {
                echo json_encode(["erro" => "Nenhum dado encontrado"]);
                exit;
            }

            $pdf = PhpExporter::exportToExcel(
                ['Código', 'Quantidade de Entrada', 'Saldo Atual', 'Quantidade de Alerta'],
                array_map(function ($estoque) {
                    return [
                        $estoque["CODIGO"],
                        $estoque["ENTRADA"],
                        $estoque["SALDO"],
                        $estoque["QUANTIDADE DE ALERTA"]
                    ];
                }, $dados),
                "EstoqueMinimo.pdf"
            );

            return;
        }
    }

    public function movimentacoes()
    {
        // Exemplo: ?dataMovimentacao=2021-09
        $dataMovimentacao = null;
        $page = 1;
        $limit = 30;

        if (isset($_GET["dataMovimentacao"]) && !empty($_GET["dataMovimentacao"])) {
            $dataMovimentacao = $_GET["dataMovimentacao"];
        } else {
            $dataMovimentacao = date("Y-m");
        }

        if (isset($_GET["page"]) && !empty($_GET["page"])) {
            $page = $_GET["page"];
        }

        $movimentacoes = $this->relatorios->movimentacoes(
            $dataMovimentacao,
            $page,
            $limit
        );


        $this->view("Relatorios/movimentacoes", [
            "dados" => $movimentacoes["dados"],
            "page" => $page,
            "pageCount" => $movimentacoes["pageCount"],
        ]);
    }

    public function exportarMovimentacoes()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            header("Content-Type: application/json");

            $dataMovimentacao = $_POST["dataMovimentacao"];

            $page = 1;

            $results = $this->relatorios->movimentacoes($dataMovimentacao, $page, 1000000);

            $dados = $results["dados"];
            $pageCount = $results["pageCount"];

            if (empty($dados)) {
                echo json_encode(["erro" => "Nenhum dado encontrado"]);
                exit;
            }

            for ($i = 2; $i <= $pageCount; $i++) {
                $results = $this->relatorios->movimentacoes($dataMovimentacao, $i, 1000000);
                $dados = array_merge($dados, $results["dados"]);
            }

            $pdf = PhpExporter::exportToExcel(
                ['Código', 'Saídas', 'Percentual', 'Estoque Atual'],
                array_map(function ($movimentacao) {
                    return [
                        $movimentacao["CODIGO"],
                        $movimentacao["SAIDAS"],
                        $movimentacao["PERCENTUAL"] . "%",
                        $movimentacao["ESTOQUE"]
                    ];
                }, $dados),
                "Movimentacoes-$dataMovimentacao.pdf"
            );

            return;
        }
    }

    public function comparativoDeVendas()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            header("Content-Type: application/json");

            $data = json_decode(file_get_contents("php://input"), true);
            $meses = $data["meses"];

            $dados = $this->relatorios->comparativoDeVendas($meses);

            // Para cada dia do mês, se não houver vendas, adicionar um array com o valor 0
            // para que o gráfico não fique com "buracos"
            // Key = '2024-05'
            // Value = Array ([26] => 75)
            // A ideia eh que ele complete os dias vazios
            foreach ($dados as $key => $value) {
                $dias = $value;
                // Fixar para sempre 31 dias
                $diasDoMes = 31;

                for ($i = 1; $i <= $diasDoMes; $i++) {
                    if (!isset($dias[$i])) {
                        $dias[$i] = 0;
                    }
                }

                ksort($dias);
                $dados[$key] = $dias;
            }

            echo json_encode($dados);
            exit;
        }

        $this->view("Relatorios/comparativoDeVendas");
    }

    public function entradas()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            header("Content-Type: application/json");


            $dados = $this->relatorios->entradas();

            // Para cada dado
            // {2024-4: 100, 2024-5: 50}
            // Preciso que ele preencha os meses vazios
            // Sendo que precisa ter do mes atual até 12 meses atras
            // {2024-4: 100, 2024-5: 50, 2024-6: 0, 2024-7: 0, 2024-8: 0, 2024-9: 0, 2024-10: 0, 2024-11: 0, 2024-12: 0, 2025-1: 0, 2025-2: 0, 2025-3: 0}

            $meses = 12;
            $dataAtual = date("Y-m");
            $data = explode("-", $dataAtual);
            $ano = $data[0];
            $mes = $data[1];

            $dadosFinais = [];

            for ($i = 0; $i < $meses; $i++) {
                $mesAtual = $mes - $i;
                $anoAtual = $ano;

                if ($mesAtual <= 0) {
                    $mesAtual = 12 + $mesAtual;
                    $anoAtual = $ano - 1;
                }

                $mesAtual = str_pad($mesAtual, 2, "0", STR_PAD_LEFT);
                $dataAtual = "$anoAtual-$mesAtual";

                if (!isset($dados[$dataAtual])) {
                    $dadosFinais[$dataAtual] = 0;
                } else {
                    $dadosFinais[$dataAtual] = $dados[$dataAtual];
                }
            }

            echo json_encode($dadosFinais);
            exit;
        }

        $this->view("Relatorios/entradas");
    }
}
