<?php
require_once "Managers/AuthManager.php";

class AuthController
{
    private $authManager;

    public function __construct()
    {
        $this->authManager = new AuthManager();
    }

    public function login()
    {
        $mensagem_erro = isset($_SESSION['mensagem_erro']) ? $_SESSION['mensagem_erro'] : "";
        unset($_SESSION['mensagem_erro']); // Remove a mensagem de erro da sessão

        if (AuthManager::isLoggedIn()) {
            header("location: /");
            exit(0);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST["email"];
            $password = $_POST["password"];

            $user = $this->authManager->login($email, $password);

            if ($user) {
                AuthManager::setUser($user["ID"], $user["email"], $user["username"]);
            } else if ($user === false) {
                $_SESSION['mensagem_erro'] = "Usuário desativado, entre em contato com um administrador";
            } else {
                $_SESSION['mensagem_erro'] = "Usuário ou senha inválidos";
            }

            header("location: /");
            exit(0);
        }

        require_once "Views/Login.php";
    }

    public function register()
    {
        if (isset($_SESSION["username"]) && isset($_SESSION["email"])) {
            header("location: /");
            exit(0);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST["username"];
            $email = $_POST["email"];
            $password = $_POST["password"];

            $this->authManager->register($username, $email, $password);

            header("location: /auth/login");
            exit(0);
        }

        require_once "Views/Register.php";
    }

    public function logout()
    {
        AuthManager::clearUser();
        AuthManager::logout();
        header("location: /");

        echo 'You have cleaned session';
        header('Refresh: 1; URL = login');
        exit(0);
    }

    public function info()
    {
        print_r($_SESSION);
    }
}
