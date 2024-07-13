<?php
require_once "Models/Database.php";

class Lancamento
{
    private $db;

    public function __construct($db = null)
    {
        // Não extendi a classe Model por conta das transactions. Nesses casos é melhor abrir uma nova conexão.
        if ($db) {
            $this->db = $db;
        } else {
            $this->db = new Database();
        }
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
            } else {
                $row = $result->fetch_assoc();
                $containerID = $row["ID"];

                $sql = "SELECT * FROM `products_in_container` WHERE 
                        `container_ID` = $containerID AND `product_ID` = $product_ID";
                $result = $this->db->query($sql);

                if ($result->num_rows != 0) {
                    throw new Exception("O produto já está no container.");
                }
            }

            // Criando registro após a criação do container
            $sql = "INSERT INTO `products_in_container` (`container_ID`, `product_ID`, `quantity`, `quantity_expected`, `arrival_date`) 
                    VALUES ($containerID, $product_ID, $quantidade, $quantidade, NOW())";
            $this->db->query($sql);

            if (!$stock_ID) {
                $stock_ID = 1;
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

            self::createTransaction($this->db, $product_ID, null, $stock_ID, "Entrada", $quantidade, observation: $observacao);

            $this->db->commit(); // Confirma a transação
        } catch (Exception $e) {
            $this->db->rollback(); // Reverte a transação em caso de erro
            throw $e; // Lança a exceção para cima
        }
    }

    public function criarEntradaEmMassa(
        $products
    ) {
        $this->db->beginTransaction(); // Inicia a transação

        try {
            foreach ($products as $product) {
                $product_ID = $product['product_ID'];
                $quantity = $product['quantity'];
                $lote_container = $product['lote_container'];
                $observation = $product['observation'];
                $stock_ID = 1; // Fixado no galpão

                // Verificando se o lote container já existe no banco de dados
                $sql = "SELECT `ID` FROM `lote_container` WHERE `name` = '$lote_container'";
                $result = $this->db->query($sql);

                // Se ele não existir, crie-o
                if ($result->num_rows == 0) {
                    $sql = "INSERT INTO `lote_container` (`name`) VALUES ('$lote_container')";
                    $this->db->query($sql);

                    // Pegando o ID do container após a criação
                    $containerID = $this->db->get_con()->insert_id;
                } else {
                    $row = $result->fetch_assoc();
                    $containerID = $row["ID"];

                    $sql = "SELECT * FROM `products_in_container` WHERE 
                            `container_ID` = $containerID AND `product_ID` = $product_ID";
                    $result = $this->db->query($sql);

                    if ($result->num_rows != 0) {
                        throw new Exception("O produto já está no container.");
                    }
                }

                // Criando registro após a criação do container
                $sql = "INSERT INTO `products_in_container` (`container_ID`, `product_ID`, `quantity`, `quantity_expected`, `arrival_date`) 
                        VALUES ($containerID, $product_ID, $quantity, $quantity, NOW())";
                $this->db->query($sql);

                if (!$stock_ID) {
                    $stock_ID = 1;
                }

                $result = $this->db->query(
                    " SELECT `ID` FROM `quantity_in_stock` 
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
                    SET `quantity` = `quantity` + $quantity 
                    WHERE `ID` = " . $row["ID"]
                );

                self::createTransaction($this->db, $product_ID, null, $stock_ID, "Entrada", $quantity, observation: $observation);
            }

            $this->db->commit(); // Confirma a transação
        } catch (Exception $e) {
            $this->db->rollback(); // Reverte a transação em caso de erro
            throw $e;
        }
    }


    public function criarReserva(
        $product_ID,
        $stock_ID,
        $quantity,
        $client_name,
        $rescue_date,
        $observation
    ) {
        // Verificar se tem quantidade suficiente do produto para reservar
        $results = $this->db->query("SELECT * FROM quantity_in_stock WHERE 
            stock_ID = $stock_ID 
            AND product_ID = $product_ID 
            AND quantity >= $quantity");

        if ($results->num_rows > 0) {
            //cria registro na tabela reserva e altera quantidade na tabela stock
            $sql = "INSERT into `reserves` (product_ID, stock_ID, quantity, client_name, rescue_date) VALUES ($product_ID,
                $stock_ID,
                $quantity,
                '$client_name',
                '$rescue_date')";

            $this->db->query($sql);

            $this->db->query("UPDATE quantity_in_stock 
                SET quantity_in_reserve = quantity_in_reserve + $quantity, quantity = quantity - $quantity 
                WHERE stock_ID = $stock_ID AND product_ID = $product_ID");

            // $this->createTransaction($product_ID, $stock_ID, null, "Reserva", $quantity, $observation);
        } else {
            throw new Exception("O produto não possuí quantidade suficiente para reserva em estoque!");
        }
    }

    public function criarReservaEmMassa(
        $products
    ) {
        $this->db->beginTransaction(); // Inicia a transação

        try {
            foreach ($products as $product) {
                $product_ID = $product['product_ID'];
                $stock_ID = $product['stock'];
                $quantity = $product['quantity'];
                $client_name = $product['client'];
                $rescue_date = $product['rescue_date'];
                $observation = $product['observation'];

                // Verificar se tem quantidade suficiente do produto para reservar
                $results = $this->db->query("SELECT * FROM quantity_in_stock WHERE 
                    stock_ID = $stock_ID 
                    AND product_ID = $product_ID 
                    AND quantity >= $quantity");

                if ($results->num_rows > 0) {
                    //cria registro na tabela reserva e altera quantidade na tabela stock
                    $sql = "INSERT into `reserves` (product_ID, stock_ID, quantity, client_name, rescue_date) VALUES ($product_ID,
                        $stock_ID,
                        $quantity,
                        '$client_name',
                        '$rescue_date')";

                    $this->db->query($sql);

                    $this->db->query("UPDATE quantity_in_stock 
                        SET quantity_in_reserve = quantity_in_reserve + $quantity, quantity = quantity - $quantity 
                        WHERE stock_ID = $stock_ID AND product_ID = $product_ID");

                    // $this->createTransaction($product_ID, $stock_ID, null, "Reserva", $quantity, $observation);
                } else {
                    throw new Exception("O produto não possuí quantidade suficiente para reserva em estoque!");
                }
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
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
        self::createTransaction($this->db, $product_ID, $stock_ID, null, "Saída", $quantidade, $nome_cliente, $observacao);
    }

    public function criarSaidaEmMassa(
        $products
    ) {
        $this->db->beginTransaction(); // Inicia a transação

        try {
            foreach ($products as $product) {
                $product_ID = $product['product_ID'];
                $quantidade = $product['quantity'];
                $stock_ID = $product['stock'];
                $nome_cliente = $product['client'];
                $observacao = $product['observation'];

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
                self::createTransaction($this->db, $product_ID, $stock_ID, null, "Saída", $quantidade, $nome_cliente, $observacao);
            }

            $this->db->commit(); // Confirma a transação
        } catch (Exception $e) {
            $this->db->rollback(); // Reverte a transação em caso de erro
            throw $e; // Lança a exceção para cima
        }
    }

    public function criarTransferencia(
        $product_ID,
        $quantidade,
        $estoque_origem_ID,
        $estoque_destino_ID,
        $localizacao,
        $observacao
    ) {
        $this->db->query("INSERT INTO `transferences` (`product_ID`, `quantity`, `from_stock_ID`, `to_stock_ID`, `location`, `observation`) 
            VALUES ($product_ID, $quantidade, $estoque_origem_ID, $estoque_destino_ID, '$localizacao', '$observacao')");
    }

    public function criarTransferenciaEmMassa(
        $products
    ) {
        foreach ($products as $product) {
            $product_ID = $product['product_ID'];
            $quantity = $product['quantity'];
            $from_stock_ID = $product['from_stock'];
            $to_stock_ID = $product['to_stock'];
            $location = $product['location'];
            $observation = $product['observation'];

            $query = "INSERT INTO `transferences` (`product_ID`, `quantity`, `from_stock_ID`, `to_stock_ID`, `location`, `observation`) 
            VALUES ($product_ID, $quantity, $from_stock_ID, $to_stock_ID, '$location', '$observation')";

            echo $query;

            $this->db->query($query);
        }
    }

    public function confirmarTransferencias(
        $transference_IDs
    ) {
        $this->db->beginTransaction();
        try {
            $transference_IDs_str = implode(",", array_map(function ($transference) {
                return $transference->id;
            }, $transference_IDs));

            $result = $this->db->query("SELECT `transferences`.*, `products`.`code`, `from_stock`.`name` as `from_stock_name` FROM `transferences` 
            INNER JOIN `products` ON `transferences`.`product_ID` = `products`.`ID`
            LEFT JOIN `stocks` AS `from_stock` ON `transferences`.`from_stock_ID` = `from_stock`.`ID`
            WHERE `transferences`.`ID` IN ($transference_IDs_str)");

            if ($result->num_rows != count($transference_IDs)) {
                throw new Exception("Algum dos IDs de transferência não é válido");
            }

            while ($transference = $result->fetch_assoc()) {
                $product_code = $transference["code"];
                $product_ID = $transference["product_ID"];
                $quantity = $transference_IDs[array_search($transference["ID"], array_column($transference_IDs, 'id'))]->quantity;
                $from_stock_ID = $transference["from_stock_ID"];
                $to_stock_ID = $transference["to_stock_ID"];
                $from_stock_name = $transference["from_stock_name"];
                // $location = $transference["location"];
                $observation = $transference["observation"];

                $result = $this->db->query(
                    "SELECT * FROM `quantity_in_stock` 
                    WHERE `product_ID` = $product_ID AND `stock_ID` = $from_stock_ID"
                );

                $row = $result->fetch_assoc();

                if ($row == null || $row["quantity"] < (int) $quantity) {
                    throw new Exception("Quantidade insuficiente do produto de ID '$product_ID' e Código '$product_code' no estoque '$from_stock_name'");
                }

                $estoque_origem_quantidade_ID = $row["ID"];

                // Verificar se produto já tem registro no estoque destino
                $result = $this->db->query(
                    "SELECT `ID`, `quantity` FROM `quantity_in_stock` 
                    WHERE `product_ID` = $product_ID AND `stock_ID` = $to_stock_ID"
                );

                // Se não existir, criar registro
                if ($result->num_rows == 0) {
                    $this->db->query("INSERT INTO quantity_in_stock (`product_ID`, `stock_ID`, `quantity`) VALUES ($product_ID, $to_stock_ID, $quantity)");
                } else {
                    // Se existir, atualizar a quantidade
                    $row = $result->fetch_assoc();
                    $estoque_destino_quantidade_ID = $row["ID"];
                    $nova_quantidade = $row["quantity"] + $quantity;
                    $this->db->query(
                        "UPDATE `quantity_in_stock` 
                        SET `quantity` = $nova_quantidade
                        WHERE `ID` = $estoque_destino_quantidade_ID"
                    );
                }

                // Alterando quantidade do produto no estoque origem
                $this->db->query(
                    "UPDATE `quantity_in_stock` 
                    SET `quantity` = `quantity` - $quantity 
                    WHERE `ID` = $estoque_origem_quantidade_ID"
                );

                self::createTransaction($this->db, $product_ID, $from_stock_ID, $to_stock_ID, "Transferência", $quantity, observation: $observation);
                $this->db->query("UPDATE `transferences` SET `confirmed` = 1 WHERE `ID` = " . $transference["ID"]);
            }
        } catch (Exception $e) {
            $this->db->rollback(); // Reverte a transação em caso de erro
            throw $e; // Lança a exceção para cima
        }

        $this->db->commit(); // Confirma a transação
    }

    public function cancelarTransferencias($transference_IDs)
    {
        $transference_IDs_str = implode(",", $transference_IDs);

        $result = $this->db->query("SELECT * FROM `transferences` WHERE `ID` IN ($transference_IDs_str)");

        if ($result->num_rows != count($transference_IDs)) {
            throw new Exception("Algum dos IDs de transferência não é válido");
        }

        // Cancelar cada transferencia - remover da tabela
        $this->db->query("DELETE FROM `transferences` WHERE `ID` IN ($transference_IDs_str)");
    }

    public function getTransferenciasPendentes($where = "1")
    {
        $query = "SELECT `transferences`.*, `products`.`code`, `products`.`importer`, `products`.`description`, `from_stock`.`name` as `from_stock_name`, `to_stock`.`name` as `to_stock_name` FROM `transferences` 
        INNER JOIN `products` ON `transferences`.`product_ID` = `products`.`ID`
        LEFT JOIN `stocks` AS `from_stock` ON `transferences`.`from_stock_ID` = `from_stock`.`ID`
        LEFT JOIN `stocks` AS `to_stock` ON `transferences`.`to_stock_ID` = `to_stock`.`ID`
        WHERE `confirmed` = 0 AND $where";
        $result = $this->db->query($query);

        $transferences = [];
        while ($row = $result->fetch_assoc()) {
            $transferences[] = $row;
        }

        return $transferences;
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

        self::createTransaction($this->db, $produto_ID, null, $estoque_destino_ID, "Devolução", $quantidade, $cliente, $observacao);
    }

    public function criarDevolucaoEmMassa(
        $products
    ) {
        $this->db->beginTransaction();

        try {
            foreach ($products as $product) {
                $product_ID = $product['product_ID'];
                $quantity = $product['quantity'];
                $stock_ID = $product['stock'];
                $client = $product['client'];
                $observation = $product['observation'];

                $result = $this->db->query(
                    "SELECT * FROM `quantity_in_stock` 
                    WHERE `product_ID` = $product_ID AND `stock_ID` = $stock_ID"
                );

                // Se não existir, criar registro
                if ($result->num_rows == 0) {
                    $this->db->query("INSERT INTO quantity_in_stock (`product_ID`, `stock_ID`, `quantity`) VALUES ($product_ID, $stock_ID, $quantity)");
                } else {
                    // Se existir, atualizar a quantidade
                    $row = $result->fetch_assoc();
                    $estoque_destino_quantidade_ID = $row["ID"];
                    $nova_quantidade = $row["quantity"] + $quantity;
                    $this->db->query(
                        "UPDATE `quantity_in_stock` 
                    SET `quantity` = $nova_quantidade
                    WHERE `ID` = $estoque_destino_quantidade_ID"
                    );
                }

                self::createTransaction($this->db, $product_ID, null, $stock_ID, "Devolução", $quantity, $client, $observation);
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public static function createTransaction($db, $product_ID, $from_stock_ID, $to_stock_ID, $type_ID, $quantity, $client_name = null, $observation = null)
    {
        // Check if the transaction type exists, if not, create it
        $transaction_type_ID = self::getTransactionTypeID($db, $type_ID);

        $from_stock = $from_stock_ID !== null ? $from_stock_ID : 'NULL';
        $to_stock = $to_stock_ID !== null ? $to_stock_ID : 'NULL';

        // Insert the transaction
        $sql = "INSERT INTO `transactions` (`product_ID`, `from_stock_ID`, `to_stock_ID`, `type_ID`, `quantity`, `client_name`, `observation`) 
        VALUES ($product_ID, $from_stock, $to_stock, $transaction_type_ID, $quantity, '" . ($client_name ? $db->escapeString($client_name) : '') .  "', '" . $db->escapeString($observation) . "')";

        return $db->query($sql);
    }

    private static function getTransactionTypeID($db, $transaction_type)
    {
        $transaction_type = $db->escapeString($transaction_type);
        $result = $db->query("SELECT `ID` FROM `transaction_types` WHERE `type` = '$transaction_type'");

        if ($result->num_rows > 0) {
            return $result->fetch_assoc()["ID"];
        } else {
            // If the transaction type does not exist, create it
            $db->query("INSERT INTO `transaction_types` (`type`) VALUES ('$transaction_type')");
            return $db->get_con()->insert_id;
        }
    }
}
