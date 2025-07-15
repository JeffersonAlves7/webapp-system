<?php
// Controllers/EstoquesController.php

require_once "Models/Estoque.php";
require_once "Controllers/_Controller.php";
require_once "Managers/ConfigManager.php";
require_once "Utils/PhpExporter.php";

class EstoquesController extends _Controller
{
    private $estoquesModel;
    public function __construct()
    {
        parent::__construct("Estoques");
        $this->verifyReadPermission();
        $this->estoquesModel = new Estoque();
    }

    public function index()
    {
        $sucesso = isset($_SESSION["sucesso"]) && $_SESSION["sucesso"];
        unset($_SESSION["sucesso"]);
        $mensagem_erro = isset($_SESSION["mensagem_erro"]) ? $_SESSION["mensagem_erro"] : "";
        unset($_SESSION["mensagem_erro"]);

        $page = 1;
        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        }

        $estoque_ID = null;
        if (isset($_GET["estoque"]) && $_GET["estoque"] != "") {
            $estoque_ID = $_GET["estoque"];
        }

        $alert = 0.2;
        if (isset($_COOKIE["alerta"])) {
            $alert = $_COOKIE["alerta"] / 100;
        }

        $alert_filter = null;
        if (isset($_GET["alerta-filtro"]) && !empty($_GET["alerta-filtro"])) {
            $alert_filter = $_GET["alerta-filtro"];
        }

        $orderBy = "created_at";
        if (isset($_GET["orderBy"]) && !empty($_GET["orderBy"])) {
            $orderBy = $_GET["orderBy"];
            if ($orderBy == "codigo") {
                $orderBy = "code";
            }
        }
        if (isset($_GET["orderType"]) && !empty($_GET["orderType"]) && ($_GET["orderType"] == "asc" || $_GET["orderType"] == "desc")) {
            $orderType = $_GET["orderType"];
        }
        else{ 
            $orderType = "desc";
        }

        $nestJsEndpointPath = "/products";
        if ($estoque_ID == 1) {
            $nestJsEndpointPath = "/products/galpao";
        } elseif ($estoque_ID == 2) {
            $nestJsEndpointPath = "/products/loja";
        }

        $queryParams = [
            "page" => $page,
            "limit" => 50,
            "orderBy" => $orderBy,
            "orderType" => $orderType
        ];

        if (isset($_COOKIE["codigo"]) && $_COOKIE["codigo"] != "") {
            $queryParams["code"] = $_COOKIE["codigo"];
        }
        if (isset($_GET["importadora"]) && $_GET["importadora"] != "") {
            $queryParams["importer"] = $_GET["importadora"];
        }
        if (isset($_COOKIE["alerta"]) && $_COOKIE["alerta"] != "") {
            $queryParams["alerta"] = $_COOKIE["alerta"];
        }

        $fullNestJsUrl = ConfigManager::$NEST_SERVER . $nestJsEndpointPath . "?" . http_build_query($queryParams);

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
                "User-Agent: PHP EstoquesController Index"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        $products = [];
        $totalProdutos = 0;
        $pageCount = 1;
        $totalCaixas = 0;

        if ($err) {
            error_log("Erro cURL ao buscar produtos do NestJS: " . $err);
            $mensagem_erro = "Não foi possível carregar os dados de estoque do serviço externo.";
        } else {
            $nestJsResponseData = json_decode($response, true);

            if (
                json_last_error() === JSON_ERROR_NONE && is_array($nestJsResponseData) &&
                isset($nestJsResponseData["products"]) && is_array($nestJsResponseData["products"]) &&
                isset($nestJsResponseData["totalCount"]) &&
                isset($nestJsResponseData["pageCount"]) &&
                isset($nestJsResponseData["totalQuantityInStock"])
            ) {

                $productsRaw = $nestJsResponseData["products"];
                $totalProdutos = $nestJsResponseData["totalCount"];
                $pageCount = $nestJsResponseData["pageCount"];
                $totalCaixas = $nestJsResponseData["totalQuantityInStock"];

                // Mapeia os dados do NestJS para o formato esperado pela view PHP
                foreach ($productsRaw as $productData) {
                    $mappedProduct = [
                        "ID" => $productData["ID"] ?? null,
                        "codigo" => $productData["code"] ?? '',
                        "description" => $productData["description"] ?? '', // Mantendo original para reuso
                        "quantidade_entrada" => $productData["entry"]["quantity"] ?? 0,
                        "observacao" => $productData["description"] ?? '', // Usando 'description' como 'observacao'
                        "importadora" => $productData["importer"] ?? '',
                        "daysInStock" => $productData["daysInStock"] ?? 0, // Mantendo original para reuso
                        "giro_percentual" => $productData["giro_percentual"] ?? 0, // Mantendo original para reuso
                        "alerta" => $productData["alerta"] ?? 0, // Mantendo original para reuso
                    ];

                    // Inicializa quantidades do galpão e loja
                    $mappedProduct["quantity_galpao"] = 0;
                    $mappedProduct["quantity_loja"] = 0;

                    if (isset($productData["quantity_in_stock"]) && is_array($productData["quantity_in_stock"])) {
                        foreach ($productData["quantity_in_stock"] as $stockEntry) {
                            if (isset($stockEntry["stock_ID"]) && isset($stockEntry["quantity"])) {
                                if ($stockEntry["stock_ID"] == 1) { // Galpão
                                    $mappedProduct["quantity_galpao"] = $stockEntry["quantity"];
                                } elseif ($stockEntry["stock_ID"] == 2) { // Loja
                                    $mappedProduct["quantity_loja"] = $stockEntry["quantity"];
                                }
                            }
                        }
                    }

                    // Define o saldo atual com base no estoque selecionado ou total
                    if ($estoque_ID == 1) { // Galpão
                        $mappedProduct["saldo_atual"] = $mappedProduct["quantity_galpao"];
                        // O "container_de_origem" só é relevante para o galpão, e o NestJS o retorna em "entry.containers"
                        $mappedProduct["container_de_origem"] = $productData["entry"]["containers"] ?? '';
                    } elseif ($estoque_ID == 2) { // Loja
                        $mappedProduct["saldo_atual"] = $mappedProduct["quantity_loja"];
                        // Para a loja, o container de origem não é retornado na mesma estrutura
                        // ou é uma transação que não tem container direto na entrada
                        $mappedProduct["container_de_origem"] = ''; // Ou defina uma lógica específica se tiver
                    } else { // Geral (sem filtro por estoque específico)
                        $mappedProduct["saldo_atual"] = ($mappedProduct["quantity_galpao"] ?? 0) + ($mappedProduct["quantity_loja"] ?? 0);
                        // No caso geral, se o container de origem é relevante para o galpão, inclua
                        $mappedProduct["container_de_origem"] = $productData["entry"]["containers"] ?? '';
                    }

                    // A data de entrada no NestJS é o created_at do produto, que é mais como "data de cadastro"
                    // ou a data da última transação. A view esperava "data_de_entrada" de um `entry`.
                    // Vamos usar o `created_at` do produto e formatar.
                    $mappedProduct["data_de_entrada"] = $productData["entryDate"] ?? null;

                    // 'dias_em_estoque', 'giro', 'quantidade_para_alerta' já vêm prontos do NestJS, só renomeamos
                    $mappedProduct["dias_em_estoque"] = $productData["daysInStock"] ?? 0;
                    $mappedProduct["giro"] = $productData["giro_percentual"] ?? 0;
                    $mappedProduct["quantidade_para_alerta"] = $productData["alerta"] ?? 0;


                    $products[] = $mappedProduct;
                }
            } else {
                error_log("Resposta NestJS inválida ou estrutura ausente: " . $response);
                $mensagem_erro = "Formato de dados inválido recebido do serviço externo.";
            }
        }

        $stocks = $this->estoquesModel->getAll();

        return $this->view(
            "Estoques",
            [
                "estoques" => $stocks,
                "produtos" => $products,
                "page" => $page,
                "pageCount" => $pageCount,
                "sucesso" => $sucesso,
                "mensagem_erro" => $mensagem_erro,
                "totalProdutos"  => $totalProdutos,
                "totalCaixas" => $totalCaixas,
                "orderType" => $orderType,
                "orderBy" => $orderBy
            ]
        );
    }

    public function getAll()
    {
        header("Content-type: application/json");

        $stocks = $this->estoquesModel->getAll();
        $stocks_arr = array();
        if ($stocks->num_rows > 0) {
            while ($stock = $stocks->fetch_assoc()) {
                array_push($stocks_arr, $stock);
            }
        }

        echo json_encode(array("stocks" => $stocks_arr));
        exit(0);
    }

    public function exportar()
    {
        $estoque_ID = null;
        if (isset($_GET["estoque"]) && $_GET["estoque"] != "") {
            $estoque_ID = $_GET["estoque"];
        }

        $nestJsEndpointPath = "/products";
        // Ajuste: 1 agora é Galpão, 2 agora é Loja
        if ($estoque_ID == 1) {
            $nestJsEndpointPath = "/products/galpao";
        } elseif ($estoque_ID == 2) {
            $nestJsEndpointPath = "/products/loja";
        }

        $queryParams = [];
        if (isset($_COOKIE["codigo"]) && $_COOKIE["codigo"] != "") {
            $queryParams["code"] = $_COOKIE["codigo"];
        }
        if (isset($_GET["importadora"]) && $_GET["importadora"] != "") {
            $queryParams["importer"] = $_GET["importer"];
        }
        // Removemos o limite fixo aqui, pois vamos iterar por todas as páginas
        // $queryParams["limit"] = 999999; 

        $productsToExport = [];
        $currentPage = 1;
        $pageSize = 50; // Use o mesmo limite por página que a listagem normal ou um limite alto para menos requisições
        $totalPageCount = 1; // Inicializa para garantir que o loop comece

        do {
            $queryParams["page"] = $currentPage;
            $queryParams["limit"] = $pageSize; // Garante que o limite seja passado em cada requisição

            $fullNestJsUrl = ConfigManager::$NEST_SERVER . $nestJsEndpointPath . "?" . http_build_query($queryParams);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $fullNestJsUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "User-Agent: PHP EstoquesController Export"
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                error_log("Erro cURL ao buscar produtos para exportação do NestJS (página {$currentPage}): " . $err);
                // Em caso de erro em qualquer página, podemos parar e exportar o que já foi coletado
                break;
            } else {
                $nestJsResponseData = json_decode($response, true);
                if (
                    json_last_error() === JSON_ERROR_NONE && is_array($nestJsResponseData) &&
                    isset($nestJsResponseData["products"]) && is_array($nestJsResponseData["products"]) &&
                    isset($nestJsResponseData["pageCount"])
                ) {

                    $productsRaw = $nestJsResponseData["products"];
                    $totalPageCount = $nestJsResponseData["pageCount"]; // Atualiza o total de páginas

                    foreach ($productsRaw as $productData) {
                        $mappedProduct = [
                            "code" => $productData["code"] ?? '',
                            "description" => $productData["description"] ?? '',
                            "importer" => $productData["importer"] ?? '',
                            "daysInStock" => $productData["daysInStock"] ?? 0 // Incluindo Dias Em Estoque
                        ];

                        // Inicializa quantidades do galpão e loja
                        $mappedProduct["quantity_galpao"] = 0;
                        $mappedProduct["quantity_loja"] = 0;

                        if (isset($productData["quantity_in_stock"]) && is_array($productData["quantity_in_stock"])) {
                            foreach ($productData["quantity_in_stock"] as $stockEntry) {
                                if (isset($stockEntry["stock_ID"]) && isset($stockEntry["quantity"])) {
                                    // Ajuste: 1 agora é Galpão, 2 agora é Loja
                                    if ($stockEntry["stock_ID"] == 1) { // Galpão
                                        $mappedProduct["quantity_galpao"] = $stockEntry["quantity"];
                                    } elseif ($stockEntry["stock_ID"] == 2) { // Loja
                                        $mappedProduct["quantity_loja"] = $stockEntry["quantity"];
                                    }
                                }
                            }
                        }
                        $productsToExport[] = $mappedProduct;
                    }
                } else {
                    error_log("Resposta NestJS inválida ou estrutura ausente para exportação (página {$currentPage}): " . $response);
                    break; // Sai do loop em caso de resposta inválida
                }
            }
            $currentPage++; // Avança para a próxima página
        } while ($currentPage <= $totalPageCount); // Continua enquanto houver páginas a buscar

        if (empty($productsToExport)) {
            // Se nenhuma produto foi coletado, exporta um arquivo vazio ou com mensagem de erro
            PhpExporter::exportToExcel([], [], "erro_estoque_exportacao");
            return;
        }

        // Headers para o Excel, agora incluindo "Dias Em Estoque"
        $excelHeaders = ["Código", "Descrição", "Quantidade Galpão", "Quantidade Loja", "Importadora", "Dias Em Estoque"];

        PhpExporter::exportToExcel(
            $excelHeaders,
            array_map(function ($product) {
                return [
                    $product["code"],
                    $product["description"],
                    $product["quantity_galpao"],
                    $product["quantity_loja"],
                    $product["importer"],
                    $product["daysInStock"] // Valor de "Dias Em Estoque"
                ];
            }, $productsToExport),
            "estoqueTotal"
        );

        return;
    }
}
