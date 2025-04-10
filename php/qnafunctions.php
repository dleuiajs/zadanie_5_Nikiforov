<?php
namespace otazkyodpovede;
error_reporting(E_ALL);
ini_set("display_errors", "On");
require_once("db/Database.php");
use Exception, databaza\Database;

class QnA extends Database
{
    protected $connection;

    public function __construct()
    {
        $this->connect();
        $this->connection = $this->getConnection();
    }

    public function createData()
    {
        try {
            // dopyt na vytvorenie databázy
            // $sqlCreateDb = "CREATE DATABASE IF NOT EXISTS sablona";
            // $statement = $conn->prepare($sqlCreateDb);
            // $statement->execute();

            // dopyt na vytvorenie tabuľky
            $sqlCreateTable = "
            CREATE TABLE IF NOT EXISTS qna (
                id INT AUTO_INCREMENT PRIMARY KEY, 
                otazka VARCHAR(255) NOT NULL,
                odpoved VARCHAR(1000) NOT NULL,
                UNIQUE(otazka, odpoved)
            );
        ";
            // vykonávame mysql dopyt
            $statement = $this->connection->prepare($sqlCreateTable);
            $statement->execute();
            http_response_code(200);
        } catch (\Exception $e) {
            echo "Chyba: " . $e->getMessage();
            http_response_code(500);
        }
    }

    public function insertQnA()
    {
        try {
            // čítame dáta z json súboru
            $data = json_decode(file_get_contents("data/qna.json"), true);
            $otazky = $data["otazky"];
            $odpovede = $data["odpovede"];

            // spustenie transakcie
            $this->connection->beginTransaction();
            // dopyt na vloženie údajov do tabuľky (ak takéto údaje už existujú, nebudú vložené, pretože sú jedinečné a používa sa INSERT IGNORE)
            $sql = "INSERT IGNORE INTO qna (otazka, odpoved) VALUES (:otazka, :odpoved)";
            $statement = $this->connection->prepare($sql);

            // vkladáme každú otázku a odpoveď
            for ($i = 0; $i < count($otazky); $i++) {
                $statement->bindParam(":otazka", $otazky[$i]);
                $statement->bindParam(":odpoved", $odpovede[$i]);
                $statement->execute();
            }
            // dokončíme transakciu a urobíme zmeny trvalými
            $this->connection->commit();
            http_response_code(200);
        } catch (\Exception $e) {
            echo "Chyba pri vkladaní dát do datábazý: " . $e->getMessage();
            // vraciame zmeny
            $this->connection->rollBack();
            http_response_code(500);
        }
    }

    public function generateQnA()
    {
        try {
            echo '<section class="container">';
            // dopyt na výber všetkých údajov z tabuľky
            $sql = "SELECT * FROM qna";
            // používame metódu query, pretože sa budú vracať údaje z tabuľky
            $statement = $this->connection->query($sql);

            // získavame údaje
            $result = $statement->fetchAll();

            //  robíme akordeón pre každý riadok v tabuľke
            foreach ($result as $row) {
                echo '<div class="accordion">
                    <div class="question">' . $row["otazka"] . '</div>
                    <div class="answer">' . $row["odpoved"] . '</div>
                  </div>';
            }
            echo '</section>';
            http_response_code(200);
        } catch (\Exception $e) {
            echo "Chyba pri čítaní dát z databázy: " . $e->getMessage();
            http_response_code(500);
        }
    }

}
?>