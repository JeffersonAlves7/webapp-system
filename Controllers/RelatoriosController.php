<?php
// Controllers/RelatoriosController.php

require_once "Controllers/_Controller.php";
require_once "Utils/PhpExporter.php";
require_once "Models/Relatorios.php";

class RelatoriosController extends _Controller
{
    private $relatorios;
    // Adicione a URL base do NestJS aqui também, se ainda não estiver
    private $nestjsBaseUrl = "http://host.docker.internal:3000";

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

        if (isset($_GET["dataInicio"]) || isset($_GET["dataFim"])) {
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
                        $estoque["QUANTIDADE_DE_ALERTA"]
                    ];
                }, $dados),
                "EstoqueMinimo.pdf"
            );

            return;
        }
    }

    public function movimentacoes()
    {
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

            PhpExporter::exportToExcel(
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

            foreach ($dados as $key => $value) {
                $dias = $value;
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

    public function semSaida()
    {
        $this->verifyReadPermission();

        $sucesso = isset($_SESSION["sucesso"]) && $_SESSION["sucesso"];
        unset($_SESSION["sucesso"]);
        $mensagem_erro = isset($_SESSION["mensagem_erro"]) ? $_SESSION["mensagem_erro"] : "";
        unset($_SESSION["mensagem_erro"]);

        $page = 1;
        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        }

        // Parâmetros de filtro
        $code = $_GET["code"] ?? '';
        $importer = $_GET["importer"] ?? '';

        $orderBy = "ID"; // Ordem padrão para 'not-selled' no NestJS
        $orderType = "desc";
        if (isset($_GET["orderBy"]) && !empty($_GET["orderBy"])) {
            $orderBy = $_GET["orderBy"];
            if ($orderBy == "codigo") {
                $orderBy = "code"; // Mapeia para o campo do NestJS
            }
        }
        if (isset($_GET["orderType"]) && !empty($_GET["orderType"]) && ($_GET["orderType"] == "asc" || $_GET["orderType"] == "desc")) {
            $orderType = $_GET["orderType"];
        }

        $queryParams = [
            "page" => $page,
            "limit" => 20, // Limite padrão da função NestJS
            "orderBy" => $orderBy,
            "orderType" => $orderType
        ];

        // Adiciona filtros se existirem
        if (!empty($code)) {
            $queryParams["code"] = $code;
        }
        if (!empty($importer)) {
            $queryParams["importer"] = $importer;
        }

        $fullNestJsUrl = $this->nestjsBaseUrl . "/products/notselled?" . http_build_query($queryParams);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $fullNestJsUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "User-Agent: PHP RelatoriosController SemSaida"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        $products = [];
        $totalProdutos = 0;
        $pageCount = 1;
        $totalCaixas = 0; // A função NestJS retorna 0 para totalQuantityInStock

        if ($err) {
            error_log("Erro cURL ao buscar produtos sem saída do NestJS: " . $err);
            $mensagem_erro = "Não foi possível carregar os dados de produtos sem saída do serviço externo.";
        } else {
            $nestJsResponseData = json_decode($response, true);

            if (
                json_last_error() === JSON_ERROR_NONE && is_array($nestJsResponseData) &&
                isset($nestJsResponseData["products"]) && is_array($nestJsResponseData["products"]) &&
                isset($nestJsResponseData["totalCount"]) &&
                isset($nestJsResponseData["pageCount"])
            ) {

                $productsRaw = $nestJsResponseData["products"];
                $totalProdutos = $nestJsResponseData["totalCount"];
                $pageCount = $nestJsResponseData["pageCount"];
                // totalQuantityInStock é 0 na resposta do NestJS para esta rota
                $totalCaixas = $nestJsResponseData["totalQuantityInStock"] ?? 0;
                $products = $productsRaw;
                $totalProductsInGalpaoUnfiltered = $nestJsResponseData["totalProductsInGalpaoUnfiltered"] ?? 0;


                // Mapeia os dados do NestJS para o formato esperado pela view PHP
                // foreach ($productsRaw as $productData) {
                //     $mappedProduct = [
                //         "ID" => $productData["ID"] ?? null,
                //         "codigo" => $productData["code"] ?? '',
                //         "description" => $productData["description"] ?? '',
                //         "chineseDescription" => $productData["chineseDescription"] ?? '',
                //         "importadora" => $productData["importer"] ?? '',
                //         "quantidade_entrada" => $productData["entry"]["quantity"] ?? 0,
                //         "container_de_origem" => $productData["entry"]["containers"] ?? '',
                //         "dias_em_estoque" => $productData["daysInStock"] ?? 0,
                //         "data_de_entrada" => $productData["productsInContainer"][0]["created_at"] ?? null,
                //         "saldo_atual" => $productData["quantity_in_stock"][0]["quantity"] ?? 0,
                //     ];
                //     $products[] = $mappedProduct;
                // }
            } else {
                error_log("Resposta NestJS inválida ou estrutura ausente para produtos sem saída: " . $response);
                $mensagem_erro = "Formato de dados inválido recebido do serviço externo para produtos sem saída.";
            }
        }

        return $this->view(
            "Relatorios/semSaida", // Nova view específica para este relatório
            [
                // "estoques" => $stocks, // Se precisar de filtros de estoque, adicione aqui
                "produtos" => $products,
                "page" => $page,
                "pageCount" => $pageCount,
                "sucesso" => $sucesso,
                "mensagem_erro" => $mensagem_erro,
                "totalProdutos"  => $totalProdutos,
                "totalCaixas" => $totalCaixas,
                "totalProductsInGalpaoUnfiltered" => $totalProductsInGalpaoUnfiltered
            ]
        );
    }
}
