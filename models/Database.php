<?php

class Database
{
    // ? means nullable and Database also as a type 
    private static ?Database $instance = null;

    private string $host = "localhost";
    private string $db_name = "enrollment_db";
    private string $username = "root";
    private string $password = "Root@123";

    private PDO $conn;

    private function __construct()
    {
        $this->conn = new PDO("mysql:host=localhost;dbname=enrollment_db", 'root', 'Root@123');
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

}
