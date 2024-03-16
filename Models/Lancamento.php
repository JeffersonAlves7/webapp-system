<?php
require_once "Models/Database.php";

class Lancamento
{
    private $db;

    public function __construct()
    {
        // Não extendi a classe Model por conta das transactions. Nesses casos é melhor abrir uma nova conexão.
        $this->db = new Database();
    }

    public function criarEntrada(
        $product_ID,
        $quantidade,
        $lote_container,
        $stock_ID = null,
        $observacao = null
    ) {
        $this->db->beginTransaction(); // Inicia a transação

        try {
            // Verificando se o lote container já existe no banco de dados
            $sql = "SELECT `ID` FROM `lote_container` WHERE `name` = '$lote_container'";
            $result = $this->db->query($sql);

            // Se ele não existir, crie-o
            if ($result->num_rows == 0) {
                $sql = "INSERT INTO `lote_container` (`name`) VALUES ('$lote_container')";
                $this->db->query($sql);

                // Pegando o ID do container após a criação
                $containerID = $this->db->get_con()->insert_id;

                // Criando registro após a criação do container
                $sql = "INSERT INTO `products_in_container` (`container_ID`, `product_ID`, `quantity`) 
                        VALUES ($containerID, $product_ID, $quantidade)";
                $this->db->query($sql);
            } else {
                $row = $result->fetch_assoc();
                $containerID = $row["ID"];

                $sql = "SELECT * FROM `products_in_container` WHERE 
                        `container_ID` = $containerID AND `product_ID` = $product_ID";
                $result = $this->db->query($sql);

                if ($result->num_rows == 0) {
                    $sql = "INSERT INTO `products_in_container` (`container_ID`, `product_ID`, `quantity`) 
                            VALUES ($containerID, $product_ID, $quantidade)";
                    $this->db->query($sql);
                } else {
                    throw new Exception("O produto já está no container.");
                }
            }

            if (!$stock_ID) {
                $result = $this->db->query("SELECT `ID` FROM stocks WHERE `name` = 'Galpão'");

                if ($result->num_rows == 0) {
                    $this->db->query("INSERT INTO stocks (`name`) VALUES ('Galpão')");
                    $stock_ID = $this->db->get_con()->insert_id;
                } else {
                    $row = $result->fetch_assoc();
                    $stock_ID = $row["ID"];
                }
            }

            $result = $this->db->query(
                "SELECT `ID` FROM `quantity_in_stock` 
                WHERE `product_ID` = $product_ID AND `stock_ID` = $stock_ID"
            );

            if ($result->num_rows == 0) {
                $this->db->query("INSERT INTO quantity_in_stock (`product_ID`, `stock_ID`) VALUES ($product_ID, $stock_ID)");
                $result = $this->db->query(
                    "SELECT `ID` FROM `quantity_in_stock` 
                    WHERE `product_ID` = $product_ID AND `stock_ID` = $stock_ID"
                );
            }

            $row = $result->fetch_assoc();

            $this->db->query(
                "UPDATE `quantity_in_stock` 
                SET `quantity` = `quantity` + $quantidade 
                WHERE `ID` = " . $row["ID"]
            );

            $this->createTransaction($product_ID, null, $stock_ID, "Entrada", $quantidade, $observacao);

            $this->db->commit(); // Confirma a transação
        } catch (Exception $e) {
            $this->db->rollback(); // Reverte a transação em caso de erro
            throw $e; // Lança a exceção para cima
        }
    }

    public function criarSaida(
        $product_ID,
        $quantidade,
        $stock_ID,
        $nome_cliente,
        $observacao
    ) {
        // $this->db->beginTransaction(); // Inicia a transação
        $result = $this->db->query(
            "SELECT * FROM `quantity_in_stock` 
            WHERE `product_ID` = $product_ID AND `stock_ID` = $stock_ID"
        );

        if ($result->num_rows == 0) {
            throw new Exception("Quantidade insuficiente do produto no estoque selecionado");
        }

        $row = $result->fetch_assoc();
        if ($row["quantity"] < (int) $quantidade) {
            throw new Exception("Quantidade insuficiente do produto no estoque selecionado");
        }

        // Alterando quantidade do produto no estoque
        $this->db->query(
            "UPDATE `quantity_in_stock` 
            SET `quantity` = `quantity` - $quantidade 
            WHERE `ID` = " . $row["ID"]
        );

        // Criando Transação do tipo Saída para esse produto e estoque
        $this->createTransaction($product_ID, $stock_ID, null, "Saída", $quantidade, $observacao);
    }

    public function criarTransferencia(
        $product_ID,
        $quantidade,
        $estoque_origem_ID,
        $estoque_destino_ID,
        $localizacao,
        $observacao
    ) {
        // $this->db->beginTransaction(); // Inicia a transação
        $result = $this->db->query(
            "SELECT * FROM `quantity_in_stock` 
            WHERE `product_ID` = $product_ID AND `stock_ID` = $estoque_origem_ID"
        );

        if ($result->num_rows == 0) {
            throw new Exception("Quantidade insuficiente do produto no estoque selecionado");
        }

        $row = $result->fetch_assoc();
        if ($row["quantity"] < (int) $quantidade) {
            throw new Exception("Quantidade insuficiente do produto no estoque selecionado");
        }

        $estoque_origem_quantidade_ID = $row["ID"];

        // Verificar se produto já tem registro no estoque destino
        $result = $this->db->query(
            "SELECT `ID`, `quantity` FROM `quantity_in_stock` 
            WHERE `product_ID` = $product_ID AND `stock_ID` = $estoque_destino_ID"
        );

        // Se não existir, criar registro
        if ($result->num_rows == 0) {
            $this->db->query("INSERT INTO quantity_in_stock (`product_ID`, `stock_ID`, `quantity`) VALUES ($product_ID, $estoque_destino_ID, $quantidade)");
        } else {
            // Se existir, atualizar a quantidade
            $row = $result->fetch_assoc();
            $estoque_destino_quantidade_ID = $row["ID"];
            $nova_quantidade = $row["quantity"] + $quantidade;
            $this->db->query(
                "UPDATE `quantity_in_stock` 
            SET `quantity` = $nova_quantidade
            WHERE `ID` = $estoque_destino_quantidade_ID"
            );
        }

        // Alterando quantidade do produto no estoque
        $this->db->query(
            "UPDATE `quantity_in_stock` 
            SET `quantity` = `quantity` - $quantidade 
            WHERE `ID` = $estoque_origem_quantidade_ID"
        );

        // Criando Transação do tipo Transferência para esse produto e estoque
        $this->createTransaction($product_ID, $estoque_origem_ID, $estoque_destino_ID, "Transferência", $quantidade, $observacao);
    }

    public function criarDevolucao(
        $produto_ID,
        $quantidade,
        $estoque_destino_ID,
        $cliente,
        $observacao
    ) {
        // $this->db->beginTransaction(); // Inicia a transação
        $result = $this->db->query(
            "SELECT * FROM `quantity_in_stock` 
            WHERE `product_ID` = $produto_ID AND `stock_ID` = $estoque_destino_ID"
        );

        // Se não existir, criar registro
        if ($result->num_rows == 0) {
            $this->db->query("INSERT INTO quantity_in_stock (`product_ID`, `stock_ID`, `quantity`) VALUES ($produto_ID, $estoque_destino_ID, $quantidade)");
        } else {
            // Se existir, atualizar a quantidade
            $row = $result->fetch_assoc();
            $estoque_destino_quantidade_ID = $row["ID"];
            $nova_quantidade = $row["quantity"] + $quantidade;
            $this->db->query(
                "UPDATE `quantity_in_stock` 
            SET `quantity` = $nova_quantidade
            WHERE `ID` = $estoque_destino_quantidade_ID"
            );
        }

        $this->createTransaction($produto_ID, null, $estoque_destino_ID, "Devolução", $quantidade, $observacao);
    }

    private function createTransaction($product_ID, $from_stock_ID, $to_stock_ID, $type_ID, $quantity, $observation = null)
    {
        // Check if the transaction type exists, if not, create it
        $transaction_type_ID = $this->getTransactionTypeID($type_ID);

        $from_stock = $from_stock_ID !== null ? $from_stock_ID : 'NULL';
        $to_stock = $to_stock_ID !== null ? $to_stock_ID : 'NULL';

        // Insert the transaction
        $sql = "INSERT INTO `transactions` (`product_ID`, `from_stock_ID`, `to_stock_ID`, `type_ID`, `quantity`, `observation`) 
            VALUES ($product_ID, $from_stock, $to_stock, $transaction_type_ID, $quantity, '" . $this->db->escapeString($observation) . "')";

        return $this->db->query($sql);
    }


    private function getTransactionTypeID($transaction_type)
    {
        $transaction_type = $this->db->escapeString($transaction_type);
        $result = $this->db->query("SELECT `ID` FROM `transaction_types` WHERE `type` = '$transaction_type'");

        if ($result->num_rows > 0) {
            return $result->fetch_assoc()["ID"];
        } else {
            // If the transaction type does not exist, create it
            $this->db->query("INSERT INTO `transaction_types` (`type`) VALUES ('$transaction_type')");
            return $this->db->get_con()->insert_id;
        }
    }
}
