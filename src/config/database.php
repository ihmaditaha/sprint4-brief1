<?php

class Database
{

    private $host = "localhost";
    private $dbName = "nowpay";
    private $username = "root";
    private $password = "";

    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct()
    {
        try {
            $dsn = "mysql:host=$this->host;dbname=$this->dbName;";
            $this->connection = new PDO($dsn, $this->username, $this->password);
        } catch (\PDOException $th) {
            throw new ServerErrorException("Database Error", 500, $th);
        }
    }

    public static function getInstance(): ?Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function __clone() {
    throw new Exception("Can't clone a singleton");
    }
}