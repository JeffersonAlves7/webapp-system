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
            s.name as `ORIGEM`,
            t.observation as `OBSERVACAO`,
            t.created_at as `DATA`
        FROM `transactions` t
            INNER JOIN `transaction_types` tt ON tt.ID = t.type_ID
            INNER JOIN `products` p ON p.ID = t.product_ID
            INNER JOIN `stocks` s ON s.ID = t.from_stock_ID
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
        if ($quantidadeDePaginas == false) {
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
        } else {
            $totalPages = $quantidadeDePaginas;
        }

        return [
            "dados" => $result,
            "totalPages" => $totalPages
        ];
    }

    public function movimentacoes($dataMovimentacao, $page = 1, $limit = 30)
    {
        $offset = ($page - 1) * $limit;

        $sqlTotal = "SELECT SUM(quantity) AS total
                FROM transactions
                WHERE 
                    type_ID = 2 
                    AND created_at >= CONCAT(?, '-01 00:00:00') 
                    AND created_at < CONCAT(DATE_ADD(CONCAT(?, '-01'), INTERVAL 1 MONTH), ' 00:00:00')";

        $stmtTotal = $this->db->prepare($sqlTotal);
        $stmtTotal->bind_param("ss", $dataMovimentacao, $dataMovimentacao);
        $stmtTotal->execute();

        $resultTotal = $stmtTotal->get_result();
        $rowTotal = $resultTotal->fetch_assoc();
        if ($rowTotal['total'] == 0) {
            return false;
        }

        $sqlMain = "SELECT 
                p.code AS 'CODIGO', 
                SUM(t.quantity) AS 'SAIDAS',
                ROUND((SUM(t.quantity) / ?) * 100, 2) AS 'PERCENTUAL',
                qs.total_stock AS 'ESTOQUE'
            FROM 
                products p 
            INNER JOIN 
                transactions t ON t.product_ID = p.ID 
            INNER JOIN (
                SELECT 
                    product_ID, 
                    SUM(quantity) + SUM(quantity_in_reserve) AS total_stock
                FROM 
                    quantity_in_stock
                GROUP BY 
                    product_ID
            ) qs ON qs.product_ID = p.ID
            WHERE 
                t.type_ID = 2 
                AND t.created_at >= CONCAT(?, '-01 00:00:00') 
                AND t.created_at < CONCAT(DATE_ADD(CONCAT(?, '-01'), INTERVAL 1 MONTH), ' 00:00:00')
            GROUP BY 
                p.ID
            ORDER BY
                SAIDAS DESC
            LIMIT ? OFFSET ?;";


        $stmtMain = $this->db->prepare($sqlMain);
        $stmtMain->bind_param("dssii", $rowTotal['total'], $dataMovimentacao, $dataMovimentacao, $limit, $offset);
        $stmtMain->execute();

        $results = $stmtMain->get_result();

        return $results;
    }

    public function comparativoDeVendas($meses)
    {
        $results = [];

        foreach ($meses as $key => $mes) {
            $sql = "SELECT 
                        SUM(t.quantity) as total, DAY(t.created_at) as 'DAY'
                        from transactions t
                    WHERE 
                        t.type_ID = 2 
                        AND t.created_at >= CONCAT(?, '-01 00:00:00')
                        AND t.created_at < CONCAT(DATE_ADD(CONCAT(?, '-01'), INTERVAL 1 MONTH), ' 00:00:00')
                    GROUP BY 'DAY' 
                    ORDER BY 'DAY' ASC;
                ";

            $stmt = $this->db->prepare($sql);

            $stmt->bind_param("ss", $mes, $mes);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                $results[$mes] = [];
                continue;
            }

            while ($row = $result->fetch_assoc()) {
                $dia = $row["DAY"];
                $totalInt = (int)$row["total"];
                $results[$mes][$dia] = $totalInt;
            }
        }

        return $results;
    }

    public function entradas()
    {
        $results = [];

        $sql = "SELECT 
                SUM(t.quantity) as total, MONTH(t.created_at) as 'MONTH', YEAR(t.created_at) as 'YEAR'
            from transactions t
            WHERE 
                t.type_ID = 1 
                AND t.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY MONTH(t.created_at)
            ORDER BY MONTH(t.created_at) ASC;
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            return [];
        }

        while ($row = $result->fetch_assoc()) {
            $month = $row["MONTH"];
            $month = str_pad($month, 2, "0", STR_PAD_LEFT);
            $year = $row["YEAR"];

            $totalInt = (int)$row["total"];
            $results["$year-$month"] = $totalInt;
        }

        return $results;
    }
}
