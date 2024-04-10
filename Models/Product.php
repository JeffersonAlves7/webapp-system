<?php
require_once "Models/Model.php";

class Product extends Model
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
        $sql =  "SELECT * FROM products WHERE $where ORDER BY `ID` DESC LIMIT $limit OFFSET $offset";
        $products = $this->db->query($sql);
        $pageCount = ceil($this->db->query("SELECT COUNT(*) as count FROM products WHERE $where")->fetch_assoc()["count"] / $limit);

        return array(
            "products" => $products,
            "pageCount" => $pageCount
        );
    }

    public function create($code, $ean, $importer, $description, $chinese_description)
    {
        $ean_string = isset($ean) && $ean != "" ? "\"$ean\"" : "null";
        $description_string = isset($description) && $description != "" ? "\"$description\"" : "null";
        $chinese_description_string = isset($chinese_description) && $chinese_description != "" ? "\"$chinese_description\"" : "null";

        $sql = "INSERT INTO `products`
        (
            `code`, 
            `ean`, 
            `importer`, 
            `description`, 
            `chinese_description`
        ) VALUES 
        (
            \"$code\",
            $ean_string,
            \"$importer\",
            $description_string,
            $chinese_description_string
        )";

        return $this->db->query($sql);
    }

    public function update($id, $code, $ean, $importer, $description, $chinese_description)
    {
        $fieldsToUpdate = array();

        if (!empty($code)) {
            $fieldsToUpdate[] = "code = '$code'";
        }
        if (!empty($ean)) {
            $fieldsToUpdate[] = "ean = '$ean'";
        }
        if (!empty($importer)) {
            $fieldsToUpdate[] = "importer = '$importer'";
        }
        if (!empty($description)) {
            $fieldsToUpdate[] = "description = '$description'";
        }
        if (!empty($chinese_description)) {
            $fieldsToUpdate[] = "chinese_description = '$chinese_description'";
        }

        if (!empty($fieldsToUpdate)) {
            $fieldsString = implode(", ", $fieldsToUpdate);

            $sql = "UPDATE `products` SET $fieldsString WHERE ID = $id";

            $this->db->query($sql);

            return true;
        } else {
            return false;
        }
    }

    public function delete($id)
    {
        $sql = "DELETE FROM `products` WHERE `ID` = $id";
        return $this->db->query($sql);
    }

    public function byId($id)
    {
        $result = $this->db->query("SELECT * FROM `products` WHERE `ID` = $id");
        return $result;
    }

    /**
     * Função utilizada para autocompletar inputs onde o usuário escreve o código ou ean na aba Lancamento.
     */
    public function findAllByCodeOrEan($code_or_ean, $limit = 10)
    {

        $sql = "SELECT * FROM 
            `products` 
        WHERE 
            `code` LIKE '$code_or_ean%' OR 
            `ean` LIKE '$code_or_ean%' LIMIT $limit";

        return $this->db->query($sql);
    }

    public function quantityInStockById($id)
    {
        $sql = "SELECT qs.*, s.name as stock_name FROM `quantity_in_stock` qs 
        INNER JOIN `stocks` s ON s.ID = qs.stock_ID
        WHERE qs.product_ID = $id";

        $result = $this->db->query($sql);
        return $result;
    }
}
