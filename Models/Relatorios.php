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

    public function estoqueMinimo($page = 1, $limit = 30, $porcentagem = 0.50, $quantidadeDePaginas = false)
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT 
            p.code as 'CODIGO', 
            (qs.quantity + qs.quantity_in_reserve) as 'SALDO', 
            t.quantity as 'ENTRADA',
            ? * t.quantity as 'QUANTIDADE DE ALERTA'
        FROM products p
            INNER JOIN quantity_in_stock qs ON qs.product_ID = p.ID AND qs.stock_ID = 1
            INNER JOIN transactions t ON t.type_ID = 1 AND t.product_ID = p.ID
            WHERE (qs.quantity + qs.quantity_in_reserve) < ? * t.quantity
        ORDER BY t.updated_at
        LIMIT ? OFFSET ?;
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ddii", $porcentagem, $porcentagem, $limit, $offset);

        $stmt->execute();
        $result = $stmt->get_result();

        // Get the total number of records
        if($quantidadeDePaginas == false){
            $sqlTotal = "SELECT COUNT(*) as total
            FROM products p
                INNER JOIN quantity_in_stock qs ON qs.product_ID = p.ID AND qs.stock_ID = 1
                INNER JOIN transactions t ON t.type_ID = 1 AND t.product_ID = p.ID
                WHERE (qs.quantity + qs.quantity_in_reserve) < ? * t.quantity
            ";

            $stmtTotal = $this->db->prepare($sqlTotal);
            $stmtTotal->bind_param("d", $porcentagem);

            $stmtTotal->execute();
            $resultTotal = $stmtTotal->get_result();
            $rowTotal = $resultTotal->fetch_assoc();

            $totalPages = ceil($rowTotal['total'] / $limit);
        }else{
            $totalPages = $quantidadeDePaginas;
        }

        return [
            "dados" => $result,
            "totalPages" => $totalPages
        ];
    }
}
