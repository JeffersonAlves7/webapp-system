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
        parent::__construct("Reservas");

        $this->verifyReadPermission();
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
        $dados = $this->reservaModel->getAll($page, 50, $where);

        $this->view("Reservas", [
            "stocks" => $stocks,
            "reserves" => $dados["reserves"],
            "pageCount" => $dados["pageCount"]
        ]);
    }

    public function cancelar()
    {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            exit(0);
        }

        $this->verifyDeletePermission();

        $ID = $_POST["ID"];

        $this->reservaModel->delete($ID);
        header("Location: /reservas");
    }

    public function confirmar()
    {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            exit(0);
        }

        $this->verifyWritePermission();

        $ID = $_POST["ID"];

        $this->reservaModel->confirm($ID);
        header("Location: /reservas");
    }
}
