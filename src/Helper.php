<?php


namespace App;

use PDO; 

class DatabaseConnectionMYSQL
{
    // ? If you have a dotenv file, you can set this properties values from that env file
    // ? You need to install dotenv library using composer if you want to use it, i prefer mine like this since its only a simple project
    protected $DB_HOST = "localhost";
    protected $DB_PORT = 3306;
    protected $DB_USER = "root";
    protected $DB_PASS = "";
    protected $DB_NAME = "logbook_db";

    private $pdo;

    public function __construct()
    {
        $dsn = "mysql:host=" . $this->DB_HOST . ";port=" . $this->DB_PORT . ";dbname=" . $this->DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true
        ];
        $this->pdo = new PDO(dsn: $dsn, username: $this->DB_USER, password: $this->DB_PASS, options: $options);
    }

    public function connect()
    {
        return $this->pdo;
    }

    public function __destruct()
    {
        $this->pdo = null;
    }
}



class Helper
{
    public function SanitizeInput(string|int $data): string|int
    {
        $data = trim($data);
        $data = htmlentities($data);
        $data = stripslashes($data);
        return $data;
    }

    public function SetResponse(int $status, string | int | array $data): array
    {
        http_response_code($status);
        return ['status' => $status, "response" => $data];
    }

    /**
     * A helper method that returns an array containing all the request body information/data
     */
    public function RequestBody(array $body): array
    {
        if (empty($body)) {
            $data = $_POST;
        } else {
            $data = $body;
        }

        return $data;
    }

    public function QueryPlaceholder(array $arr, string $placeholder)
    {
        $string = implode(",", array_fill(0, count($arr), $placeholder));
        return $string;
    }
}
