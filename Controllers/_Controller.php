<?php
require_once "Managers/AuthManager.php";
require_once "Managers/UserPermissionManager.php";
require_once "Controllers/_Viewer.php";

class _Controller extends _Viewer
{
    protected $user;
    protected $userPermissionManager;

    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $user = AuthManager::getUser();
        $this->user = $user;
        AuthManager::checkLogin();
        $this->userPermissionManager = new UserPermissionManager($user['id']);
    }

    protected function verifyWritePermission($controller_name)
    {
        if (
            !$this->userPermissionManager->controller($controller_name)->canWrite()
        ) {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para realizar esta ação!";
            header("Location: /produtos");
            exit;
        }
    }

    protected function verifyDeletePermission($controller_name)
    {
        if (
            !$this->userPermissionManager->controller($controller_name)->canDelete()
        ) {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para realizar esta ação!";
            header("Location: /produtos");
            exit;
        }
    }

    protected function verifyReadPermission($controller_name)
    {
        if (
            !$this->userPermissionManager->controller($controller_name)->canRead()
        ) {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para acessar esta página!";
            header("Location: /");
            exit;
        }
    }

    protected function verifyEditPermission($controller_name)
    {
        if (
            !$this->userPermissionManager->controller($controller_name)->canEdit()
        ) {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para realizar esta ação!";
            header("Location: /produtos");
            exit;
        }
    }
}
