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
        if (isset($_SESSION["username"]) && isset($_SESSION["email"])) {
            header("location: /");
            exit(0);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST["email"];
            $password = $_POST["password"];

            $user = $this->userModel->login($email, $password);

            if ($user) {
                $_SESSION["username"] = $user["username"];
                $_SESSION["email"] = $user["email"];
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
