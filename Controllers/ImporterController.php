<?php

require_once "Controllers/_Controller.php";
require_once "Utils/PhpExcel.php";
require_once "Models/Importer.php";
require_once "Models/Lancamento.php";

class ImporterController extends _Controller
{
    public $importerModel;
    public $lancamentoModel;

    public function __construct()
    {
        parent::__construct("Lancamento");

        $this->importerModel = new Importer();
        $this->lancamentoModel = new Lancamento();
    }

    public function importarSaida()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                $this->verifyWritePermission();

                $file = $_FILES['file'];
                $rows = PhpExcel::read(($file['tmp_name']));

                if (count($rows) == 0) {
                    $_SESSION['mensagem_erro'] = "Nenhum dado encontrado na planilha!";
                    header("Location: " . $_SERVER["REQUEST_URI"]);
                    exit();
                }

                echo PhpExcel::formatAsTable($rows);

                array_shift($rows); // Remove o cabeçalho

                $products = [];

                foreach ($rows as $row) {
                    // $ean = $row[0];
                    $code = $row[1];
                    $importer = $this->validateImporter($row[2]);
                    $quantity = $row[3];
                    $stock = $this->validateStock($row[4]);
                    $client = $row[5];
                    $observation = $row[6];

                    $product = $this->importerModel->getProductByCodeAndImporter($code, $importer);

                    if (!$product) {
                        $_SESSION['mensagem_erro'] = "Produto não encontrado: $code - $importer";
                        header("Location: " . $_SERVER["REQUEST_URI"]);
                        exit();
                    }

                    $productQuantity = $this->importerModel->getProductQuantity($product['ID'], $stock);

                    if ($productQuantity['quantity'] < $quantity) {
                        $_SESSION['mensagem_erro'] = "Quantidade insuficiente para o produto: $code - $importer";
                        header("Location: " . $_SERVER["REQUEST_URI"]);
                        exit();
                    }

                    $products[] = [
                        "product_ID" => $product['ID'],
                        "quantity" => $quantity,
                        "stock" => $productQuantity['stock_ID'],
                        "client" => $client,
                        "observation" => $observation
                    ];
                }

                $this->lancamentoModel->criarSaidaEmMassa($products);
                $_SESSION['sucesso'] = true;
            } catch (Exception $e) {
                $_SESSION['mensagem_erro'] = $e->getMessage();
                header("Location: " . $_SERVER["REQUEST_URI"]);
                exit();
            }
        }

        header("Location: /lancamento/saida");
    }

    public function importarEntrada()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                $this->verifyWritePermission();

                $file = $_FILES['file'];
                $rows = PhpExcel::read(($file['tmp_name']));

                if (count($rows) == 0) {
                    $_SESSION['mensagem_erro'] = "Nenhum dado encontrado na planilha!";
                    header("Location: " . $_SERVER["REQUEST_URI"]);
                    exit();
                }

                echo PhpExcel::formatAsTable($rows);

                array_shift($rows); // Remove o cabeçalho

                $products = [];

                foreach ($rows as $row) {
                    // $ean = $row[0];
                    $code = $row[1];
                    $quantity = $row[2];
                    $lote_container = $row[3];
                    $importer = $this->validateImporter($row[4]);
                    $observation = $row[5];

                    $product = $this->importerModel->getProductByCodeAndImporter($code, $importer);

                    if (!$product) {
                        $_SESSION['mensagem_erro'] = "Produto não encontrado: $code - $importer";
                        header("Location: " . $_SERVER["REQUEST_URI"]);
                        exit();
                    }

                    $products[] = [
                        "product_ID" => $product['ID'],
                        "quantity" => $quantity,
                        "lote_container" => $lote_container,
                        "observation" => $observation
                    ];
                }

                $this->lancamentoModel->criarEntradaEmMassa($products);
                $_SESSION['sucesso'] = true;
            } catch (Exception $e) {
                $_SESSION['mensagem_erro'] = $e->getMessage();
                header("Location: " . $_SERVER["REQUEST_URI"]);
                exit();
            }
        }

        header("Location: /lancamento/entrada");
    }

    public function importarDevolucao()
    {
        // Colunas da planilha de devolucao: ean, codigo, importadora, quantidade, origem/cliente, estoque, observacao
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                $this->verifyWritePermission();

                $file = $_FILES['file'];
                $rows = PhpExcel::read(($file['tmp_name']));

                if (count($rows) == 0) {
                    $_SESSION['mensagem_erro'] = "Nenhum dado encontrado na planilha!";
                    header("Location: " . $_SERVER["REQUEST_URI"]);
                    exit();
                }

                echo PhpExcel::formatAsTable($rows);

                array_shift($rows); // Remove o cabeçalho

                $products = [];

                foreach ($rows as $row) {
                    // $ean = $row[0];
                    $code = $row[1];
                    $importer = $this->validateImporter($row[2]);
                    $quantity = $row[3];
                    $client = $row[4];
                    $stock = $this->validateStock($row[5]);
                    $observation = $row[6];

                    $product = $this->importerModel->getProductByCodeAndImporter($code, $importer);

                    if (!$product) {
                        $_SESSION['mensagem_erro'] = "Produto não encontrado: $code - $importer";
                        header("Location: " . $_SERVER["REQUEST_URI"]);
                        exit();
                    }

                    $productQuantity = $this->importerModel->getProductQuantity($product['ID'], $stock);

                    $products[] = [
                        "product_ID" => $product['ID'],
                        "quantity" => $quantity,
                        "stock" => $productQuantity['stock_ID'],
                        "client" => $client,
                        "observation" => $observation
                    ];
                }

                $this->lancamentoModel->criarDevolucaoEmMassa($products);
                $_SESSION['sucesso'] = true;
            } catch (Exception $e) {
                $_SESSION['mensagem_erro'] = $e->getMessage();
                header("Location: " . $_SERVER["REQUEST_URI"]);
                exit();
            }
        }

        header("Location: /lancamento/devolucao");
    }

    public function importarTransferencias()
    {
        // Colunas da planilha de transferencias: ean, Código, Importadora, Quantidade, Estoque Origem, Estoque Destino, Localização, Observação
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                $this->verifyWritePermission();

                $file = $_FILES['file'];
                $rows = PhpExcel::read(($file['tmp_name']));

                if (count($rows) == 0) {
                    $_SESSION['mensagem_erro'] = "Nenhum dado encontrado na planilha!";
                    header("Location: " . $_SERVER["REQUEST_URI"]);
                    exit();
                }

                echo PhpExcel::formatAsTable($rows);

                array_shift($rows); // Remove o cabeçalho

                $products = [];

                foreach ($rows as $row) {
                    // $ean = $row[0];
                    $code = $row[1];
                    $importer = $this->validateImporter($row[2]);
                    $quantity = $row[3];
                    $stock_origin = $this->validateStock($row[4]);
                    $stock_destination = $this->validateStock($row[5]);
                    $location = $row[6];
                    $observation = $row[7];

                    $product = $this->importerModel->getProductByCodeAndImporter($code, $importer);

                    if (!$product) {
                        $_SESSION['mensagem_erro'] = "Produto não encontrado: $code - $importer";
                        header("Location: " . $_SERVER["REQUEST_URI"]);
                        exit();
                    }

                    $stockOrigin = $this->importerModel->getStockByName($stock_origin);
                    $stockDestination = $this->importerModel->getStockByName($stock_destination);

                    $products[] = [
                        "product_ID" => $product['ID'],
                        "quantity" => $quantity,
                        "from_stock" => $stockOrigin['ID'],
                        "to_stock" => $stockDestination['ID'],
                        "location" => $location,
                        "observation" => $observation
                    ];
                }

                $this->lancamentoModel->criarTransferenciaEmMassa($products);
                $_SESSION['sucesso'] = true;
            } catch (Exception $e) {
                $_SESSION['mensagem_erro'] = $e->getMessage();
                header("Location: " . $_SERVER["REQUEST_URI"]);
                exit();
            }
        }

        header("Location: /lancamento/transferencias");
    }

    public function importarReservas()
    {
        // Colunas da planilha de reservas: ean, Código, Importadora, Quantidade, Origem, Destino/Cliente, Dia de Retirada, Observação

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                $this->verifyWritePermission();

                $file = $_FILES['file'];
                $rows = PhpExcel::read(($file['tmp_name']));

                if (count($rows) == 0) {
                    $_SESSION['mensagem_erro'] = "Nenhum dado encontrado na planilha!";
                    header("Location: " . $_SERVER["REQUEST_URI"]);
                    exit();
                }

                echo PhpExcel::formatAsTable($rows);

                array_shift($rows); // Remove o cabeçalho

                $products = [];

                foreach ($rows as $row) {
                    // $ean = $row[0];
                    $code = $row[1];
                    $importer = $this->validateImporter($row[2]);
                    $quantity = $row[3];
                    $stock = $this->validateStock($row[4]);
                    $client = $row[5];
                    $rescue_date = $this->validateDate($row[6]);
                    $observation = $row[7];

                    $product = $this->importerModel->getProductByCodeAndImporter($code, $importer);

                    if (!$product) {
                        $_SESSION['mensagem_erro'] = "Produto não encontrado: $code - $importer";
                        header("Location: " . $_SERVER["REQUEST_URI"]);
                        exit();
                    }

                    $productQuantity = $this->importerModel->getProductQuantity($product['ID'], $stock);

                    if ($productQuantity['quantity'] < $quantity) {
                        $_SESSION['mensagem_erro'] = "Quantidade insuficiente para o produto: $code - $importer";
                        header("Location: " . $_SERVER["REQUEST_URI"]);
                        exit();
                    }

                    $products[] = [
                        "product_ID" => $product['ID'],
                        "stock" => $productQuantity['stock_ID'],
                        "quantity" => $quantity,
                        "client" => $client,
                        "rescue_date" => $rescue_date,
                        "observation" => $observation
                    ];
                }

                $this->lancamentoModel->criarReservaEmMassa($products);
                $_SESSION['sucesso'] = true;
            } catch (Exception $e) {
                $_SESSION['mensagem_erro'] = $e->getMessage();
                header("Location: " . $_SERVER["REQUEST_URI"]);
                exit();
            }
        }

        header("Location: /lancamento/reserva");
    }

    private function validateStock($stock)
    {
        if (!$stock) {
            $_SESSION['mensagem_erro'] = "Estoque não encontrado!";
            header("Location: " . $_SERVER["REQUEST_URI"]);
            exit();
        }

        $stocks_galpao_str = ["galpão", "galpao"];
        $stocks_loja_str = ["loja"];

        $stock_name = strtolower($stock);
        if (in_array($stock_name, $stocks_galpao_str)) {
            return "Galpão";
        } else if (in_array($stock_name, $stocks_loja_str)) {
            return "Loja";
        } else {
            $_SESSION['mensagem_erro'] = "Estoque inválido!";
            header("Location: " . $_SERVER["REQUEST_URI"]);
            exit();
        }
    }

    private function validateImporter($importer_name)
    {
        $importer_attus_str = ["attus"];
        $importer_attus_bloom_str = ["attus bloom", "attusbloom", "attus_bloom"];
        $importer_alpha_ynfinity_str = ["alpha ynfinity", "alpha_ynfinity", "alpha ynfinity"];

        $importer_name = strtolower($importer_name);

        if (in_array($importer_name, $importer_attus_str)) {
            return "ATTUS";
        } else if (in_array($importer_name, $importer_attus_bloom_str)) {
            return "ATTUS_BLOOM";
        } else if (in_array($importer_name, $importer_alpha_ynfinity_str)) {
            return "ALPHA_YNFINITY";
        } else {
            $_SESSION['mensagem_erro'] = "Importadora inválida!";
            header("Location: " . $_SERVER["REQUEST_URI"]);
            exit();
        }
    }

    private function validateDate($date_string)
    {
        // A data sempre vem no formato dd/mm/aaaa ou dd-mm-aaaa
        // Precisa retornar a data compativel com o banco de dados
        // utilize regex para validar a data

        $date = DateTime::createFromFormat('d/m/Y', $date_string);

        if (!$date) {
            $date = DateTime::createFromFormat('d-m-Y', $date_string);
        }

        if (!$date) {
            $_SESSION['mensagem_erro'] = "Data inválida! ". $date_string;
            header("Location: " . $_SERVER["REQUEST_URI"]);
            exit();
        }

        return $date->format('Y-m-d');
    }
}
