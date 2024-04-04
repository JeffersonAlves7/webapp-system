<?php

class _Controller
{
    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $this->verifySession();
    }

    private function verifySession()
    {
        if (!isset($_SESSION["username"]) && !isset($_SESSION["email"])) {
            header("location: /auth/login");
            exit(0);
        }
    }
}
