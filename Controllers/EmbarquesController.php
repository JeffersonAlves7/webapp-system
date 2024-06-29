<?php
require_once "Models/Container.php";
require_once "Controllers/_Controller.php";
require_once "Utils/PhpExcel.php";

class EmbarquesController extends _Controller
{
    private $containerModel;

    public function __construct()
    {
        parent::__construct();

        if (
            !$this->userPermissionManager->controller("Embarques")->canRead()
        ) {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para acessar os Embarques!";
            header("Location: /");
            exit;
        }


        $this->containerModel = new Container();
    }

    private function verifyWritePermission()
    {
        if (
            !$this->userPermissionManager->controller("Embarques")->canWrite()
        ) {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para realizar esta ação!";
            header("Location: /embarques");
            exit;
        }
    }

    private function verifyDeletePermission()
    {
        if (
            !$this->userPermissionManager->controller("Embarques")->canDelete()
        ) {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para realizar esta ação!";
            header("Location: /embarques");
            exit;
        }
    }


    public function index()
    {
        $mensagem_erro = isset($_SESSION['mensagem_erro']) ? $_SESSION['mensagem_erro'] : "";
        unset($_SESSION['mensagem_erro']);

        $sucesso = isset($_SESSION['sucesso']) ? $_SESSION['sucesso'] : false;
        unset($_SESSION['sucesso']);

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

        $this->view("Embarques/Index", [
            "containers" => $containers,
            "page" => $page,
            "sucesso" => $sucesso,
            "mensagem_erro" => $mensagem_erro
        ]);
    }

    public function importar()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->verifyWritePermission();

            $file = $_FILES["file"];
            $rows = PhpExcel::read($file["tmp_name"], 9);

            if (count($rows) == 0) {
                $_SESSION["mensagem_erro"] = "Falha ao importar arquivo! O arquivo não possuí dados";
                header("Refresh: 0; URL = /embarques");
                return;
            }

            echo PhpExcel::formatAsTable($rows);

            array_shift($rows);
            $products = [];

            $index = 0;
            foreach ($rows as $row) {
                $ean = $row[0];
                $code = $row[1];
                $description = $row[2];
                $description_chinese = $row[3];
                $quantity = $row[4];
                $importer = $row[5];
                $lote = $row[6];
                $date = $row[7];
                $status = $row[8];

                $campos_vazios = [];
                if (!$code || empty($code)) {
                    $campos_vazios[] = "Código";
                }

                if (!$description || empty($description)) {
                    $campos_vazios[] = "Descrição";
                }

                if (!$quantity || empty($quantity)) {
                    $campos_vazios[] = "Quantidade";
                }

                if (!$importer || empty($importer)) {
                    $campos_vazios[] = "Importador";
                }

                if (!$lote || empty($lote)) {
                    $campos_vazios[] = "Lote";
                }

                if (!$date || empty($date)) {
                    $campos_vazios[] = "Data de embarque";
                }

                if (!$status || empty($status)) {
                    $campos_vazios[] = "Status";
                }

                if (count($campos_vazios) > 0) {
                    $_SESSION["mensagem_erro"] = "Falha ao importar arquivo! Os seguintes campos estão vazios: " . implode(", ", $campos_vazios) . " na linha " . ($index + 2);
                    header("Refresh: 0; URL = /embarques");
                    return;
                }

                // Verificar se importadora eh igual a "ATTUS_BLOOM", "ALPHA_YNFINITY" ou "ATTUS". 
                if ($importer != "ATTUS_BLOOM" && $importer != "ALPHA_YNFINITY" && $importer != "ATTUS" && $importer != "ALPHA_YNFINITY") {
                    $_SESSION["mensagem_erro"] = "Falha ao importar arquivo! O importador não é válido na linha " . ($index + 2);
                    header("Refresh: 0; URL = /embarques");
                    return;
                }

                if (!preg_match("/\d{2}\/\d{2}\/\d{4}/", $date)) {
                    $_SESSION["mensagem_erro"] = "Falha ao importar arquivo! A data de embarque não está no formato correto (dd/mm/yyyy) na linha " . ($index + 2);
                    header("Refresh: 0; URL = /embarques");
                    return;
                }

                // Mudar data para formato do banco de dados
                $date = DateTime::createFromFormat("d/m/Y", $date)->format("Y-m-d");

                $ean = empty($product["ean"]) ? null : $product["ean"];
                $description_chinese = empty($product["description_chinese"]) ? null : $product["description_chinese"];

                $products[] = [
                    "ean" => $ean,
                    "code" => $code,
                    "description" => $description,
                    "description_chinese" => $description_chinese,
                    "quantity" => $quantity,
                    "importer" => $importer,
                    "lote" => $lote,
                    "date" => $date,
                    "status" => $status
                ];

                $index++;
            }

            $this->containerModel->importData($products);

            $_SESSION["sucesso"] = true;
        } else {
            $_SESSION["mensagem_erro"] = "Falha ao importar arquivo!";
        }

        header("Refresh: 0; URL = /embarques");
    }

    public function produtos($container_ID)
    {
        $mensagem_erro = isset($_SESSION['mensagem_erro']) ? $_SESSION['mensagem_erro'] : "";
        unset($_SESSION['mensagem_erro']);

        $sucesso = isset($_SESSION['sucesso']) ? $_SESSION['sucesso'] : false;
        unset($_SESSION['sucesso']);

        $page = 1;

        if (isset($_GET["page"])) {
            $page = (int) $_GET["page"];
        }

        $where = "1 = 1 ";

        // $result_container = $this->containerModel->byId($container_ID);
        $productsData = $this->containerModel->produtosById($container_ID, $page, where: $where);
        $products = $productsData["products"];
        $pageCount = $productsData["pageCount"];

        $this->view("Embarques/Produtos", [
            "products" => $products,
            "container_ID" => $container_ID,
            "pageCount" => $pageCount,
            "sucesso" => $sucesso,
            "mensagem_erro" => $mensagem_erro
        ]);
    }

    public function conferir($container_ID)
    {
        $this->verifyWritePermission();

        $sucesso = isset($_SESSION['sucesso']) ? $_SESSION['sucesso'] : false;
        unset($_SESSION['sucesso']);

        $mensagem_erro = isset($_SESSION['mensagem_erro']) ? $_SESSION['mensagem_erro'] : "";
        unset($_SESSION['mensagem_erro']);

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                $products = [];

                // Cada produto tem que ter o campo "product_ID" e "quantity_delivered"
                foreach ($_POST["selected"] as $index => $product_ID) {
                    $quantity_delivered = $_POST["quantity_delivered"][$index];
                    $observations = $_POST["observations"][$index];

                    $products[] = [
                        "product_ID" => $product_ID,
                        "quantity" => $quantity_delivered,
                        "observations" => $observations
                    ];
                }

                $arrival_date = $_POST["arrival_date"];

                // Transformar a data para o formato do banco de dados
                $this->containerModel->confirmProducts($container_ID, $products, $arrival_date);
                $_SESSION["sucesso"] = true;
            } catch (Exception $e) {
                // Mensagem generica de erro com quebra de linha e descricao do erro
                $_SESSION["mensagem_erro"] = "Falha ao confirmar produtos! \n" . $e->getMessage();
            }

            header("Refresh: 0; URL = /embarques");
            return;
        }

        $page = 1;

        if (isset($_GET["page"])) {
            $page = (int) $_GET["page"];
        }

        $where = "pc.`in_stock` = 0";

        $products = $this->containerModel->produtosById($container_ID, $page, where: $where);
        $container = $this->containerModel->byId($container_ID);

        $this->view("Embarques/Conferir", [
            "products" => $products["products"],
            "container_ID" => $container_ID,
            "container" => $container,
            "pageCount" => $products["pageCount"],
            "sucesso" => $sucesso,
            "mensagem_erro" => $mensagem_erro
        ]);
    }

    public function deletarProduto()
    {
        $this->verifyDeletePermission();

        $redirect = "/";

        if (isset($_GET["redirect"])) {
            $redirect = htmlspecialchars($_GET["redirect"]);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $product_ID = $_POST["product_ID"];
            $container_ID = $_POST["container_ID"];

            $this->containerModel->deleteProduct($container_ID, $product_ID);
            $_SESSION["sucesso"] = true;
        } else {
            $_SESSION["mensagem_erro"] = "Falha ao deletar produto!";
        }

        header("Refresh: 0; URL = $redirect");
    }

    public function editarProduto()
    {
        $this->verifyWritePermission();

        $redirect = "/";

        if (isset($_GET["redirect"])) {
            $redirect = htmlspecialchars($_GET["redirect"]);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $product_ID = $_POST["product_ID"];
            $container_ID = $_POST["container_ID"];
            $quantity = $_POST["quantity"];
            $arrival_date = $_POST["arrival_date"];

            $this->containerModel->editProduct($container_ID, $product_ID, $quantity, $arrival_date);
            $_SESSION["sucesso"] = true;
        } else {
            $_SESSION["mensagem_erro"] = "Falha ao editar produto!";
        }

        header("Refresh: 0; URL = $redirect");
    }

    public function deletar($id)
    {
        $this->verifyDeletePermission();

        $this->containerModel->delete($id);
        $_SESSION["sucesso"] = true;
        header("Refresh: 0; URL = /embarques");
    }
}
