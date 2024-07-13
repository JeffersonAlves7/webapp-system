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

    public function getProductsByStock($stock_ID = null, $page = 1, $limit = 10, $alert = 0.2, $where = "1", $order = "p.created_at DESC ")
    {
        $offset = ($page - 1) * $limit;
        $stock_sql = $stock_ID ? "AND qis.stock_ID = ?" : "";

        // Consulta principal para buscar produtos
        $sql = "SELECT
            p.ID,
            p.code as codigo,
            p.importer as importadora,
            (SUM(qis.quantity) + SUM(qis.quantity_in_reserve)) as saldo_atual
            FROM `products` p
            INNER JOIN `quantity_in_stock` qis ON p.ID = qis.product_ID
            WHERE (
                SELECT COUNT(*) FROM `transactions` t2 WHERE t2.product_ID = p.ID  LIMIT 1
            ) > 0 AND $where $stock_sql
            GROUP BY p.ID
            ORDER BY $order
            LIMIT ? OFFSET ?";

        // Preparar a consulta SQL
        $stmt = $this->db->prepare($sql);
        $params = [$limit, $offset];
        if ($stock_ID) {
            array_unshift($params, $stock_ID);
        }

        $stmt->bind_param(str_repeat('i', count($params)), ...$params);

        // Executar a consulta
        $stmt->execute();
        $produtos_result = $stmt->get_result();
        $produtos = $produtos_result->fetch_all(MYSQLI_ASSOC);

        // Obter contagem total de páginas
        $total_count_sql = "SELECT COUNT(DISTINCT p.ID) as count,
                            SUM(qis.quantity) as saldo_total
                            FROM `products` p
                            INNER JOIN `quantity_in_stock` qis ON p.ID = qis.product_ID
                            WHERE 
                            (
                                SELECT COUNT(*) FROM `transactions` t2 WHERE t2.product_ID = p.ID  LIMIT 1
                            ) > 0 AND $where $stock_sql";

        $stmt = $this->db->prepare($total_count_sql);
        if ($stock_ID) {
            $stmt->bind_param('i', $stock_ID);
        }

        $stmt->execute();
        $total_count_result = $stmt->get_result();
        $total_count_result = $total_count_result->fetch_assoc();
        $total_count = $total_count_result['count'];
        $saldo_total = $total_count_result['saldo_total'];
        $pageCount = ceil($total_count / $limit);

        // Buscar informações adicionais para produtos fora do loop principal
        $product_ids = array_column($produtos, 'ID');
        if (!empty($product_ids)) {
            $product_ids_placeholder = implode(',', array_fill(0, count($product_ids), '?'));

            $entrada_sql = ($stock_ID == null || $stock_ID == 1) ?
                "SELECT
                    pic.product_ID,
                    SUM(pic.quantity) as quantidade_entrada,
                    MAX(pic.arrival_date) as ultima_data_entrada,
                    lc.name as container_de_origem,
                    GREATEST(DATEDIFF(NOW(), MAX(pic.arrival_date)), 1) as dias_em_estoque
                FROM `products_in_container` pic
                INNER JOIN lote_container lc ON lc.ID = pic.container_ID
                WHERE pic.in_stock = 1 AND pic.product_ID IN ($product_ids_placeholder)
                GROUP BY pic.product_ID, lc.name" :
                "SELECT
                    t.product_ID,
                    SUM(t.quantity) as quantidade_entrada,
                    MAX(t.created_at) as ultima_data_entrada,
                    s.name as estoque_de_origem,
                    DATEDIFF(NOW(), MAX(t.created_at)) as dias_em_estoque
                FROM `transactions` t
                INNER JOIN transaction_types tt ON tt.ID = t.type_ID
                INNER JOIN stocks s ON s.ID = t.from_stock_ID
                WHERE tt.type = 'Transferência' AND t.product_ID IN ($product_ids_placeholder)
                GROUP BY t.product_ID, s.name";

            $stmt = $this->db->prepare($entrada_sql);
            $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
            $stmt->execute();
            $entrada_result = $stmt->get_result();

            $entradas = [];
            while ($entrada = $entrada_result->fetch_assoc()) {
                $entradas[$entrada['product_ID']] = $entrada;
            }

            // Processar produtos e adicionar informações adicionais
            foreach ($produtos as &$produto) {
                if (isset($entradas[$produto['ID']])) {
                    $entrada = $entradas[$produto['ID']];
                    $produto['quantidade_entrada'] = $entrada['quantidade_entrada'];
                    $produto['data_de_entrada'] = $entrada['ultima_data_entrada'];
                    $produto['dias_em_estoque'] = $entrada['dias_em_estoque'];
                    $produto['container_de_origem'] = $entrada['container_de_origem'] ?? '';
                    $produto['estoque_de_origem'] = $entrada['estoque_de_origem'] ?? '';

                    if ($entrada['quantidade_entrada'] > 0) {
                        $produto['giro'] = round(($entrada['quantidade_entrada'] - $produto['saldo_atual']) / $entrada['quantidade_entrada'] * 100, 2);
                        $produto['quantidade_para_alerta'] = ceil($entrada['quantidade_entrada'] * $alert);
                    } else {
                        $produto['giro'] = 0;
                        $produto['quantidade_para_alerta'] = 0;
                    }
                } else {
                    $produto['quantidade_entrada'] = 0;
                    $produto['giro'] = 0;
                    $produto['quantidade_para_alerta'] = 0;
                }
            }
        }

        return [
            "products" => $produtos,
            "pageCount" => $pageCount,
            "total_count" => $total_count,
            "saldo_total" => $saldo_total
        ];
    }
}
