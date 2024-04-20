<?php
require_once "Models/Container.php";
require_once "Controllers/_Controller.php";

class EmbarquesController extends _Controller
{
    private $containerModel;

    public function __construct()
    {
        parent::__construct();
        $this->containerModel = new Container();
    }

    public function index()
    {
        $page = 1;
        if (isset($_GET["page"])) {
            $page = (int) $_GET["page"];
        }

        $where = "1 = 1 ";
        if (isset($_GET["search"])) {
            $search = htmlspecialchars($_GET["search"]);
            $where .= " AND `name` LIKE '%$search%'";
        }

        if (
            isset($_GET["start_date"]) && !empty($_GET["start_date"])
            && isset($_GET["end_date"]) && !empty($_GET["end_date"])
        ) {
            $start_date = htmlspecialchars($_GET["start_date"]);
            $end_date = htmlspecialchars($_GET["end_date"]);
            $where .= " AND `created_at` BETWEEN '$start_date' AND '$end_date'";
        } else if (isset($_GET["start_date"]) && !empty($_GET["start_date"])) {
            $start_date = htmlspecialchars($_GET["start_date"]);
            $where .= " AND `created_at` BETWEEN '$start_date 00:00:00' AND '$start_date 23:59:59'";
        }

        $containers = $this->containerModel->getAll($page, where: $where);

        include_once "Views/Containers/index.php";
    }

    public function produtos($container_ID)
    {
        $page = 1;

        if (isset($_GET["page"])) {
            $page = (int) $_GET["page"];
        }

        $where = "1 = 1 ";

        // $result_container = $this->containerModel->byId($container_ID);
        $productsData = $this->containerModel->produtosById($container_ID, $page, where: $where);
        $products = $productsData["products"];
        $pageCount = $productsData["pageCount"];
        include_once "Views/Containers/Produtos.php";
    }

    public function conferir($container_ID)
    {
        $page = 1;

        if (isset($_GET["page"])) {
            $page = (int) $_GET["page"];
        }

        $where = "1 = 1 ";
        $where .= " AND in_stock = 0";

        $products_in_container = $this->containerModel->produtosById($container_ID, $page, where: $where);

        include_once "Views/Containers/Conferir.php";
    }

    public function deletarProduto()
    {
        $redirect = "/";

        if (isset($_GET["redirect"])) {
            $redirect = htmlspecialchars($_GET["redirect"]);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $product_ID = $_POST["product_ID"];
            $container_ID = $_POST["container_ID"];

            $this->containerModel->deleteProduct($container_ID, $product_ID);
            echo "Sucesso ao deletar!";
        } else {
            echo "Falha ao deletar!";
        }

        header("Refresh: 1; URL = $redirect");
    }

    public function deletar($id)
    {
        $this->containerModel->delete($id);
        echo "Container apagado com sucesso!";
        header("Refresh: 1; URL = /embarques");
    }
}
