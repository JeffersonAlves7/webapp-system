<?php
require_once "Models/Estoque.php";
require_once "Models/Reserva.php";
require_once "Controllers/_Controller.php";

class ReservasController extends _Controller
{
    private $estoquesModel = null;
    private $reservaModel = null;

    public function __construct()
    {
        parent::__construct();
        $this->estoquesModel = new Estoque();
        $this->reservaModel = new Reserva();
    }

    public function index()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
        }

        $page = 1;
        $where = "`confirmed` = 0 ";

        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        }
        if (isset($_GET["code"]) && $_GET["code"] != "") {
            $where .= "AND p.`code` LIKE \"%" . htmlspecialchars($_GET["code"]) . "%\"";
        }
        if (isset($_GET["client"]) && $_GET["client"] != "") {
            $where .= "AND `client_name` LIKE \"%" . htmlspecialchars($_GET["client"]) . "%\"";
        }

        $stocks = $this->estoquesModel->getAll();
        $reserves = $this->reservaModel->getAll($page, 50, $where);

        include_once "Views/Reservas.php";
    }

    public function cancelar()
    {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            exit(0);
        }

        $ID = $_POST["ID"];

        echo "Reserve cancelada com sucesso!";
        $this->reservaModel->delete($ID);
        header("Refresh: 1; url=/reservas");
    }

    public function confirmar()
    {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            exit(0);
        }

        $ID = $_POST["ID"];

        echo "Reserve confirmada com sucesso!";
        $this->reservaModel->confirm($ID);
        header("Refresh: 1; url=/reservas");
    }
}
