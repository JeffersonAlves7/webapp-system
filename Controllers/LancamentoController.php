<?php
require_once "Models/Lancamento.php";
require_once "Models/Estoque.php";

class LancamentoController
{
    public $lancamentoModel;
    public $estoqueModel;

    public function __construct()
    {
        $this->lancamentoModel = new Lancamento();
        $this->estoqueModel = new Estoque();
    }

    public function index()
    {
        $this->entrada();
    }

    public function entrada()
    {
        // Verifica se há uma mensagem de erro na sessão
        $mensagem_erro = isset($_SESSION['mensagem_erro']) ? $_SESSION['mensagem_erro'] : "";
        unset($_SESSION['mensagem_erro']); // Remove a mensagem de erro da sessão

        // Verifica se há uma variável de sucesso na sessão
        $sucesso = isset($_SESSION['sucesso']) ? $_SESSION['sucesso'] : false;
        unset($_SESSION['sucesso']); // Remove a variável de sucesso da sessão

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $produto_id = $_POST["produto_id"];
            $quantidade = $_POST["quantidade"];
            $lote_container = $_POST["lote_container"];
            $observacao = $_POST["observacao"];

            try {
                $this->lancamentoModel->criarEntrada(
                    $produto_id,
                    $quantidade,
                    $lote_container,
                    $observacao
                );
                $_SESSION['sucesso'] = true; // Define a variável de sucesso na sessão
                header("Location: " . $_SERVER["REQUEST_URI"]);
                exit();
            } catch (Exception $e) {
                $_SESSION['mensagem_erro'] = $e->getMessage(); // Define a mensagem de erro na sessão
                header("Location: " . $_SERVER["REQUEST_URI"]);
                exit();
            }
        }

        require_once "Views/Lancamento/Entrada.php";
    }


    public function saida()
    {
        // Verifica se há uma mensagem de erro na sessão
        $mensagem_erro = isset($_SESSION['mensagem_erro']) ? $_SESSION['mensagem_erro'] : "";
        unset($_SESSION['mensagem_erro']); // Remove a mensagem de erro da sessão

        // Verifica se há uma variável de sucesso na sessão
        $sucesso = isset($_SESSION['sucesso']) ? $_SESSION['sucesso'] : false;
        unset($_SESSION['sucesso']); // Remove a variável de sucesso da sessão

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $produto_ID = $_POST["produto_id"];
            $quantidade = $_POST["quantidade"];
            $stock_ID = $_POST["estoque_origem"];
            $cliente = $_POST["cliente"];
            $observacao = $_POST["observacao"];

            try {
                $this->lancamentoModel->criarSaida(
                    $produto_ID,
                    $quantidade,
                    $stock_ID,
                    $cliente,
                    $observacao
                );
                $_SESSION['sucesso'] = true; // Define a variável de sucesso na sessão
                header("Location: " . $_SERVER["REQUEST_URI"]);
                exit();
            } catch (Exception $e) {
                $_SESSION['mensagem_erro'] = $e->getMessage(); // Define a mensagem de erro na sessão
                header("Location: " . $_SERVER["REQUEST_URI"]);
                exit();
            }
        }

        $stocks = $this->estoqueModel->getAll();
        require_once "Views/Lancamento/Saida.php";
    }

    public function transferencia()
    {
        // Verifica se há uma mensagem de erro na sessão
        $mensagem_erro = isset($_SESSION['mensagem_erro']) ? $_SESSION['mensagem_erro'] : "";
        unset($_SESSION['mensagem_erro']); // Remove a mensagem de erro da sessão

        // Verifica se há uma variável de sucesso na sessão
        $sucesso = isset($_SESSION['sucesso']) ? $_SESSION['sucesso'] : false;
        unset($_SESSION['sucesso']); // Remove a variável de sucesso da sessão

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $produto_ID = $_POST["produto_id"];
            $quantidade = $_POST["quantidade"];
            $estoque_origem = $_POST["estoque_origem"];
            $estoque_destino = $_POST["estoque_destino"];
            $localizacao = $_POST["localizacao"];
            $observacao = $_POST["observacao"];

            try {
                $this->lancamentoModel->criarTransferencia(
                    $produto_ID,
                    $quantidade,
                    $estoque_origem,
                    $estoque_destino,
                    $localizacao,
                    $observacao
                );
                $_SESSION['sucesso'] = true; // Define a variável de sucesso na sessão
                header("Location: " . $_SERVER["REQUEST_URI"]);
                exit();
            } catch (Exception $e) {
                $_SESSION['mensagem_erro'] = $e->getMessage(); // Define a mensagem de erro na sessão
                header("Location: " . $_SERVER["REQUEST_URI"]);
                exit();
            }
        }

        $stocks = $this->estoqueModel->getAll();
        require_once "Views/Lancamento/Transferencia.php";
    }

    public function devolucao()
    {
        // Verifica se há uma mensagem de erro na sessão
        $mensagem_erro = isset($_SESSION['mensagem_erro']) ? $_SESSION['mensagem_erro'] : "";
        unset($_SESSION['mensagem_erro']); // Remove a mensagem de erro da sessão

        // Verifica se há uma variável de sucesso na sessão
        $sucesso = isset($_SESSION['sucesso']) ? $_SESSION['sucesso'] : false;
        unset($_SESSION['sucesso']); // Remove a variável de sucesso da sessão

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $produto_ID = $_POST["produto_id"];
            $quantidade = $_POST["quantidade"];
            $estoque_destino = $_POST["estoque_destino"];
            $cliente = $_POST["cliente"];
            $observacao = $_POST["observacao"];

            try {
                $this->lancamentoModel->criarDevolucao(
                    $produto_ID,
                    $quantidade,
                    $estoque_destino,
                    $cliente,
                    $observacao
                );
                $_SESSION['sucesso'] = true; // Define a variável de sucesso na sessão
                header("Location: " . $_SERVER["REQUEST_URI"]);
                exit();
            } catch (Exception $e) {
                $_SESSION['mensagem_erro'] = $e->getMessage(); // Define a mensagem de erro na sessão
                header("Location: " . $_SERVER["REQUEST_URI"]);
                exit();
            }
        }

        $stocks = $this->estoqueModel->getAll();
        require_once "Views/Lancamento/Devolucao.php";
    }
}
