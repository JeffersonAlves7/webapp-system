<?php
require_once "Models/Model.php";

class Historico extends Model
{
    public function getTransferencias($page = 1, $limit = 10, $where = "1")
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT `transferences`.*, `products`.`code`, `from_stock`.`name` as `from_stock_name`, `to_stock`.`name` as `to_stock_name` FROM `transferences`
        INNER JOIN `products` ON `transferences`.`product_ID` = `products`.`ID`
        INNER JOIN `stocks` AS `from_stock` ON `transferences`.`from_stock_ID` = `from_stock`.`ID`
        INNER JOIN `stocks` AS `to_stock` ON `transferences`.`to_stock_ID` = `to_stock`.`ID`
        WHERE confirmed = 1 AND $where
        ORDER BY `transferences`.`created_at` DESC
        LIMIT $limit OFFSET $offset";

        $result = $this->db->query($sql);

        $pageCount = ceil($this->db->query("SELECT COUNT(*) FROM `transferences`
        INNER JOIN `products` ON `transferences`.`product_ID` = `products`.`ID`
        INNER JOIN `stocks` AS `from_stock` ON `transferences`.`from_stock_ID` = `from_stock`.`ID`
        INNER JOIN `stocks` AS `to_stock` ON `transferences`.`to_stock_ID` = `to_stock`.`ID`
        WHERE confirmed = 1 AND $where")->fetch_row()[0] / $limit);

        return [
            "transferences" => $result->fetch_all(MYSQLI_ASSOC),
            "pageCount" => $pageCount
        ];
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

        $pageCount = ceil($this->db->query("SELECT COUNT(*) FROM `reserves` 
        INNER JOIN `products` ON `reserves`.`product_ID` = `products`.`ID` 
        WHERE confirmed = 1 AND $where")->fetch_row()[0] / $limit);

        return [
            "reservations" => $result->fetch_all(MYSQLI_ASSOC),
            "pageCount" => $pageCount
        ];
    }

    public function getAll(
        $transaction_type_id,
        $page = 1,
        $limit = 10,
        $where = "1"
    ) {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT 
                `transactions_history`.*, 
                `products`.`code`, 
                COALESCE(`from_stock`.`name`, 'N/A') as `from_stock_name`,
                COALESCE(`to_stock`.`name`, 'N/A') as `to_stock_name`
            FROM `transactions_history`
            INNER JOIN `products` ON `transactions_history`.`product_ID` = `products`.`ID`
            LEFT JOIN `stocks` AS `from_stock` ON `transactions_history`.`from_stock_ID` = `from_stock`.`ID`
            LEFT JOIN `stocks` AS `to_stock` ON `transactions_history`.`to_stock_ID` = `to_stock`.`ID`
            WHERE 
                `transactions_history`.`type_ID` = $transaction_type_id AND
                $where
            ORDER BY `transactions_history`.`created_at` DESC
            LIMIT $limit OFFSET $offset";

        $result = $this->db->query($sql);

        $pageCount = ceil($this->db->query("SELECT COUNT(*) FROM `transactions_history`
            INNER JOIN `products` ON `transactions_history`.`product_ID` = `products`.`ID`
            LEFT JOIN `stocks` AS `from_stock` ON `transactions_history`.`from_stock_ID` = `from_stock`.`ID`
            LEFT JOIN `stocks` AS `to_stock` ON `transactions_history`.`to_stock_ID` = `to_stock`.`ID`
            WHERE 
                `transactions_history`.`type_ID` = $transaction_type_id AND
                $where")->fetch_row()[0] / $limit);

        return [
            "transactions" => $result->fetch_all(MYSQLI_ASSOC),
            "pageCount" => $pageCount
        ];
    }
}
