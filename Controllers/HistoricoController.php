<?php
require_once "Models/Historico.php";
require_once "Controllers/_Controller.php";
require_once "Utils/PhpExporter.php";

class HistoricoController extends _Controller
{
    public $historicoModel;

    public function __construct()
    {
        parent::__construct("Histórico");

        $this->verifyReadPermission();
        $this->historicoModel = new Historico();
    }

    public function index()
    {
        $this->transferencias();
    }

    public function entradas()
    {
        $this->view("Historico/Entradas");
    }

    public function saidas()
    {
        $this->view("Historico/Saidas");
    }

    public function transferencias()
    {
        $transaction_type_ID = 3;

        $this->view(
            "Historico/Transferencias",
            $this->renderTransactions($transaction_type_ID)
        );
    }

    public function devolucoes()
    {
        $transaction_type_ID = 4;


        $this->view(
            "Historico/Devolucoes",
            $this->renderTransactions($transaction_type_ID)
        );
    }

    private function renderTransactions($transaction_type_ID)
    {
        $where = "1";

        if (isset($_GET["code"]) && !empty($_GET["code"])) {
            $code = $_GET["code"];
            $where .= " AND `products`.`code` = '$code'";
        }

        if (isset($_GET["data-inicio"]) && !empty($_GET["data-inicio"])) {
            $dataInicio = $_GET["data-inicio"];
            $where .= " AND `transactions_history`.`created_at` >= '$dataInicio'";
        }

        if (isset($_GET["data-fim"]) && !empty($_GET["data-fim"])) {
            $dataFim = $_GET["data-fim"];
            $where .= " AND `transactions_history`.`created_at` <= '$dataFim'";
        }

        if (isset($_GET["action"]) && $_GET["action"] == "exportarDevolucoes") {
            $devolucoes = $this->historicoModel->getAll($transaction_type_ID, 1, 1000000, $where);
            PhpExporter::exportToExcel(
                ['Produto', 'Quantidade', 'Estoque', 'Data', 'Observação'],
                array_map(function ($devolucao) {
                    return [
                        $devolucao["code"],
                        $devolucao["quantity"],
                        $devolucao["stock_name"],
                        date("d/m/Y H:i", strtotime($devolucao["created_at"])),
                        $devolucao["observation"],
                    ];
                }, $devolucoes),
                "historicoDevolucoes"
            );

            return;
        }

        $page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? $_GET["page"] : 1;
        $limit = 50;

        $transactions = $this->historicoModel->getAll($transaction_type_ID, $page, $limit, $where);
        return [
            "transactions" => $transactions["transactions"],
            "pageCount" => $transactions["pageCount"]
        ];
    }

    public function reservas()
    {
        $where = "1";

        if (isset($_GET["code"]) && !empty($_GET["code"])) {
            $code = $_GET["code"];
            $where .= " AND `products`.`code` = '$code'";
        }

        if (isset($_GET["data-inicio"]) && !empty($_GET["data-inicio"])) {
            $dataInicio = $_GET["data-inicio"];
            $where .= " AND `reserves`.`created_at` >= '$dataInicio'";
        }

        if (isset($_GET["data-fim"]) && !empty($_GET["data-fim"])) {
            $dataFim = $_GET["data-fim"];
            $where .= " AND `reserves`.`created_at` <= '$dataFim'";
        }

        if (isset($_GET["action"]) && $_GET["action"] == "exportarReservas") {
            $reservas = $this->historicoModel->getReservas(1, 1000000, $where);
            PhpExporter::exportToExcel(
                ['Produto', 'Quantidade', 'Estoque', 'Data', 'Observação'],
                array_map(function ($reserva) {
                    return [
                        $reserva["code"],
                        $reserva["quantity"],
                        $reserva["stock_name"],
                        date("d/m/Y H:i", strtotime($reserva["created_at"])),
                        $reserva["observation"],
                    ];
                }, $reservas["reservations"]),
                "historicoReservas"
            );

            return;
        }

        $page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? $_GET["page"] : 1;
        $limit = 50;

        $reservas = $this->historicoModel->getReservas($page, $limit, $where);

        $this->view("Historico/Reservas", ["reservas" => $reservas["reservations"], "pageCount" => $reservas["pageCount"]]);
    }
}
