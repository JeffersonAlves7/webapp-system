<?php
require_once "Models/Model.php";

class Estoque extends Model
{
    public function getAll()
    {
        $sql = "SELECT * FROM `stocks` ORDER BY id DESC";
        return $this->db->query($sql);
    }

    public function create($name)
    {
        $sql = "INSERT INTO `stocks` (`name`) VALUES ('$name')";

        return $this->db->query($sql);
    }
}
