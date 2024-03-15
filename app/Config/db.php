<?php

namespace App\Config;

use \PDO;

class DB
{
    private $host;
    private $user;
    private $pass;
    private $dbname;

    public function __construct()
    {
        $this->host = getenv('DB_HOST');
        $this->user = getenv('DB_USER');
        $this->pass = getenv('DB_PASS');
        $this->dbname = getenv('DB_NAME');
    }

    public function connect()
    {
        $conn_str = "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4";
        $conn = new PDO($conn_str, $this->user, $this->pass);
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }
}