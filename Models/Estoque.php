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

    private function getLojaProductsByStock($limit, $offset, $where, $order)
    {
        $query = "SELECT 
                p.ID, p.code as codigo, p.importer as importadora,
                (
                    SELECT SUM(qis.quantity) + SUM(qis.quantity_in_reserve)
                        FROM `quantity_in_stock` qis
                        WHERE qis.product_ID = p.ID
                ) as saldo_atual
            FROM `products` p
            INNER JOIN `transactions` t ON p.ID = t.product_ID 
            WHERE 
                t.type_ID = 3 AND
                t.to_stock_ID = 2 AND 
                p.is_active = 1 AND $where
            GROUP BY p.ID
            ORDER BY $order
            LIMIT $limit OFFSET $offset";

        $productsResult = $this->db->query($query);
        $products = $productsResult->fetch_all(MYSQLI_ASSOC);

        foreach ($products as &$product) {
            $this->populateLojaProductsDetails($product);
        }

        $sqlCount = "SELECT COUNT(DISTINCT p.ID) as count,
                SUM(t.quantity) as saldo_total
            FROM `products` p
            INNER JOIN `transactions` t ON p.ID = t.product_ID
            WHERE t.to_stock_ID = 2 AND p.is_active = 1 AND $where";

        $pageCount = $this->db->query($sqlCount);
        $pageCount = $pageCount->fetch_assoc();

        return [
            "products" => $products,
            "pageCount" => ceil($pageCount['count'] / $limit),
            "total_count" => $pageCount['count'],
            "saldo_total" => $pageCount['saldo_total']
        ];
    }

    private function populateLojaProductsDetails(&$product)
    {
        $product_ID = $product['ID'];

        $product['quantidade_entrada'] = 0;
        $product['data_de_entrada'] = 0;
        $product['dias_em_estoque'] = 0;

        // Preciso pegar a ultima transacao de entrada do produto   
        $queryLastTransaction = "SELECT 
                        t.ID as transaction_ID, 
                        t.observation as observacao
                    FROM transactions t
                    WHERE t.product_ID = $product_ID AND t.to_stock_ID = 2 AND t.type_ID = 3 ORDER BY t.ID DESC LIMIT 1";

        $lastTransaction = $this->db->query($queryLastTransaction);
        $lastTransaction = $lastTransaction->fetch_assoc();

        $product['observacao'] = $lastTransaction['observacao'];
        $product['transaction_ID'] = $lastTransaction['transaction_ID'];

        // Aqui como eh loja, nao temos que considerar container e sim as transacoes
        $query = "SELECT 
                    SUM(t.quantity) as quantidade_entrada, MAX(t.created_at) as ultima_data_entrada,
                    GREATEST(DATEDIFF(NOW(), MAX(t.created_at)), 1) as dias_em_estoque
                FROM `transactions` t
                WHERE t.product_ID = $product_ID AND t.to_stock_ID = 2 AND t.type_ID = 3
                GROUP BY t.product_ID ORDER BY t.created_at DESC LIMIT 1";

        $entrada = $this->db->query($query);
        $entrada = $entrada->fetch_assoc();

        $product['quantidade_entrada'] = $entrada['quantidade_entrada'];
        $product['data_de_entrada'] = $entrada['ultima_data_entrada'];
        $product['dias_em_estoque'] = $entrada['dias_em_estoque'];

        $index = 1;
        while ($product['quantidade_entrada'] < $product['saldo_atual']) {
            $query = "SELECT 
                        SUM(t.quantity) as quantidade_entrada, MAX(t.created_at) as ultima_data_entrada,
                        GREATEST(DATEDIFF(NOW(), MAX(t.created_at)), 1) as dias_em_estoque
                    FROM `transactions` t
                    WHERE t.product_ID = $product_ID AND t.to_stock_ID = 2 AND t.type_ID = 3
                    GROUP BY t.product_ID ORDER BY t.created_at DESC LIMIT 1 OFFSET $index";

            $entrada = $this->db->query($query);
            $entrada = $entrada->fetch_assoc();
            if (!$entrada) {
                break;
            }

            $product['quantidade_entrada'] += $entrada['quantidade_entrada'];
            $index++;
        }
    }

    private function getGalpaoProductsByStock($limit, $offset, $alert, $where, $order, $giro = false)
    {
        $query = "SELECT 
                p.ID, 
                p.code as codigo, 
                p.importer as importadora,
                (
                    SELECT SUM(qis.quantity) + SUM(qis.quantity_in_reserve)
                        FROM `quantity_in_stock` qis
                        WHERE qis.product_ID = p.ID
                ) as saldo_atual 
            FROM `products` p
            INNER JOIN `transactions` t ON p.ID = t.product_ID 
            WHERE t.to_stock_ID = 1 AND p.is_active = 1 AND $where
            GROUP BY p.ID
            ORDER BY $order
            LIMIT $limit OFFSET $offset";

        $productsResult = $this->db->query($query);
        $products = $productsResult->fetch_all(MYSQLI_ASSOC);

        foreach ($products as &$product) {
            $this->populateGalpaoProductsDetails($product);

            if ($giro) {
                $this->populateGiro($product, $alert);
            }
        }

        $sqlCount = "SELECT COUNT(DISTINCT p.ID) as count,
                SUM(qis.quantity) as saldo_total 
            FROM `products` p
            INNER JOIN `transactions` t ON p.ID = t.product_ID
            INNER JOIN `quantity_in_stock` qis ON p.ID = qis.product_ID
            WHERE t.to_stock_ID = 1 AND p.is_active = 1 AND $where";

        $pageCount = $this->db->query($sqlCount);
        $pageCount = $pageCount->fetch_assoc();

        return [
            "products" => $products,
            "pageCount" => ceil($pageCount['count'] / $limit),
            "total_count" => $pageCount['count'],
            "saldo_total" => $pageCount['saldo_total']
        ];
    }

    private function populateGiro(&$product, $alert)
    {
        if ($product["saldo_atual"] == 0) {
            $product['giro'] = 100;
        } else if ($product['quantidade_entrada'] > 0) {
            $product['giro'] = round(($product['quantidade_entrada'] - $product['saldo_atual']) / $product['quantidade_entrada'] * 100, 2);
        } else {
            $product['giro'] = 0;
        }

        // Quantidade para alerta
        $product['alerta'] = round($product['quantidade_entrada'] * $alert);
        $product["quantidade_para_alerta"] = $product['quantidade_entrada'] - $product['alerta'];
    }

    private function populateGalpaoProductsDetails(&$product)
    {
        $product_ID = $product['ID'];

        $product['quantidade_entrada'] = 0;
        $product['data_de_entrada'] = 0;
        $product['dias_em_estoque'] = 0;
        $product['container_de_origem'] = "";


        // Preciso pegar a ultima transacao de entrada do produto
        $queryLastTransaction = "SELECT 
                    t.ID as transaction_ID, t.observation as observacao
                    FROM transactions t
                    WHERE t.product_ID = $product_ID AND t.to_stock_ID = 1 AND t.type_ID = 1";

        $lastTransaction = $this->db->query($queryLastTransaction);
        $lastTransaction = $lastTransaction->fetch_assoc();

        $product['observacao'] = $lastTransaction['observacao'];
        $product['transaction_ID'] = $lastTransaction['transaction_ID'];


        $query = "SELECT 
                    pic.product_ID, SUM(pic.quantity) as quantidade_entrada, MAX(pic.arrival_date) as ultima_data_entrada,
                    lc.name as container_de_origem, GREATEST(DATEDIFF(NOW(), MAX(pic.arrival_date)), 1) as dias_em_estoque
                FROM `products_in_container` pic
                INNER JOIN lote_container lc ON lc.ID = pic.container_ID
                WHERE pic.in_stock = 1 AND pic.product_ID = $product_ID
                GROUP BY pic.product_ID, lc.name ORDER BY pic.arrival_date DESC LIMIT 1";

        $entrada = $this->db->query($query);
        $entrada = $entrada->fetch_assoc();

        $product['quantidade_entrada'] = $entrada['quantidade_entrada'];
        $product['data_de_entrada'] = $entrada['ultima_data_entrada'];
        $product['dias_em_estoque'] = $entrada['dias_em_estoque'];
        $product['container_de_origem'] = $entrada['container_de_origem'];

        $index = 1;
        while ($product['quantidade_entrada'] < $product['saldo_atual']) {
            $query = "SELECT 
                        pic.product_ID, SUM(pic.quantity) as quantidade_entrada, MAX(pic.arrival_date) as ultima_data_entrada,
                        lc.name as container_de_origem, GREATEST(DATEDIFF(NOW(), MAX(pic.arrival_date)), 1) as dias_em_estoque
                    FROM `products_in_container` pic
                    INNER JOIN lote_container lc ON lc.ID = pic.container_ID
                    WHERE pic.in_stock = 1 AND pic.product_ID = $product_ID
                    GROUP BY pic.product_ID, lc.name ORDER BY pic.arrival_date DESC LIMIT 1 OFFSET $index";

            $entrada = $this->db->query($query);
            $entrada = $entrada->fetch_assoc();
            if (!$entrada) {
                break;
            }

            $product['quantidade_entrada'] += $entrada['quantidade_entrada'];
            $product['container_de_origem'] .= ", " . $entrada['container_de_origem'];
            $index++;
        }
    }

    public function getProductsByStock($stock_ID = null, $page = 1, $limit = 10, $alert = 0.2, $where = "1", $order = "p.created_at DESC ")
    {
        $offset = ($page - 1) * $limit;
        $stock_ID = (int) $stock_ID;

        switch ($stock_ID) {
            case 1:
                return $this->getGalpaoProductsByStock($limit, $offset, $alert, $where, $order, false);
            case 2:
                return $this->getLojaProductsByStock($limit, $offset, $where, $order);
            default:
                return $this->getGalpaoProductsByStock($limit, $offset, $alert, $where, $order, true);
        }
    }
}
