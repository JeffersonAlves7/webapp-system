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
}
