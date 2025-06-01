<?php
require_once "Models/Model.php";

class Relatorios extends Model
{
    public function saidasDiarias($where = "1", $dataSaida = null)
    {
        $sql = "SELECT 
            p.code, 
            SUM(t.quantity) AS `QUANTIDADE`,
            tt.type AS `TIPO`,
            t.client_name AS `CLIENTE`,
            u.username AS `OPERADOR`,
            COALESCE(s_from.name, s_to.name) AS `ORIGEM`,
            t.observation AS `OBSERVACAO`,
            t.created_at AS `DATA`
        FROM `transactions` t
            INNER JOIN `products` p ON p.ID = t.product_ID
            LEFT JOIN `stocks` s_from ON s_from.ID = t.from_stock_ID AND t.type_ID = 2
            LEFT JOIN `stocks` s_to ON s_to.ID = t.to_stock_ID AND t.type_ID = 4
            INNER JOIN `transaction_types` tt ON tt.ID = t.type_ID
            LEFT JOIN `users` u ON t.operator_ID = u.ID
        WHERE
            t.type_ID IN (2, 4)
            AND $where
        GROUP BY t.ID
        ORDER BY t.created_at ASC;
        ";


        $result = $this->db->query($sql);


        return $result;
    }

    public function estoqueMinimo($page = 1, $limit = 30, $porcentagem = 0.20)
    {
        $offset = ($page - 1) * $limit;

        $sql = "
        SELECT 
            p.code AS 'CODIGO',
            COALESCE(SUM(qs.quantity + qs.quantity_in_reserve), 0) AS 'SALDO',
            (
                SELECT t.quantity
                FROM transactions t
                WHERE t.product_ID = p.ID AND t.type_ID = 1
                ORDER BY t.ID DESC
                LIMIT 1
            ) AS 'ENTRADA',
            CEIL(? * (
                SELECT t.quantity
                FROM transactions t
                WHERE t.product_ID = p.ID AND t.type_ID = 1
                ORDER BY t.ID DESC
                LIMIT 1
            )) AS 'QUANTIDADE_DE_ALERTA'
        FROM products p
        LEFT JOIN quantity_in_stock qs ON qs.product_ID = p.ID
        WHERE p.is_active = 1
        GROUP BY p.ID
        HAVING SALDO < QUANTIDADE_DE_ALERTA AND ENTRADA IS NOT NULL
        LIMIT ? OFFSET ?
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("dii", $porcentagem, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $dados = $result->fetch_all(MYSQLI_ASSOC);

        // Total de itens (sem paginação)
        $sqlCount = "
        SELECT COUNT(*) as total
        FROM (
            SELECT p.ID
            FROM products p
            LEFT JOIN quantity_in_stock qs ON qs.product_ID = p.ID
            WHERE p.is_active = 1
            GROUP BY p.ID
            HAVING 
                COALESCE(SUM(qs.quantity + qs.quantity_in_reserve), 0) < 
                CEIL(? * (
                    SELECT t.quantity
                    FROM transactions t
                    WHERE t.product_ID = p.ID AND t.type_ID = 1
                    ORDER BY t.ID DESC
                    LIMIT 1
                )) 
                AND (
                    SELECT t.quantity
                    FROM transactions t
                    WHERE t.product_ID = p.ID AND t.type_ID = 1
                    ORDER BY t.ID DESC
                    LIMIT 1
                ) IS NOT NULL
        ) AS sub
    ";

        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->bind_param("d", $porcentagem);
        $stmtCount->execute();
        $resultCount = $stmtCount->get_result()->fetch_assoc();

        $total = $resultCount['total'];
        $pageCount = ceil($total / $limit);

        return [
            'dados' => $dados,
            'pageCount' => $pageCount,
            'totalItens' => $total,
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

        $sqlTotalPages = "SELECT COUNT(*) as total
                FROM products p 
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
                p.ID;";

        $stmtTotalPages = $this->db->prepare($sqlTotalPages);
        $stmtTotalPages->bind_param("ss", $dataMovimentacao, $dataMovimentacao);
        $stmtTotalPages->execute();

        $resultTotalPages = $stmtTotalPages->get_result();

        $stmtMain = $this->db->prepare($sqlMain);
        $stmtMain->bind_param("dssii", $rowTotal['total'], $dataMovimentacao, $dataMovimentacao, $limit, $offset);
        $stmtMain->execute();

        // return array of results
        $resultMain = $stmtMain->get_result();

        if ($resultMain->num_rows == 0) {
            $dados = [];
        } else {
            $dados = $resultMain->fetch_all(MYSQLI_ASSOC);
        }
        $resultMain->close();

        $rowTotalPages = $resultTotalPages->fetch_assoc();
        $pageCount = ceil($rowTotalPages['total'] / $limit);

        $resultTotalPages->close();

        return [
            "dados" => $dados,
            "pageCount" => $pageCount
        ];
    }

    public function comparativoDeVendas($meses)
    {
        $results = [];

        foreach ($meses as $key => $mes) {
            $sql = "SELECT 
                    SUM(t.quantity) as total,
                    DAY(t.created_at) AS `DAY`
                FROM transactions t
                WHERE 
                    t.type_ID = 2
                    AND t.created_at >= CONCAT(?, '-01 00:00:00')
                    AND t.created_at < CONCAT(DATE_ADD(CONCAT(?, '-01'), INTERVAL 1 MONTH), ' 00:00:00')
                GROUP BY `DAY`
                ORDER BY `DAY` ASC;
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
