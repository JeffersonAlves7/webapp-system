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
        $containers = [];
        foreach ($products as $product) {
            $containers[$product["lote"]] = $product["lote"];
        }

        $containers = array_values($containers);

        // Criando containers
        $container_com_id = [];

        foreach ($containers as $container_name) {
            $stmt = $this->db->prepare("SELECT * FROM `lote_container` WHERE `name` = ?");
            $stmt->bind_param("s", $container_name);
            $stmt->execute();

            $result = $stmt->get_result();
            $container = $result->fetch_assoc();

            if (!$container) {
                $stmt = $this->db->prepare("INSERT INTO `lote_container` (`name`) VALUES (?)");
                $stmt->bind_param("s", $container_name);
                $stmt->execute();

                $container_com_id[$container_name] = $stmt->insert_id;
            } else {
                $container_com_id[$container_name] = $container["ID"];
            }
        }

        // Adicionando produtos nos containers
        foreach ($products as $product) {
            $stmt = $this->db->prepare("SELECT * FROM `products` WHERE `code` = ?");
            $stmt->bind_param("s", $product["code"]);
            $stmt->execute();

            $result = $stmt->get_result();
            $product_from_DB = $result->fetch_assoc();
            $container_ID = $container_com_id[$product["lote"]];

            if (!$product_from_DB) {
                // Criar produto no banco de dados se nao existir
                $stmt = $this->db->prepare("INSERT INTO `products` (`code`, `description`, `chinese_description`, `ean`, `importer`) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $product["code"], $product["description"], $product["description_chinese"], $product["ean"], $product["importer"]);
                $stmt->execute();

                $product_ID = $stmt->insert_id;
            } else {
                $product_ID = $product_from_DB["ID"];

                // Verificar se produto ja esta no container, se estiver passar para o proximo
                $stmt = $this->db->prepare("SELECT * FROM `products_in_container` WHERE `container_ID` = ? AND `product_ID` = ?");
                $stmt->bind_param("ii", $container_ID, $product_ID);
                $stmt->execute();

                if ($stmt->get_result()->fetch_assoc()) {
                    continue;
                }
            }

            // Adicionando o produto no container
            if ($product["status"] == "A caminho") {
                $stmt = $this->db->prepare("INSERT INTO `products_in_container` (`container_ID`, `product_ID`, `quantity_expected`, `in_stock`, `departure_date`) VALUES (?, ?, ?, 0, ?)");
                $stmt->bind_param("iiis", $container_ID, $product_ID, $product["quantity"], $product["date"]);
                $stmt->execute();
            } else {
                $stmt = $this->db->prepare("INSERT INTO `products_in_container` (`container_ID`, `product_ID`, `quantity`, `in_stock`, `departura_date`, `arrival_date`) VALUES (?, ?, ?, 1, NOW(), ?)");
                $stmt->bind_param("iiis", $container_ID, $product_ID, $product["quantity"], $product["date"]);
                $stmt->execute();

                $stmt = $this->db->prepare("UPDATE `products` SET `quantity` = `quantity` + ? WHERE `ID` = ?");
                $stmt->bind_param("ii", $product["quantity"], $product_ID);
                $stmt->execute();
            }
        }
    }

    public function byId($container_ID)
    {
        $stmt = $this->db->prepare("SELECT * FROM `lote_container` WHERE `ID` = ?");
        $stmt->bind_param("i", $container_ID);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
