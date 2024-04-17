<?php
require_once "Controllers/_Controller.php";
require_once "Models/User.php";

class PainelController extends _Controller
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();

        $this->userModel = new User();

        if (
            !$this->userPermissionManager->controller("Painel")->canRead()
            || !$this->userPermissionManager->controller("Painel")->canWrite()
            || !$this->userPermissionManager->controller("Painel")->canDelete()
        ) {
            header("Location: /");
            exit;
        }
    }

    public function index()
    {
        $sucesso = isset($_SESSION["sucesso"]) && $_SESSION["sucesso"];
        unset($_SESSION["sucesso"]);
        $mensagem_erro = isset($_SESSION["mensagem_erro"]) ? $_SESSION["mensagem_erro"] : "";
        unset($_SESSION["mensagem_erro"]);

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["_method"])) {
            if ($_POST["_method"] == "delete" && isset($_POST["user_ID"])) {
                try {
                    $this->userModel->deleteUser($_POST["user_ID"]);
                    $_SESSION["sucesso"] = true;
                } catch (Exception $e) {
                    $_SESSION["mensagem_erro"] = "Erro ao deletar usuário!";
                }

                header("Location: /painel");
                exit;
            }
        }

        $page = 1;
        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        }

        $usersData = $this->userModel->getAllUsers($page);

        $this->view("Painel/index", [
            "users" => $usersData["users"],
            "pageCount" => $usersData["pageCount"],
            "groups" => $this->userModel->getGroups(),
            "page" => $page
        ]);
    }

    public function updateUser()
    {
        // Rota que sera chamada para atualizar um usuário atraves de um fetch no front-end
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["user_ID"])) {
            $types = "";
            $properties = array();

            if (isset($_POST["group_ID"]) && $_POST["group_ID"] != "") {
                $types .= "i";
                $properties["group_ID"] = $_POST["group_ID"];
            }

            if (isset($_POST["active"]) && $_POST["active"] != "") {
                $types .= "i";
                $properties["active"] = $_POST["active"];
            }

            if ($types != "") {
                try {
                    $this->userModel->updateUser($_POST["user_ID"], $types, $properties);
                    echo json_encode([
                        "success" => true
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        "success" => false,
                        "message" => "Erro ao atualizar usuário!"
                    ]);
                }
            }

            exit;
        }

        exit;
    }
}
