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
        $products_in_container = $this->containerModel->produtosById($container_ID, $page, where: $where);
        include_once "Views/Containers/Produtos.php";
    }

    public function conferir($container_ID)
    {
        $page = 1;

        if (isset($_GET["page"])) {
            $page = (int) $_GET["page"];
        }

        $where = "1 = 1 ";

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
        header("Refresh: 1; URL = /containers");
    }
}
