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
        $sql =  "SELECT 
            *, 
            (SELECT COUNT(*) FROM products_in_container WHERE container_ID = lote_container.ID) as total,
            (SELECT COUNT(*) FROM products_in_container WHERE container_ID = lote_container.ID AND in_stock = 1) as conferidos
        FROM lote_container
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

    public function importData($products)
    {

        /**
         * Criando os containers
         * 1) Pegar a chave lote de todos os produtos e agrupar
         * 2) Se não existir, criar um container para cada lote
         */
        $containers = [];
        foreach ($products as $product) {
            $containers[$product["lote"]] = $product["lote"];
        }

        $containers = array_values($containers);
        var_dump($containers);

        // $container_com_id = []; // Array com o ID do container e o lote

        // foreach ($containers as $container) {
        //     // Verificar se o container já existe
        //     $stmt = $this->db->prepare("SELECT * FROM `lote_container` WHERE `lote` = ?");
        //     $stmt->bind_param("s", $container);
        //     $stmt->execute();

        //     $result = $stmt->get_result();
        //     $container = $result->fetch_assoc();

        //     if (!$container) {
        //         $stmt = $this->db->prepare("INSERT INTO `lote_container` (`lote`) VALUES (?)");
        //         $stmt->bind_param("s", $container);
        //         $stmt->execute();

        //         $container_com_id[] = [$container, $stmt->insert_id];
        //     } else {
        //         $container_com_id[] = [$container, $container["ID"]];
        //     }
        // }

        /**
         * Criando produtos quando estes não existirem
         * Adicionando os produtos no seu respectivo container
         * 1) Verificar se o produto existe
         * 2) Se não existir, criar o produto
         * 3) Adicionar o produto no container
         */
    }
}
