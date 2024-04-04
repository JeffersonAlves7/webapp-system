<?php

class _Controller {
    public function __construct() {
        if (!isset($_SESSION["username"]) && !isset($_SESSION["email"])) {
            header("location: /auth/login");
            exit(0);
        }
    }
}