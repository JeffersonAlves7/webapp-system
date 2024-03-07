<?php
require_once "Models/Database.php";

class Model
{
    protected $db;

    public function __construct()
    {
        $this->db = new Database();
    }
}
