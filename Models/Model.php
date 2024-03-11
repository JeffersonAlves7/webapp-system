<?php
require_once "Models/Database.php";

class Model
{
    protected $db;

    public function __construct()
    {
        // Fiz dessa forma para que aproveitasse sessões criadas anteriormente
        $this->db = Database::getInstance();
    }
}
