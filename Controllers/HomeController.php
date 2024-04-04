<?php

require_once "Models/Estoque.php";

class HomeController
{
    private $estoquesModel;

    public function __construct()
    {
        $this->estoquesModel = new Estoque();
    }

    public function index()
    {
        // if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["name"])) {
        //     $name = $_POST["name"];
        //     $this->estoquesModel->create($name);
        //     header("location: /estoques");
        // }

        $stocks = $this->estoquesModel->getAll();
        include_once "Views/Estoques.php";
    }

    public function getAll()
    {
        header("Content-type: application/json");

        $stocks = $this->estoquesModel->getAll();
        $stocks_arr = array();
        if ($stocks->num_rows > 0) {
            while ($stock = $stocks->fetch_assoc()) {
                array_push($stocks_arr, $stock);
            }
        }

        echo json_encode(array("stocks" => $stocks_arr));
        exit(0);
    }
}
