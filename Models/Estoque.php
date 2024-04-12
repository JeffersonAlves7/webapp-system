<?php
require_once "Models/Model.php";

class Estoque extends Model
{
    private $MAX_LIMIT = 50;

    public function getAll()
    {
        $sql = "SELECT * FROM `stocks`";
        return $this->db->query($sql);
    }

    public function create($name)
    {
        $sql = "INSERT INTO `stocks` (`name`) VALUES ('$name')";

        return $this->db->query($sql);
    }

    public function getProductsByStock($stock_ID = null, $page = 1, $limit = 10, $alert = 0.2, $where = "1")
    {
        $offset = ($page - 1) * $limit;

        $stock_sql = $stock_ID ? "AND qis.stock_ID = ? " : "";

        $sql = "SELECT
            p.ID,
            p.code as codigo,
            p.importer as importadora,
            (
                SUM(qis.quantity) + SUM(qis.quantity_in_reserve)
            ) as saldo_atual
            FROM
                `products` p
                JOIN `quantity_in_stock` qis ON p.ID = qis.product_ID
            WHERE $where $stock_sql 
            GROUP BY p.ID 
            ORDER BY p.created_at 
            DESC LIMIT ? OFFSET ?";

        // Prepare the SQL statement
        $stmt = $this->db->prepare($sql);

        // Bind the parameters
        $params = [$limit, $offset];
        if ($stock_ID) {
            array_unshift($params, $stock_ID);
        }
        $stmt->bind_param(str_repeat('i', count($params)), ...$params);

        // Execute the statement
        $stmt->execute();

        // Get the result
        $produtos_result = $stmt->get_result();

        $produtos = array();
        $pageCount = ceil($this->db->query("SELECT COUNT(*) as count FROM `products`")->fetch_assoc()["count"] / $limit);

        // Faca a mesma otimizacao para o loop while adiante
        while ($produto = $produtos_result->fetch_assoc()) {
            $entrada_offset = 0;

            while (
                !isset($produto["quantidade_entrada"]) ||
                $produto["quantidade_entrada"] < $produto["saldo_atual"]
            ) {
                if (!$stock_ID || $stock_ID == 1) {
                    $sql_entrada = "SELECT
                        pic.quantity as quantidade_entrada, 
                        pic.arrival_date as data_de_entrada, 
                        lc.name as container_de_origem, pic.product_ID, 
                        GREATEST(DATEDIFF(NOW(), pic.arrival_date), 1) as dias_em_estoque
                    FROM
                        `products_in_container` pic
                        INNER JOIN lote_container lc on lc.ID = pic.container_ID
                    WHERE
                        pic.in_stock = 1
                        AND pic.product_ID =  $produto[ID]
                    ORDER BY pic.arrival_date DESC
                    LIMIT 1 OFFSET $entrada_offset;";
                } else {
                    $sql_entrada = "SELECT
                        t.quantity as quantidade_entrada, 
                        DATE(t.created_at) as data_de_entrada,
                        s.name as estoque_de_origem, 
                        t.product_ID, 
                        DATEDIFF(NOW(), t.created_at) as dias_em_estoque 
                    FROM
                        `transactions` t
                        INNER JOIN transaction_types tt on tt.ID = t.type_ID
                        INNER JOIN stocks s on s.ID = t.from_stock_ID
                    WHERE
                        tt.type = 'Transferência'
                        AND t.product_ID = $produto[ID]
                    ORDER BY t.created_at DESC
                    LIMIT 1 OFFSET $entrada_offset;";
                }

                $entrada_result = $this->db->query($sql_entrada);

                $entrada = $entrada_result->fetch_assoc();

                if (!$entrada) {
                    break;
                }

                foreach ($entrada as $key => $value) {
                    if (isset($produto[$key])) {
                        if ($key == "container_de_origem" || $key == "estoque_de_origem") {
                            $produto[$key] .= " | " . $value;
                        } else if ($key == "quantidade_entrada") {
                            $produto[$key] += $value;
                        }
                    } else {
                        $produto[$key] = $value;
                    }
                }
            }

            $produto["giro"] = 0;
            if ($produto["quantidade_entrada"] > 0) {
                $produto["giro"] = round(($produto["quantidade_entrada"] - $produto["saldo_atual"]) / $produto["quantidade_entrada"] * 100, 2);
            }

            $produto["quantidade_para_alerta"] = $produto["quantidade_entrada"] * $alert;

            $produtos[] = $produto;
        }

        return array(
            "products" => $produtos,
            "pageCount" => $pageCount
        );
    }
}
