<?php
require_once "Models/Container.php";
require_once "Controllers/_Controller.php";
require_once "Utils/PhpExcel.php";

class EmbarquesController extends _Controller
{
    private $containerModel;

    public function __construct()
    {
        parent::__construct("Embarques");

        $this->verifyReadPermission();
        $this->containerModel = new Container();
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

        $where = "1";
        if (isset($_GET["search"]) && !empty($_GET["search"])) {
            $search = htmlspecialchars($_GET["search"]);
            $where .= " AND lc.`name` LIKE '%$search%'";
        }

        if (isset($_GET["product_code"]) && !empty(trim($_GET["product_code"]))) {
            $product_code = trim(htmlspecialchars($_GET["product_code"]));
            $where .= " AND (p.`code` LIKE '%$product_code%' OR p.`ean` LIKE '%$product_code%')";
        }

        if (isset($_GET["importer"]) && !empty($_GET["importer"])) {
            $importer = htmlspecialchars($_GET["importer"]);
            $where .= " AND p.`importer` = '$importer'";
        }

        if (isset($_GET["status"]) && $_GET["status"] != "") {
            $status = htmlspecialchars($_GET["status"]);
            $where .= " AND pc.`in_stock` = $status";
        }

        if (
            isset($_GET["start_date"]) && !empty($_GET["start_date"])
            && isset($_GET["end_date"]) && !empty($_GET["end_date"])
        ) {
            $start_date = htmlspecialchars($_GET["start_date"]);
            $end_date = htmlspecialchars($_GET["end_date"]);
            $where .= " AND pc.`departure_date` <= '$end_date 23:59:59' AND pc.`departure_date` >= '$start_date 00:00:00'";
        } else if (isset($_GET["start_date"]) && !empty($_GET["start_date"])) {
            $start_date = htmlspecialchars($_GET["start_date"]);
            $where .= " AND pc.`departure_date`  <= '$start_date 23:59:59'";
        }

        $productsData = $this->containerModel->getAllProducts($page, where: $where);
        $products = $productsData["products"];
        $pageCount = $productsData["pageCount"];

        $this->view("Embarques/index", [
            "products" => $products,
            // "container_ID" => $container_ID,
            // "containers" => $result["dados"],
            "pageCount" => $pageCount,
            "page" => $page,
            "sucesso" => $sucesso,
            "mensagem_erro" => $mensagem_erro,
            "totalBoxes" => $productsData["totalBoxes"],
            "totalProducts" => $productsData["totalProducts"]
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
                if ($date === false) {
                    $_SESSION["mensagem_erro"] = "Falha ao importar arquivo! A data de embarque não é válida na linha " . ($index + 2);
                    header("Refresh: 0; URL = /embarques");
                    return;
                }

                $ean = $ean ? $ean : null;
                $description_chinese = $description_chinese ? $description_chinese : null;

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

    public function conferir($container_ID)
    {
        $this->verifyWritePermission();

        $sucesso = isset($_SESSION['sucesso']) ? $_SESSION['sucesso'] : false;
        unset($_SESSION['sucesso']);

        $mensagem_erro = isset($_SESSION['mensagem_erro']) ? $_SESSION['mensagem_erro'] : "";
        unset($_SESSION['mensagem_erro']);

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            try {
                if (!isset($_POST["to_stock"]) || !is_numeric($_POST["to_stock"])) {
                    throw new Exception("O estoque precisa ser especificado");
                }
                $to_stock = $_POST["to_stock"];

                $productsToConfirm = []; // Renomeado para evitar conflito com a variável de view

                // O JavaScript está enviando os dados como 'products_data'
                if (isset($_POST['products_data']) && is_array($_POST['products_data'])) {
                    foreach ($_POST['products_data'] as $productData) {
                        // Verifique se os campos necessários existem antes de usá-los
                        $product_ID = $productData["product_ID"] ?? null;
                        $quantity_delivered = $productData["quantity_delivered"] ?? 0;
                        $quantity_expected = $productData["quantity_expected"] ?? 0; // Captura a quantidade esperada editada
                        $observations = $productData["observation"] ?? '';

                        // Validação básica: garante que o product_ID e quantity_delivered são válidos
                        if ($product_ID !== null && $quantity_delivered >= 0) {
                            $productsToConfirm[] = [
                                "product_ID" => $product_ID,
                                "quantity_delivered" => $quantity_delivered, // Mantém o nome para clareza
                                "quantity_expected" => $quantity_expected, // Incluindo o novo campo
                                "observations" => $observations
                            ];
                        }
                    }
                } else {
                    throw new Exception("Dados de produtos não recebidos ou em formato inválido.");
                }

                $arrival_date = $_POST["arrival_date"] ?? date('Y-m-d'); // Use a data do POST ou a data atual como fallback

                // Exemplo de como a chamada ao modelo **poderia** ser, dependendo da sua lógica:
                $this->containerModel->confirmProducts(
                    $container_ID,
                    $productsToConfirm,
                    $arrival_date,
                    $to_stock
                );

                $_SESSION["sucesso"] = true;
            } catch (Exception $e) {
                $_SESSION["mensagem_erro"] = "Falha ao confirmar produtos! \n" . $e->getMessage();
            }

            header("Refresh: 0; URL = /embarques");
            return;
        }

        // --- Lógica para exibir a página de conferência (GET request) ---
        $page = 1;
        if (isset($_GET["page"])) {
            $page = (int) $_GET["page"];
        }

        $where = "pc.`in_stock` = 0";

        // Certifique-se de que produtosById está retornando a 'quantity_expected' corretamente para a view
        $productsData = $this->containerModel->produtosById($container_ID, $page, where: $where);
        $container = $this->containerModel->byId($container_ID);

        $this->view("Embarques/Conferir", [
            "products" => $productsData["products"],
            "container_ID" => $container_ID,
            "container" => $container,
            "pageCount" => $productsData["pageCount"],
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
        $this->verifyEditPermission();

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

    public function editar($container_ID)
    {
        $this->verifyEditPermission();

        $redirect = "/embarques";

        if (isset($_GET["redirect"])) {
            $redirect = htmlspecialchars($_GET["redirect"]);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST["departure_date"])) {
                $this->containerModel->editDate($container_ID, $_POST["departure_date"]);
            }

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
