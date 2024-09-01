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

        $saldoTotalQuery = "SELECT SUM(qis.quantity + qis.quantity_in_reserve) as saldo_total
            FROM quantity_in_stock qis
            WHERE qis.stock_ID = 2";

        $saldoTotal = $this->db->query($saldoTotalQuery);
        $saldoTotal = $saldoTotal->fetch_assoc();

        $sqlCount = "SELECT COUNT(DISTINCT p.ID) as count
            FROM `products` p
            INNER JOIN `transactions` t ON p.ID = t.product_ID
            WHERE t.to_stock_ID = 2 AND p.is_active = 1 AND $where";

        $pageCount = $this->db->query($sqlCount);
        $pageCount = $pageCount->fetch_assoc();

        return [
            "products" => $products,
            "pageCount" => ceil($pageCount['count'] / $limit),
            "total_count" => $pageCount['count'],
            "saldo_total" => $saldoTotal['saldo_total']
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
        if ($giro) {
            $query = "SELECT 
                p.ID, 
                p.code as codigo, 
                p.importer as importadora,
                COALESCE(
                    (
                        SELECT SUM(qis.quantity) + SUM(qis.quantity_in_reserve)
                            FROM `quantity_in_stock` qis
                            WHERE qis.product_ID = p.ID 
                    ), 0
                ) as saldo_atual,
                COALESCE(qis.last_entries, '') AS 'container_ids',
                COALESCE(qis.entry_quantity, 0) AS 'quantidade_entrada',
                CASE
                    WHEN COALESCE(
                        (
                            SELECT SUM(qis.quantity) + SUM(qis.quantity_in_reserve)
                                FROM `quantity_in_stock` qis
                                WHERE qis.product_ID = p.ID 
                        ), 0) = 0 THEN 100
                    WHEN COALESCE(qis.entry_quantity, 0) > 0 THEN 
                        ROUND(
                            (
                                (
                                    COALESCE(qis.entry_quantity, 0) - COALESCE((
                                        SELECT SUM(qis.quantity) + SUM(qis.quantity_in_reserve)
                                            FROM `quantity_in_stock` qis
                                            WHERE qis.product_ID = p.ID 
                                    ), 0)
                                ) / COALESCE(qis.entry_quantity, 0)
                            ) * 100, 2)
                    ELSE 0
                END AS giro,
                FLOOR(ROUND(COALESCE(qis.entry_quantity, 0) * $alert, 2)) AS quantidade_para_alerta,
                null as observacao
            FROM `products` p
            INNER JOIN `transactions` t ON p.ID = t.product_ID  AND t.to_stock_ID = 1
            INNER JOIN `quantity_in_stock` qis ON p.ID = qis.product_ID AND qis.stock_ID = 1
            WHERE p.is_active = 1 AND $where
            GROUP BY p.ID
            ORDER BY $order
            LIMIT $limit OFFSET $offset";
        } else {
            $query = "SELECT 
                p.ID, 
                p.code as codigo, 
                p.importer as importadora,
                COALESCE(
                    (
                        SELECT SUM(qis.quantity) + SUM(qis.quantity_in_reserve)
                            FROM `quantity_in_stock` qis
                            WHERE qis.product_ID = p.ID 
                            AND qis.stock_ID = 1
                    ), 0
                ) as saldo_atual,
                COALESCE(qis.last_entries, '') AS 'container_ids',
                COALESCE(qis.entry_quantity, 0) AS 'quantidade_entrada'
            FROM `products` p
            INNER JOIN `transactions` t ON p.ID = t.product_ID  AND t.to_stock_ID = 1
            INNER JOIN `quantity_in_stock` qis ON p.ID = qis.product_ID AND qis.stock_ID = 1
            WHERE p.is_active = 1 AND $where
            GROUP BY p.ID
            ORDER BY $order
            LIMIT $limit OFFSET $offset";
        }

        $productsResult = $this->db->query($query);
        $products = $productsResult->fetch_all(MYSQLI_ASSOC);

        foreach ($products as &$product) {
            $containers_IDs_str = $product["container_ids"];
            $containers_IDs = explode(",", $containers_IDs_str);

            // Data de entrada do último container
            $last_container = end($containers_IDs); // Corrigido para pegar o último item
            $last_container_query = "SELECT arrival_date FROM `products_in_container` WHERE ID = ?";

            if ($stmt = $this->db->prepare($last_container_query)) {
                $stmt->bind_param("i", $last_container);
                $stmt->execute();
                $lastContainerResult = $stmt->get_result();
                $containerData = $lastContainerResult->fetch_assoc();
                $arrivalDate = $containerData["arrival_date"];

                // Calcular quantos dias o produto está em estoque
                $arrivalDateObj = new DateTime($arrivalDate);
                $currentDateObj = new DateTime();
                $interval = $arrivalDateObj->diff($currentDateObj);
                $daysInStock = max($interval->days, 1); // Garantir que o mínimo seja 1 dia

                $product["data_de_entrada"] = $arrivalDate;
                $product["dias_em_estoque"] = $daysInStock;
            }

            // Nomes dos containers
            $container_ids_placeholder = implode(',', array_fill(0, count($containers_IDs), '?'));
            $names_query = "SELECT lc.name FROM `products_in_container` pc
            INNER JOIN `lote_container` lc ON pc.container_ID = lc.ID
            WHERE pc.ID IN ($container_ids_placeholder)";

            if ($stmt = $this->db->prepare($names_query)) {
                $stmt->bind_param(str_repeat('i', count($containers_IDs)), ...$containers_IDs);
                $stmt->execute();
                $namesResult = $stmt->get_result();
                $containerNames = [];

                while ($row = $namesResult->fetch_assoc()) {
                    $containerNames[] = $row["name"];
                }

                $product["container_de_origem"] = implode(", ", $containerNames);
            }

            // Preciso pegar a ultima transacao de entrada do produto
            $product_ID = $product["ID"];

            $queryLastTransaction = "SELECT 
            t.ID as transaction_ID, t.observation as observacao
            FROM transactions t
            WHERE t.product_ID = $product_ID AND t.to_stock_ID = 1 AND t.type_ID = 1";

            $lastTransaction = $this->db->query($queryLastTransaction);
            $lastTransaction = $lastTransaction->fetch_assoc();

            $product['observacao'] = $lastTransaction['observacao'];
        }

        if ($giro) {
            $saldoTotalQuery = "SELECT SUM(qis.quantity + qis.quantity_in_reserve) as saldo_total
            FROM quantity_in_stock qis";
        } else {
            $saldoTotalQuery = "SELECT SUM(qis.quantity + qis.quantity_in_reserve) as saldo_total
            FROM quantity_in_stock qis
            WHERE qis.stock_ID = 1";
        }

        $saldoTotal = $this->db->query($saldoTotalQuery);
        $saldoTotal = $saldoTotal->fetch_assoc();

        $sqlCount = "SELECT COUNT(DISTINCT p.ID) as count 
            FROM `products` p
            INNER JOIN `transactions` t ON p.ID = t.product_ID
            WHERE t.to_stock_ID = 1 AND p.is_active = 1 AND $where";

        $pageCount = $this->db->query($sqlCount);
        $pageCount = $pageCount->fetch_assoc();

        return [
            "products" => $products,
            "pageCount" => ceil($pageCount['count'] / $limit),
            "total_count" => $pageCount['count'],
            "saldo_total" => $saldoTotal['saldo_total']
        ];
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

    public function getAllProductsStockWithoutAlert($stock_ID = null, $where = "1")
    {
        $stock_ID = (int) $stock_ID;

        // If stock_ID is equal to 0 or it's not set, then we want to get all products from all stocks
        $query = "SELECT 
                p.*,
                (SELECT qs.quantity + qs.quantity_in_reserve FROM quantity_in_stock qs WHERE qs.product_ID = p.ID AND qs.stock_ID = 1) as `quantity_galpao`,
                (SELECT qs.quantity + qs.quantity_in_reserve FROM quantity_in_stock qs WHERE qs.product_ID = p.ID AND qs.stock_ID = 2) as `quantity_loja`
            FROM products p
            WHERE $where
            GROUP BY p.ID
            ORDER BY p.ID DESC";

        $productsResult = $this->db->query($query);
        $products = $productsResult->fetch_all(MYSQLI_ASSOC);

        return $products;
    }
}
