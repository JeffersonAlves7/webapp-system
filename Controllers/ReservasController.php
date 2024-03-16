<?php
require_once "Models/Estoque.php";
class ReservasController
{
    private $estoquesModel = null;

    public function __construct()
    {
        $this->estoquesModel = new Estoque();
    }

    public function index()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["name"])) {
            $name = $_POST["name"];
            $this->estoquesModel->create($name);
            header("location: /estoques");
        }

        $stocks = $this->estoquesModel->getAll();

        include_once "Views/Reservas.php";
    }
}
