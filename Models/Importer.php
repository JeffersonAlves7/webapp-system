<?php
require_once "Models/Database.php";

class Importer
{
    private $db;

    public function __construct($db = null)
    {
        // Não extendi a classe Model por conta das transactions. Nesses casos é melhor abrir uma nova conexão.
        if ($db) {
            $this->db = $db;
        } else {
            $this->db = new Database();
        }
    }

    public function getProductByCodeAndImporter(
        $code,
        $importer,
    ) {
        $sql = "SELECT * FROM `products` p
        WHERE p.`code` = '$code' AND p.`importer` = '$importer'";

        $result = $this->db->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    public function getProductQuantity(
        $product_ID,
        $stock_name
    ) {
        $sql = "SELECT qs.`ID`, qs.`quantity`, s.`name` as `stock_name`, s.`ID` as `stock_ID`
        FROM `quantity_in_stock` qs
        INNER JOIN `stocks` s ON qs.`stock_ID` = s.`ID`
        WHERE qs.`product_ID` = $product_ID AND s.`name` = '$stock_name'";

        $result = $this->db->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }
}
