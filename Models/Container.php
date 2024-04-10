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
        $result = $this->db->query($sql);

        $containers = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $containers[] = $row;
            }
        }

        return $containers;
    }

    public function produtosById($container_ID, int $page = 1, int $limit = 50, $where = 1)
    {
        if ($page <= 0) {
            $page = 1;
        }

        if ($limit > $this->MAX_LIMIT) {
            $limit = $this->MAX_LIMIT;
        }

        $offset = ($page - 1) * $limit;

        $sql =  "SELECT pc.*, p.`code`, p.`importer` FROM products_in_container pc
        INNER JOIN products p ON p.ID = pc.product_ID
        WHERE pc.`container_ID` = $container_ID AND $where
        ORDER BY `created_at` DESC LIMIT $limit OFFSET $offset";
        $products = $this->db->query($sql);

        $pageCount = ceil($this->db->query("SELECT COUNT(*) as count FROM products_in_container WHERE `container_ID` = $container_ID")->fetch_assoc()["count"] / $limit);

        return [
            "products" => $products,
            "pageCount" => $pageCount
        ];
    }

    public function deleteProduct($container_ID, $product_ID)
    {
        $this->db->query("DELETE FROM `products_in_container` WHERE 
            `container_ID` = $container_ID AND 
            `product_ID` = $product_ID");
    }

    public function delete($container_ID)
    {
        $this->db->query("DELETE FROM `lote_container` WHERE `ID` = $container_ID");
    }

    public function produtosNotInStock($container_ID)
    {
        $sql =  "SELECT pc.*, p.`code` FROM products_in_container pc
        INNER JOIN products p ON p.ID = pc.product_ID
        WHERE pc.`container_ID` = $container_ID AND pc.`in_stock` = 0
        ORDER BY `created_at` DESC";
        $result = $this->db->query($sql);
        $products = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        return $products;
    }

    public function confirmProducts($container_ID, $products)
    {
        // A variavel produtos precisa ter o Id dos produtos, tambem precisa ter a quantidade que foi entregue

        foreach ($products as $product) {
            $this->db->query("UPDATE `products_in_container` SET 
            `in_stock` = 1, 
            `arrival_date` = NOW(), 
            `quantity` = {$product['quantity']}
            WHERE `container_ID` = $container_ID AND `product_ID` = {$product['product_ID']}");
        }
    }
}
