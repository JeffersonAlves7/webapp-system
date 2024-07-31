<?php

require_once "Models/Estoque.php";
require_once "Controllers/_Controller.php";

class EstoquesController extends _Controller
{
    private $estoquesModel;

    public function __construct()
    {
        parent::__construct("Estoques");
        $this->verifyReadPermission();
        $this->estoquesModel = new Estoque();
    }

    public function index()
    {
        // if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["name"])) {
        //     $name = $_POST["name"];
        //     $this->estoquesModel->create($name);
        //     header("location: /estoques");
        // }
        $sucesso = isset($_SESSION["sucesso"]) && $_SESSION["sucesso"];
        unset($_SESSION["sucesso"]);
        $mensagem_erro = isset($_SESSION["mensagem_erro"]) ? $_SESSION["mensagem_erro"] : "";
        unset($_SESSION["mensagem_erro"]);

        $page = 1;

        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        }

        $estoque_ID = null;
        if (isset($_GET["estoque"]) && $_GET["estoque"] != "") {
            $estoque_ID = $_GET["estoque"];
        }

        $alert = 0.2;
        if (isset($_COOKIE["alerta"])) {
            $alert = $_COOKIE["alerta"] / 100;
        }

        $where = "1";
        if (isset($_COOKIE["codigo"]) && $_COOKIE["codigo"] != "") {
            $where .= " AND p.code LIKE '%" . $_COOKIE["codigo"] . "%'";
        }
        if (isset($_GET["importadora"]) && $_GET["importadora"] != "") {
            $where .= " AND p.importer = '" . $_GET["importadora"] . "'";
        }

        $orderBy = "p.created_at";
        $orderType = "DESC";
        if (isset($_GET["orderBy"]) && !empty($_GET["orderBy"])) {
            $orderBy = $_GET["orderBy"];
            if ($orderBy == "codigo") {
                $orderBy = "p.code";
            }
        }
        if (isset($_GET["orderType"]) && !empty($_GET["orderType"]) && ($_GET["orderType"] == "asc" || $_GET["orderType"] == "desc")) {
            $orderType = $_GET["orderType"];
        }

        $stocks = $this->estoquesModel->getAll();
        $productsData = $this->estoquesModel->getProductsByStock($estoque_ID, $page, limit: 50, alert: $alert, where: $where, order: $orderBy . " " . $orderType);

        return $this->view(
            "Estoques",
            [
                "estoques" => $stocks,
                "produtos" => $productsData["products"],
                "page" => $page,
                "pageCount" => $productsData["pageCount"],
                "sucesso" => $sucesso,
                "mensagem_erro" => $mensagem_erro,
                "totalProdutos"  => $productsData["total_count"],
                "totalCaixas" => $productsData["saldo_total"],
            ]
        );
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
