<?php
require_once "Models/Lancamento.php";
require_once "Models/Estoque.php";
require_once "Controllers/_Controller.php";
require_once "Utils/PhpPDF.php";

class LancamentoController extends _Controller
{
    public $lancamentoModel;
    public $estoqueModel;

    public function __construct()
    {
        parent::__construct();
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

    public function reserva()
    {
        // Verifica se há uma mensagem de erro na sessão
        $mensagem_erro = isset($_SESSION['mensagem_erro']) ? $_SESSION['mensagem_erro'] : "";
        unset($_SESSION['mensagem_erro']); // Remove a mensagem de erro da sessão

        // Verifica se há uma variável de sucesso na sessão
        $sucesso = isset($_SESSION['sucesso']) ? $_SESSION['sucesso'] : false;
        unset($_SESSION['sucesso']); // Remove a variável de sucesso da sessão

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $product_ID = $_POST["produto_id"];
            $quantity = $_POST["quantidade"];
            $stock_ID = $_POST["estoque_origem"];
            $client_name = $_POST["cliente"];
            $observation = $_POST["observacao"];
            $dataRetirada = new DateTime($_POST['dataRetirada']);
            $dataRetiradaFormatada = $dataRetirada->format('Y-m-d H:i:s');

            try {
                $this->lancamentoModel->criarReserva(
                    $product_ID,
                    $stock_ID,
                    $quantity,
                    $client_name,
                    $dataRetiradaFormatada,
                    $observation
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
        require_once "Views/Lancamento/Reserva.php";
    }

    public function exportarTransferencias()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $transferenciasIds = json_decode($_POST["transference-ids"]);
            $idsString = implode(",", $transferenciasIds); 

            $transferencias = $this->lancamentoModel->getTransferenciasPendentes(
                "`transferences`.`ID` IN ($idsString)"
            );
            $data = array();

            foreach ($transferencias as $transferencia) {
                $data[] = array(
                    $transferencia['code'],
                    $transferencia['importer'],
                    $transferencia['description'],
                    $transferencia['quantity'],
                    $transferencia['from_stock_name'],
                    $transferencia['to_stock_name'],
                    $transferencia['observation']
                );
            }

            $pdf = PhpPDF::arrayToPdf(
                array('Produto', 'Importadora', 'Descrição', 'Quantidade', 'Origem', 'Destino', 'Observação'),
                $data
            );

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="relatorio.pdf"');
            header('Cache-Control: max-age=0');

            $pdf->save('php://output');
        }
    }

    public function conferirTransferencias()
    {
        // Verifica se há uma mensagem de erro na sessão
        $mensagem_erro = isset($_SESSION['mensagem_erro']) ? $_SESSION['mensagem_erro'] : "";
        unset($_SESSION['mensagem_erro']); // Remove a mensagem de erro da sessão

        // Verifica se há uma variável de sucesso na sessão
        $sucesso = isset($_SESSION['sucesso']) ? $_SESSION['sucesso'] : false;
        unset($_SESSION['sucesso']); // Remove a variável de sucesso da sessão

        $transferencias = $this->lancamentoModel->getTransferenciasPendentes();
        $this->view('Lancamento/ConferirTransferencias', [
            'transferencias' => $transferencias,
            'mensagem_erro' => $mensagem_erro,
            'sucesso' => $sucesso
        ]);
    }

    public function confirmarTransferencias()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                $transferencias = json_decode($_POST["transferences"]);
                $this->lancamentoModel->confirmarTransferencias($transferencias);
                $_SESSION['sucesso'] = true;
            } catch (Exception $e) {
                $_SESSION['mensagem_erro'] = $e->getMessage();
            }
        }

        header("Location: /lancamento/conferirTransferencias");
        exit();
    }

    public function cancelarTransferencias()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                $transferencias = json_decode($_POST["transference-ids"]);
                $this->lancamentoModel->cancelarTransferencias($transferencias);
                $_SESSION['sucesso'] = true;
                header("Location: /lancamento/conferirTransferencias");
                exit();
            } catch (Exception $e) {
                $_SESSION['mensagem_erro'] = $e->getMessage();
                header("Location: /lancamento/conferirTransferencias");
                exit();
            }
        }

        header("Location: /lancamento/conferirTransferencias");
    }
}
