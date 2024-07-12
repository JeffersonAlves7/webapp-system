<?php
require_once "Controllers/_Controller.php";
require_once "Models/Estoque.php";
require_once "Models/Product.php";
require_once "Models/Transacao.php";

class ProdutosController extends _Controller
{
    public $productModel;
    public $estoquesModel;
    public $transacaoModel;

    public function __construct()
    {
        parent::__construct();

        if (
            !$this->userPermissionManager->controller("Produtos")->canRead()
        ) {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para acessar os Produtos!";
            header("Location: /");
            exit;
        }

        $this->productModel = new Product();
        $this->transacaoModel = new Transacao();
        $this->estoquesModel = new Estoque();
    }

    private function verifyWritePermission()
    {
        if (
            !$this->userPermissionManager->controller("Produtos")->canWrite()
        ) {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para realizar esta ação!";
            header("Location: /produtos");
            exit;
        }
    }

    private function verifyDeletePermission()
    {
        if (
            !$this->userPermissionManager->controller("Produtos")->canDelete()
        ) {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para realizar esta ação!";
            header("Location: /produtos");
            exit;
        }
    }


    public function index()
    {
        $mensagem_erro = isset($_SESSION['mensagem_erro']) ? $_SESSION['mensagem_erro'] : "";
        unset($_SESSION['mensagem_erro']);

        $sucesso = isset($_SESSION['sucesso']) ? $_SESSION['sucesso'] : false;
        unset($_SESSION['sucesso']);

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["_method"])) {
            $this->verifyWritePermission();

            $redirect_to = isset($_SESSION['redirect_to']) ? $_SESSION['redirect_to'] : "/produtos";

            if ($_POST["_method"] == "put") {
                try {
                    if (!isset($_POST["id"])) {
                        throw new Exception("ID não informado");
                    }
                    if (!isset($_POST["code"])) {
                        throw new Exception("Código não informado");
                    }
                    if (!isset($_POST["ean"])) {
                        throw new Exception("EAN não informado");
                    }
                    if (!isset($_POST["importer"])) {
                        throw new Exception("Importador não informado");
                    }

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

                    $_SESSION['sucesso'] = true;
                    header("location: " . $redirect_to);
                } catch (Exception $e) {
                    $_SESSION['mensagem_erro'] = $e->getMessage();
                    header("location: " . $redirect_to);
                    exit(0);
                }
            } else if ($_POST["_method"] == "post") {
                try {
                    if (!isset($_POST["code"])) {
                        throw new Exception("Código não informado");
                    }
                    if (!isset($_POST["ean"])) {
                        throw new Exception("EAN não informado");
                    }
                    if (!isset($_POST["importer"])) {
                        throw new Exception("Importador não informado");
                    }

                    $code = $_POST["code"];
                    $ean = $_POST["ean"];
                    $importer = $_POST["importer"];
                    $description = $_POST["description"];
                    $chinese_description = $_POST["chinese_description"];

                    $this->productModel->create($code, $ean, $importer, $description, $chinese_description);
                    $_SESSION['sucesso'] = true;
                    header("location: " . $redirect_to);
                } catch (Exception $e) {
                    $_SESSION['mensagem_erro'] = $e->getMessage();
                    header("location: " . $redirect_to);
                    exit(0);
                }
            } else if ($_POST["_method"] == "delete") {
                try {
                    if (!isset($_POST["ID"])) {
                        throw new Exception("ID não informado");
                    }

                    $id = $_POST["ID"];
                    $this->productModel->delete($id);
                    $_SESSION['sucesso'] = true;
                    header("location: " . $redirect_to);
                } catch (Exception $e) {
                    $_SESSION['mensagem_erro'] = $e->getMessage();
                    header("location: " . $redirect_to);
                    exit(0);
                }
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

        $productResponse = $this->productModel->getAll($page, where: $where);
        $products = $productResponse["products"];
        $pageCount = $productResponse["pageCount"];

        require_once "Views/Produtos/List.php";
    }

    public function byId($id)
    {
        $redirect_to = isset($_SESSION['redirect_to']) ? $_SESSION['redirect_to'] : "/produtos/byId/$id";

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["_method"])) {
            $method = $_POST["_method"];

            switch ($method) {
                case "delete":
                    $this->verifyDeletePermission();

                    try {
                        $this->transacaoModel->delete($_POST["ID"]);
                        $_SESSION['sucesso'] = true;
                    } catch (Exception $e) {
                        $_SESSION['mensagem_erro'] = $e->getMessage();
                    }

                    break;
            }

            header("location: " . $redirect_to);
            exit(0);
        }

        $result = $this->productModel->byId($id);

        $page = 1;

        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }

        // Se não encontrar deve voltar para a página iniial
        if ($result->num_rows == 0) {
            header("Location: /");
        }

        $stocks = $this->estoquesModel->getAll();
        $produto = $result->fetch_assoc();
        $quantidade_em_estoque = $this->productModel->quantityInStockById($id);

        $where = "1 = 1 ";
        if (isset($_GET["estoque"]) && $_GET["estoque"] != "") {
            $estoque_ID = $_GET["estoque"];
            $where .= "AND (from_stock_ID = $estoque_ID OR to_stock_ID = $estoque_ID)";
        }

        $transacoesData = $this->transacaoModel->getAllByProductId($id, $page, $where);
        $transacoes = $transacoesData["transactions"];
        $pageCount = $transacoesData["pageCount"];

        require_once "Views/Produtos/Produto.php";
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
