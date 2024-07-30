<?php
require_once "Models/Database.php";
require_once "Models/Model.php";

class Transacao extends Model
{
    private $MAX_LIMIT = 50;

    public function getAllByProductId($id, $page = 1, $where = 1, $limit = 20)
    {
        if ($page <= 0) {
            $page = 1;
        }

        if ($limit > $this->MAX_LIMIT) {
            $limit = $this->MAX_LIMIT;
        }

        $offset = ($page - 1) * $limit;
        $sql = "SELECT 
            t.ID, 
            t.quantity, 
            tt.`type` as `type`, 
            sf.name as from_stock, 
            st.name as to_stock, 
            t.observation, 
            t.client_name,
            t.updated_at
        FROM `transactions` t
        INNER JOIN transaction_types tt ON t.type_ID = tt.ID
        LEFT JOIN stocks sf ON t.from_stock_ID = sf.ID
        LEFT JOIN stocks st ON t.to_stock_ID = st.ID
        WHERE 
            product_ID = $id 
            AND $where
        ORDER BY created_at DESC
        LIMIT 
            $limit OFFSET $offset";

        $result = $this->db->query($sql);
        $pageCount = ceil($this->db->query("SELECT COUNT(*) as count FROM transactions WHERE product_ID = $id AND $where")->fetch_assoc()["count"] / $limit);

        return array(
            "transactions" => $result->fetch_all(MYSQLI_ASSOC),
            "pageCount" => $pageCount
        );
    }

    public function delete($id)
    {
        $result =  $this->db->query("SELECT * FROM `transactions` t 
            INNER JOIN `transaction_types` tt ON t.`type_ID` = tt.ID
            WHERE t.`ID` = $id");

        if ($result->num_rows > 0) {
            $transaction = $result->fetch_assoc();
            $type = $transaction["type"];
            $quantity = $transaction['quantity'];
            $product_ID = $transaction['product_ID'];
            $from_stock_ID = $transaction["from_stock_ID"];
            $to_stock_ID = $transaction["to_stock_ID"];

            switch ($type) {
                case "Entrada":
                    $this->db->query("UPDATE `quantity_in_stock` SET quantity = quantity - $quantity 
                    WHERE product_ID = $product_ID AND stock_ID = $to_stock_ID");
                    break;
                case "Saída":
                    $this->db->query("UPDATE `quantity_in_stock` SET quantity = quantity + $quantity 
                    WHERE product_ID = $product_ID AND stock_ID = $from_stock_ID");
                    break;
                case "Transferência":
                    $this->db->query("UPDATE `quantity_in_stock` SET quantity = quantity + $quantity 
                    WHERE product_ID = $product_ID AND stock_ID = $from_stock_ID");

                    $this->db->query("UPDATE `quantity_in_stock` SET quantity = quantity - $quantity 
                    WHERE product_ID = $product_ID AND stock_ID = $to_stock_ID");
                    break;
                default:
                    break;
            }


            $this->db->query("DELETE FROM `transactions` WHERE `ID` = $id");
        } else {
            throw new Exception("Transação não encontrada");
        }
    }
}
