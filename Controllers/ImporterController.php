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
        parent::__construct();

        $this->importerModel = new Importer();
        $this->lancamentoModel = new Lancamento();
    }

    private function verifyWritePermission()
    {
        if (
            !$this->userPermissionManager->controller("Lancamento")->canWrite()
        ) {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para realizar esta ação!";
            header("Location: /lancamento");
            exit;
        }
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

                    $product = $this->importerModel->getProductByCodeAndImporter($code, $importer, $stock);

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
                    $importer = $this->validateImporter($row[2]);
                    $quantity = $row[3];
                    $stock = $this->validateStock($row[4]);
                    $observation = $row[5];

                    $product = $this->importerModel->getProductByCodeAndImporter($code, $importer, $stock);

                    if (!$product) {
                        $_SESSION['mensagem_erro'] = "Produto não encontrado: $code - $importer";
                        header("Location: " . $_SERVER["REQUEST_URI"]);
                        exit();
                    }

                    $products[] = [
                        "product_ID" => $product['ID'],
                        "quantity" => $quantity,
                        "stock" => $stock,
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
}
