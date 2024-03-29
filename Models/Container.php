<?php
require_once "Models/Model.php";

class Container extends Model
{
    private $MAX_LIMIT = 100;

    public function getAll(int $page = 1, int $limit = 50, $where = 1)
    {
        if ($page <= 0) {
            $page = 1;
        }

        if ($limit > $this->MAX_LIMIT) {
            $limit = $this->MAX_LIMIT;
        }

        $offset = ($page - 1) * $limit;
        $sql =  "SELECT * FROM lote_container 
        WHERE $where 
        ORDER BY `created_at` DESC LIMIT $limit OFFSET $offset";
        $products = $this->db->query($sql);

        return $products;
    }

    public function produtosById($id, int $page = 1, int $limit = 50, $where = 1)
    {
        if ($page <= 0) {
            $page = 1;
        }

        if ($limit > $this->MAX_LIMIT) {
            $limit = $this->MAX_LIMIT;
        }

        $offset = ($page - 1) * $limit;

        $sql =  "SELECT pc.*, p.`code` FROM products_in_container pc
        INNER JOIN products p ON p.ID = pc.product_ID
        WHERE pc.`container_ID` = $id AND $where
        ORDER BY `created_at` DESC LIMIT $limit OFFSET $offset";
        $products = $this->db->query($sql);

        return $products;
    }

    public function byId($id)
    {
        $sql =  "SELECT * FROM lote_container 
        WHERE `ID` = $id";

        return $this->db->query($sql);
    }

    public function deleteProduct($container_ID, $product_ID)
    {
        return $this->db->query("DELETE FROM `products_in_container` WHERE 
            `container_ID` = $container_ID AND 
            `product_ID` = $product_ID");
    }

    public function delete($container_ID)
    {
        return $this->db->query("DELETE FROM `lote_container` WHERE `ID` = $container_ID");
    }
}
