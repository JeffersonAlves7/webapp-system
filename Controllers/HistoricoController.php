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
        $where = "1";

        if (isset($_GET["code"]) && !empty($_GET["code"])) {
            $code = $_GET["code"];
            $where .= " AND `products`.`code` = '$code'";
        }

        if (isset($_GET["data-inicio"]) && !empty($_GET["data-inicio"])) {
            $dataInicio = $_GET["data-inicio"];
            $where .= " AND `transferences`.`created_at` >= '$dataInicio'";
        }

        if (isset($_GET["data-fim"]) && !empty($_GET["data-fim"])) {
            $dataFim = $_GET["data-fim"];
            $where .= " AND `transferences`.`created_at` <= '$dataFim'";
        }

        if (isset($_GET["action"]) && $_GET["action"] == "exportarTransferencias") {
            $transferencias = $this->historicoModel->getTransferencias($where);
            $pdf = PhpExporter::exportToExcel(
                ['Produto', 'Quantidade', 'Origem', 'Destino', 'Data', 'Observação'],
                array_map(function ($transferencia) {
                    return [
                        $transferencia["code"],
                        $transferencia["quantity"],
                        $transferencia["from_stock_name"],
                        $transferencia["to_stock_name"],
                        date("d/m/Y H:i", strtotime($transferencia["created_at"])),
                        $transferencia["observation"],
                    ];
                }, $transferencias),
                "historicoTransferencias"
            );

            return;
        }

        $page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? $_GET["page"] : 1;
        $limit = 50;

        $transferencias = $this->historicoModel->getTransferencias($page, $limit, $where);
        $this->view("Historico/Transferencias", [
            "transferencias" => $transferencias["transferences"],
            "pageCount" => $transferencias["pageCount"]
        ]);
    }

    public function devolucoes()
    {
        $this->view("Historico/Devolucoes");
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
