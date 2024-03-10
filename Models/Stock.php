<?php
require_once "Models/Model.php";

class Stock extends Model
{
    /**
     * Função utilizada para autocompletar inputs onde o usuário escreve o nome do container.
     */
    public function findByName($name, $limit = 10)
    {

        $sql = "SELECT * FROM 
            `stocks` 
        WHERE 
            `name` LIKE '$name%' LIMIT $limit";

        return $this->db->query($sql);
    }
}
