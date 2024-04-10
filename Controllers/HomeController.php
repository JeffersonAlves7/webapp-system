<?php

require_once "Models/Estoque.php";
require_once "Controllers/_Controller.php";

class HomeController extends _Controller
{
    private $estoquesModel;

    public function __construct()
    {
        parent::__construct();
        $this->estoquesModel = new Estoque();
    }

    public function index()
    {
        // if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["name"])) {
        //     $name = $_POST["name"];
        //     $this->estoquesModel->create($name);
        //     header("location: /estoques");
        // }
        
        $page = 1;

        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        }

        $estoque_ID = null;
        if(isset($_GET["estoque"])) {
            $estoque_ID = $_GET["estoque"];
        }

        $stocks = $this->estoquesModel->getAll();
        $produtos = $this->estoquesModel->getProductsByStock($estoque_ID, $page);
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
