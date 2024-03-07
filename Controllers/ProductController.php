<?php
require_once "Models/Product.php";

class ProductController
{
    public $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function index($id)
    {
        $product = $this->productModel->byId($id);

        require_once "Views/Product.php";
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
}
