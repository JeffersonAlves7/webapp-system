<?php
require_once "Controllers/_Controller.php";
require_once "Models/Relatorios.php";

class RelatoriosController extends _Controller
{
    private $relatorios;

    public function __construct()
    {
        parent::__construct();
        $this->relatorios = new Relatorios();
    }

    public function index()
    {
        $this->view("Relatorios/index");
    }

    public function saidasDiarias()
    {
        $dataSaida = null;
        $where = "1";

        if (isset($_GET["dataSaida"]) && !empty($_GET["dataSaida"])) {
            $dataSaida = $_GET["dataSaida"];
        }

        if (isset($_GET["cliente"]) && !empty($_GET["cliente"])) {
            $cliente = $_GET["cliente"];
            $where = "t.client_name LIKE '%$cliente%'";
        }

        $dados = $this->relatorios->saidasDiarias($where, $dataSaida);

        $this->view("Relatorios/saidasDiarias", ["dados" => $dados]);
    }
}
