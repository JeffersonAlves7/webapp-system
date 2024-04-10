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

    public function getProductsByStock($stock_ID = null, $page = 1, $limit = 10)
    {
        if ($stock_ID == null) {
            return null;
        }

        // Pegar nome do estoque
        $sql = "SELECT * FROM `stocks` WHERE `ID` = $stock_ID";
        $stock_result = $this->db->query($sql);
        $stock = $stock_result->fetch_assoc();
        $stock_name = $stock["name"];

        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM `products` LIMIT $limit OFFSET $offset";

        $produtos_result = $this->db->query($sql);

        $produtos = array();

        // Pegar estoque dos produtos na tabela quantity_in_stock
        if ($produtos_result->num_rows > 0) {
            while ($produto = $produtos_result->fetch_assoc()) {
                $produto_id = $produto["ID"];
                $sql = "SELECT * FROM `quantity_in_stock` WHERE `product_id` = $produto_id AND `stock_id` = $stock_ID";
                $estoque_result = $this->db->query($sql);
                $estoque = $estoque_result->fetch_assoc();
                $quantidade_atual = $estoque["quantity"] + $estoque["quantity_in_reserve"];

                if ($stock_name == "Galpão") {
                    $sql = "SELECT * FROM `products_in_container` WHERE `product_id` = $produto_id AND `in_stock` = 1 ORDER BY `created_at` DESC LIMIT 1";
                    $entrada_result = $this->db->query($sql);
                    $entrada = $entrada_result->fetch_assoc();
                    $quantidade_entrada = $entrada["quantity"];
                    $data_entrada = $entrada["arrival_date"];

                    // Pegar nome container de origem
                    $sql = "SELECT * FROM `lote_container` WHERE `ID` = " . $entrada["container_ID"];
                    $container_result = $this->db->query($sql);
                    $container = $container_result->fetch_assoc();
                    $produto["container"] = $container["name"];

                    // Se Nesse caso preciso ir pegando o proximo container existente até que a quantidade de entrada seja maior ou igual a quantidade atual
                    $container_offset = 0;
                    while ($quantidade_entrada < $quantidade_atual) {
                        $container_offset = $container_offset + 1;

                        $sql = "SELECT * FROM `products_in_container` WHERE `product_id` = $produto_id AND `in_stock` = 1 ORDER BY `created_at` DESC LIMIT 1 OFFSET $container_offset";
                        $entrada_result = $this->db->query($sql);
                        $entrada = $entrada_result->fetch_assoc();

                        $quantidade_entrada = $entrada["quantity"] + $quantidade_entrada;
                        $produto["entry_quantity"] = $quantidade_entrada;
                        $data_entrada = $entrada["arrival_date"];

                        $sql = "SELECT * FROM `lote_container` WHERE `ID` = " . $entrada["container_ID"];
                        $container_result = $this->db->query($sql);
                        $container = $container_result->fetch_assoc();
                        $produto["container"] .= ', ' . $container["name"];
                    }

                    $giro = 0;
                    if ($quantidade_entrada > 0) {
                        $giro = ($quantidade_entrada - $quantidade_atual) / $quantidade_entrada * 100;
                    }
                    $produto["giro"] = round($giro, 2);
                    $alerta = $quantidade_entrada * 0.2;
                    $produto["alerta"] = $alerta;
                    $produto["entry_quantity"] = $quantidade_entrada;
                    $produto["entry_date"] = $data_entrada;
                    $dias_em_estoque = (strtotime(date("Y-m-d")) - strtotime($data_entrada)) / (60 * 60 * 24) - 1;
                    $produto["days_in_stock"] = $dias_em_estoque;
                }

                $produto["quantity"] = $quantidade_atual;
                array_push($produtos, $produto);
            }
        }

        return $produtos;
    }
}
