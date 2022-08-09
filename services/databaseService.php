<?php

class DatabaseService
{
    private string $db_host;
    private string $db_name;
    private string $db_user;
    private string $db_password;

    public function __construct()
    {
        $dbConfig = require_once('../config/database.php');
        $this->db_host = $dbConfig["host"];
        $this->db_name = $dbConfig["database"];
        $this->db_user = $dbConfig["username"];
        $this->db_password = $dbConfig["password"];
    }

    public function getConnection() : ?PDO
    {
        $connection = null;

        try {
            $connection = new PDO(
                "mysql:host=" . $this->db_host .
                ";dbname=" . $this->db_name,
                $this->db_user, $this->db_password,
                [
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                ]
            );
        } catch (PDOException $exception) {
            echo "Connection failed: " . $exception->getMessage();
        }

        return $connection;
    }
}