<?php

class Database
{
    // ? means nullable and Database also as a type 
    private static ?Database $instance = null;


    private PDO $conn;

    private function __construct()
    {
        $host = $_ENV["DB_HOST"] ?? 'localhost';
        $dbname = $_ENV["DB_NAME"] ?? '';
        $username = $_ENV["DB_USER"] ?? '';
        $password = $_ENV["DB_PASS"] ?? '';

        $this->conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }
    private function __clone() {}

    // Prevents unserializing the object
    private function __wakeup() 
    {
        throw new Exception("Cannot unserialize a singleton.");
    }

}
