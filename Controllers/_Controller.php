<?php
require_once "Managers/AuthManager.php";

class _Controller
{
    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        AuthManager::checkLogin();
    }
}
