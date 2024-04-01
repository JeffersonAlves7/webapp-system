<?php
require_once "Models/Model.php";

class Historico extends Model
{
    public function getTransferencias()
    {
        $sql = "SELECT `transferences`.*, `products`.`code`, `from_stock`.`name` as `from_stock_name`, `to_stock`.`name` as `to_stock_name` FROM `transferences` 
        INNER JOIN `products` ON `transferences`.`product_ID` = `products`.`ID`
        LEFT JOIN `stocks` AS `from_stock` ON `transferences`.`from_stock_ID` = `from_stock`.`ID`
        LEFT JOIN `stocks` AS `to_stock` ON `transferences`.`to_stock_ID` = `to_stock`.`ID`
        WHERE `confirmed` = 1";

        $result = $this->db->query($sql);

        $transferences = [];
        while ($row = $result->fetch_assoc()) {
            $transferences[] = $row;
        }

        return $transferences;
    }
}
