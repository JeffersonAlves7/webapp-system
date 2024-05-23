<?php
require_once "Models/Model.php";

class Relatorios extends Model
{
    public function saidasDiarias($where = "1", $dataSaida = null)
    {
        if ($dataSaida == null) {
            $dataSaida = date("Y-m-d");
        }

        $sql = "SELECT 
            p.code, 
            SUM(t.quantity) as `QUANTIDADE`,
            tt.type as `TIPO`,
            t.client_name as `CLIENTE`,
            t.operator_ID as `OPERADOR`,
            if(t.to_stock_ID IS NULL, t.from_stock_ID, t.to_stock_ID) as `ORIGEM`,
            t.observation as `OBSERVACAO`,
            t.created_at as `DATA`
        FROM `transactions` t
            INNER JOIN `transaction_types` tt ON tt.ID = t.type_ID
            INNER JOIN `products` p ON p.ID = t.product_ID
            WHERE
                DATE(t.created_at) = ?
                AND
                tt.type IN ('Saída', 'Devolução')
                AND $where
            GROUP BY t.client_name, t.type_ID
            ORDER BY t.created_at DESC; 
        ";


        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $dataSaida);
        $stmt->execute();

        $result = $stmt->get_result();

        $stmt->close();

        return $result;
    }
}
