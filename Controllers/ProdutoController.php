<?php
require_once "Models/Product.php";
require_once "Models/Transacao.php";

class ProdutoController
{
    public $productModel;
    public $transacaoModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->transacaoModel = new Transacao();
    }

    public function index($id)
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["_method"])) {
            $method = $_POST["_method"];

            switch ($method) {
                case "delete":
                    $this->transacaoModel->delete($_POST["ID"]);
            }
        }

        $result = $this->productModel->byId($id);

        // Se não encontrar deve voltar para a página iniial
        if ($result->num_rows == 0) {
            header("Location: /");
        }

        $produto = $result->fetch_assoc();
        $quantidade_em_estoque = $this->productModel->quantityInStockById($id);
        $transacoes = $this->transacaoModel->getAllByProductId($id);
        require_once "Views/Produto.php";
    }

    public function list()
    {
        if (!isset($_SESSION["username"])) {
            header("location: auth/login");
            exit(0);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["_method"])) {
            if ($_POST["_method"] == "put") {
                $id = $_POST["id"];

                $code = $_POST["code"];
                $ean = $_POST["ean"];
                $importer = $_POST["importer"];
                $description = $_POST["description"];
                $chinese_description = $_POST["chinese_description"];

                $this->productModel->update(
                    $id,
                    $code,
                    $ean,
                    $importer,
                    $description,
                    $chinese_description
                );
            } else if ($_POST["_method"] == "post") {
                $code = $_POST["code"];
                $ean = $_POST["ean"];
                $importer = $_POST["importer"];
                $description = $_POST["description"];
                $chinese_description = $_POST["chinese_description"];

                $this->productModel->create($code, $ean, $importer, $description, $chinese_description);
            }
        }

        $page = 1;
        if (isset($_GET["page"])) {
            $page = (int) $_GET["page"];
        }

        $where = "1 = 1 ";
        if (isset($_GET["importer"]) && $_GET["importer"] != "") {
            $where .= "AND importer LIKE \"%" . htmlspecialchars($_GET["importer"]) . "%\"";
        }
        if (isset($_GET["code"]) && $_GET["code"] != "") {
            $where .= "AND `code` LIKE \"%" . htmlspecialchars($_GET["code"]) . "%\"";
        }
        if (isset($_GET["ean"]) && $_GET["ean"] != "") {
            $where .= "AND ean LIKE \"%" . htmlspecialchars($_GET["ean"]) . "%\"";
        }
        if (isset($_GET["description"]) && $_GET["description"] != "") {
            $where .= "AND `description` LIKE \"%" . htmlspecialchars($_GET["description"]) . "%\"";
        }
        if (isset($_GET["chinese_description"]) && $_GET["chinese_description"] != "") {
            $where .= "AND chinese_description LIKE \"%" . htmlspecialchars($_GET["chinese_description"]) . "%\"";
        }

        $products = $this->productModel->getAll($page, where: $where);

        require_once "Views/Home.php";
    }

    public function delete($id)
    {
        if (!isset($_SESSION["username"])) {
            header("location: auth/login");
            exit(0);
        }

        $this->productModel->delete($id);

        header("location: /");
        exit(0);
    }

    public function findAllByCodeOrEan()
    {
        header("Content-type: application/json");

        if (!isset($_GET["search"]) || empty($_GET["search"])) {
            echo json_encode(array());
            exit(0);
        }

        $search = $_GET["search"];
        $products = $this->productModel->findAllByCodeOrEan(htmlspecialchars($search));

        $products_arr = array();
        if ($products->num_rows > 0) {
            while ($product = $products->fetch_assoc()) {
                array_push($products_arr, $product);
            }
        }

        echo json_encode(array("products" => $products_arr));
    }
}
