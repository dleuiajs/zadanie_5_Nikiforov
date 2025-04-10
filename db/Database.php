<?php
namespace databaza;
require_once("db/config.php");
use PDO, PDOException;

class Database
{
    private $conn;

    public function __construct()
    {
        $this->connect();
    }
    protected function connect()
    {
        $config = DATABASE;
        // možnosti
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // nastaví režim hlásenia chýb na výnimky
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // nastaví predvolený režim načítanie dát do asociatívnych polí
        );

        // pripojenie PDO
        try {
            $this->conn = new PDO("mysql:host=" . $config["HOST"] . ";dbname=" . $config["DBNAME"] . ";port=" . $config["PORT"], $config["USER_NAME"], $config["PASSWORD"], $options);
        } catch (PDOException $e) {
            die("Chyba propojenia: " . $e->getMessage());
        }
    }

    protected function getConnection()
    {
        return $this->conn;
    }
}
?>