<?php
require_once "Models/Model.php";

class Historico extends Model
{
    public function getTransferencias($where = "1")
    {
        $sql = "SELECT `transferences`.*, `products`.`code`, `from_stock`.`name` as `from_stock_name`, `to_stock`.`name` as `to_stock_name` FROM `transferences` 
        INNER JOIN `products` ON `transferences`.`product_ID` = `products`.`ID`
        LEFT JOIN `stocks` AS `from_stock` ON `transferences`.`from_stock_ID` = `from_stock`.`ID`
        LEFT JOIN `stocks` AS `to_stock` ON `transferences`.`to_stock_ID` = `to_stock`.`ID`
        WHERE confirmed = 1 AND $where";

        $result = $this->db->query($sql);

        $transferences = [];
        while ($row = $result->fetch_assoc()) {
            $transferences[] = $row;
        }

        return $transferences;
    }

    public function getReservas($page = 1, $limit = 10, $where = "1")
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT `reserves`.*, `products`.`code`, `stocks`.`name` as `stock_name` FROM `reserves`
        INNER JOIN `products` ON `reserves`.`product_ID` = `products`.`ID`
        INNER JOIN `stocks` ON `reserves`.`stock_ID` = `stocks`.`ID`
        WHERE confirmed = 1 AND $where
        ORDER BY `reserves`.`created_at` DESC
        LIMIT $limit OFFSET $offset";

        $result = $this->db->query($sql);

        $pageCount = ceil($this->db->query("SELECT COUNT(*) FROM `reserves` WHERE confirmed = 1 AND $where")->fetch_row()[0] / $limit);

        return [
            "reservations" => $result->fetch_all(MYSQLI_ASSOC),
            "pageCount" => $pageCount
        ];
    }
}
