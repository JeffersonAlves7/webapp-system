<?php

use PSpell\Config;

require_once("Managers/ConfigManager.php");

class Database
{
    private static $host;
    private static $username;
    private static $password;
    private static $dbname;
    private $conn;
    private static $instance = null;

    public function __construct()
    {
        self::$host = ConfigManager::$DATABASE_HOST;
        self::$username = ConfigManager::$DATABASE_USERNAME;
        self::$password = ConfigManager::$DATABASE_PASSWORD;
        self::$dbname = ConfigManager::$DATABASE_NAME;

        $this->conn = new mysqli(self::$host, self::$username, self::$password, self::$dbname);

        if ($this->conn->connect_error) {
            die("Erro de conexão com o banco de dados: " . $this->conn->connect_error);
        }

        // Set timezone for this session
        if (
            ConfigManager::$ENVIRONMENT === "PROD" &&
            !$this->conn->query("SET time_zone = 'America/Sao_Paulo';")
        ) {
            die("Erro ao definir o fuso horário: " . $this->conn->error);
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function query($sql)
    {
        return $this->conn->query($sql);
    }

    public function escapeString($string)
    {
        return $this->conn->real_escape_string($string);
    }

    public function beginTransaction()
    {
        return $this->conn->begin_transaction();
    }

    public function commit()
    {
        return $this->conn->commit();
    }

    public function rollback()
    {
        return $this->conn->rollback();
    }

    public function close()
    {
        return $this->conn->close();
    }

    public function get_con()
    {
        return $this->conn;
    }

    public function prepare($sql)
    {
        return $this->conn->prepare($sql);
    }
}
