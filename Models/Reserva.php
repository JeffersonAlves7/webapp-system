<?php
require_once "Models/Model.php";

class Reserva extends Model
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
        $sql = "SELECT 
                r.*,
                p.code AS code,
                s.name AS origin_container
            FROM 
                reserves AS r
            INNER JOIN 
                products AS p ON r.product_ID = p.ID
            INNER JOIN 
                stocks AS s ON r.stock_ID = s.ID
            WHERE 
                $where
            ORDER BY 
                r.created_at DESC 
            LIMIT 
                $limit OFFSET $offset";

        $reserves = $this->db->query($sql);

        return $reserves;
    }

    public function delete($id)
    {
        $results = $this->db->query("SELECT * FROM `reserves` WHERE `ID` = $id AND `confirmed` = 0");

        if ($results->num_rows > 0) {
            $reserve = $results->fetch_assoc();

            $quantity = $reserve['quantity'];
            $stock_ID = $reserve['stock_ID'];
            $product_ID = $reserve['product_ID'];

            $this->db->query("UPDATE quantity_in_stock 
            SET quantity_in_reserve = quantity_in_reserve - $quantity, quantity = quantity + $quantity 
            WHERE stock_ID = $stock_ID AND product_ID = $product_ID");

            $sql = "DELETE FROM `reserves` WHERE `ID` = $id";

            return $this->db->query($sql);
        }

        return false;
    }

    public function confirm($id)
    {
        $results = $this->db->query("SELECT * FROM `reserves` WHERE `ID` = $id AND `confirmed` = 0");

        if ($results->num_rows > 0) {
            $reserve = $results->fetch_assoc();

            $quantity = $reserve['quantity'];
            $stock_ID = $reserve['stock_ID'];
            $product_ID = $reserve['product_ID'];

            $this->db->query("UPDATE `reserves` SET `confirmed` = 1 WHERE `ID` = $id");

            $sql = "UPDATE quantity_in_stock 
                SET quantity_in_reserve = quantity_in_reserve - $quantity 
                WHERE stock_ID = $stock_ID AND product_ID = $product_ID";

            $this->db->query($sql);
            return true;
        }

        return false;
    }
}
