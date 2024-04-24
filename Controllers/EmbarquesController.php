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

    public function importar()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $file = $_FILES["file"];
            $rows = PhpExcel::read($file["tmp_name"], 9);

            if (count($rows) == 0) {
                $_SESSION["mensagem_erro"] = "Falha ao importar arquivo! O arquivo não possuí dados";
                header("Refresh: 1; URL = /embarques");
                return;
            }

            echo PhpExcel::formatAsTable($rows);
            return;

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
                $date = $row[8];
                $status = $row[9];

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
                    header("Refresh: 1; URL = /embarques");
                    return;
                }

                // Tentar formatar data para dd/mm/yyyy, se não conseguir, retornar erro
                if (!strtotime($date)) {
                    $_SESSION["mensagem_erro"] = "Falha ao importar arquivo! Data inválida na linha " . ($index + 2);
                    header("Refresh: 1; URL = /embarques");
                    return;
                }

                $date = date("Y-m-d", strtotime($date));

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

        // header("Refresh: 1; URL = /embarques");
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
