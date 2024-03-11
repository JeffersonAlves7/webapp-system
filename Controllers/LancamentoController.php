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
        $mensagem_erro = ""; // Inicializa a variável de mensagem de erro

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
                $sucesso = true;
            } catch (Exception $e) {
                // Captura a exceção e define a mensagem de erro
                $sucesso = false;
                $mensagem_erro = $e->getMessage();
            }
        }

        require_once "Views/Lancamento/Entrada.php";
    }

    public function saida()
    {
        $mensagem_erro = ""; // Inicializa a variável de mensagem de erro

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
                $sucesso = true;
            } catch (Exception $e) {
                // Captura a exceção e define a mensagem de erro
                $sucesso = false;
                $mensagem_erro = $e->getMessage();
            }
        }

        $stocks = $this->estoqueModel->getAll();
        require_once "Views/Lancamento/Saida.php";
    }
}
