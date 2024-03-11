<?php
class Database
{
    private static $host = "localhost";
    private static $username = "root";
    private static $password = "";
    private static $dbname = "webapp";
    private $conn;
    private static $instance = null;

    public function __construct()
    {
        $this->conn = new mysqli(self::$host, self::$username, self::$password, self::$dbname);

        if ($this->conn->connect_error) {
            die("Erro de conexão com o banco de dados: " . $this->conn->connect_error);
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
}
