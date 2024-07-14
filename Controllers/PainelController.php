<?php
require_once "Controllers/_Controller.php";
require_once "Models/User.php";

class PainelController extends _Controller
{
    private $userModel;

    public function __construct()
    {
        parent::__construct("Painel");

        $this->verifyReadPermission();
        $this->verifyWritePermission();
        $this->verifyDeletePermission();

        $this->userModel = new User();
    }

    public function index()
    {
        $this->view("Painel/index");
    }

    public function usuarios()
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

        $this->view("Painel/usuarios", [
            "users" => $usersData["users"],
            "pageCount" => $usersData["pageCount"],
            "groups" => $this->userModel->getGroups(),
            "page" => $page
        ]);
    }

    public function grupos()
    {
        if (
            $_SERVER["REQUEST_METHOD"] == "POST"
            && isset($_POST["_method"])
            && $_POST["_method"] == "GET"
        ) {
            $permissions = $this->userModel->getGroupsPermissions($_POST["group_ID"]);
            echo json_encode($permissions);
            exit;
        }

        $mensagem_erro = isset($_SESSION["mensagem_erro"]) ? $_SESSION["mensagem_erro"] : "";
        unset($_SESSION["mensagem_erro"]);

        $sucesso = isset($_SESSION["sucesso"]) && $_SESSION["sucesso"];
        unset($_SESSION["sucesso"]);


        $groups = $this->userModel->getGroups();

        $this->view("Painel/grupos", [
            "groups" => $groups
        ]);
    }

    public function createGroup()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $groupName = $_POST["groupName"];
            try {
                $this->userModel->createGroup($groupName);
                $_SESSION["sucesso"] = true;
            } catch (Exception $e) {
                $_SESSION["mensagem_erro"] = "Erro ao criar grupo!";
            }


            header("Location: /painel/grupos");
            exit;
        }
    }

    public function updateGroupPermissions()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["_method"]) && $_POST["_method"] == "PUT") {
            $group_ID = $_POST["group_ID"];
            $permissions = $_POST["permissions"];

            try {
                $affected_rows = $this->userModel->updateGroupPermissions($group_ID, $permissions);
                echo json_encode([
                    "success" => true,
                    "affected_rows" => $affected_rows
                ]);
                $_SESSION["sucesso"] = true;
            } catch (Exception $e) {
                $_SESSION["mensagem_erro"] = "Erro ao atualizar permissões!";
                $_SESSION["error"] = $e->getMessage();
            }
        }

        header("Location: /painel/grupos");
        exit;
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
        }

        exit;
    }

    public function deleteGroup()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["group_ID"])) {
            try {
                $this->userModel->deleteGroup($_POST["group_ID"]);
                $_SESSION["sucesso"] = true;
            } catch (Exception $e) {
                $_SESSION["mensagem_erro"] = "Erro ao deletar grupo!";
            }
        }

        header("Location: /painel/grupos");
        exit;
    }
}
