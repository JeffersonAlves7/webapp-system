<?php
require_once "Models/User.php";

class AuthController
{
    public $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST["email"];
            $password = $_POST["password"];

            $user = $this->userModel->login($email, $password);

            if ($user) {
                // Gerar token de sessão único
                $sessionToken = bin2hex(random_bytes(32)); // Gera um token de 256 bits

                // Associar token de sessão ao usuário no banco de dados
                $this->userModel->updateSessionToken($user["id"], $sessionToken);

                // Definir o token de sessão como cookie
                setcookie("session_token", $sessionToken, time() + (86400 * 30), "/"); // expira em 30 dias

                // Redirecionar para a página inicial
                header("location: /");
                exit(0);
            }
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

            $this->userModel->register($username, $email, $password);

            header("location: /auth/login");
            exit(0);
        }

        require_once "Views/Register.php";
    }

    public function logout()
    {
        unset($_SESSION["username"]);

        echo 'You have cleaned session';
        header('Refresh: 1; URL = login');
    }

    public function info()
    {
        print_r($_SESSION);
    }
}
