<?php
require_once "Models/Estoque.php";
require_once "Models/Reserva.php";

class ReservasController
{
    private $estoquesModel = null;
    private $reservaModel = null;

    public function __construct()
    {
        $this->estoquesModel = new Estoque();
        $this->reservaModel = new Reserva();
    }

    public function index()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
        }

        $page = 1;
        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        }

        $stocks = $this->estoquesModel->getAll();
        $reserves = $this->reservaModel->getAll($page, 50, "`confirmed` = 0");

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
        header("Refresh: 2; url=/reservas");
    }

    public function confirmar()
    {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            exit(0);
        }

        $ID = $_POST["ID"];

        echo "Reserve confirmada com sucesso!";
        $this->reservaModel->confirm($ID);
        header("Refresh: 2; url=/reservas");
    }
}
