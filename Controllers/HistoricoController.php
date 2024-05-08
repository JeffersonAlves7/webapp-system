<?php
require_once "Models/Historico.php";
require_once "Controllers/_Controller.php";
require_once "Utils/PhpPDF.php";

class HistoricoController extends _Controller
{
    public $historicoModel;

    public function __construct()
    {
        parent::__construct();
        $this->historicoModel = new Historico();
    }

    public function index()
    {
        $this->view("Historico/Index");
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
            $pdf = PhpPDF::arrayToPdf(
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
                }, $transferencias)
            );

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="transferencias.pdf"');
            header('Cache-Control: max-age=0');

            $pdf->save('php://output');
            header('Location: /?');
            return;
        }

        $transferencias = $this->historicoModel->getTransferencias($where);
        $this->view("Historico/Transferencias", ["transferencias" => $transferencias]);
    }
}
