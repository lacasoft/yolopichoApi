<?php

namespace Yolopicho\Models;

use App\Config\DB;
use PDO;

class CityModel
{
    public static function fetchall()
    {
        $sql = "SELECT c.* FROM City c";

        try {
            $db = new Db();
            $conn = $db->connect();
            $result = $conn->query($sql);
            //if($result->rowCount() > 0)
            $categories = $result->fetchAll(PDO::FETCH_OBJ);
            $result = null;
            $db = null;

            return $categories;

        } catch (\PDOException $e) {
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }

    public static function fetchByStateId(int $stateId)
    {
        $sql = "SELECT c.* FROM City c WHERE c.stateId = $stateId; ";

        try {
            $db = new Db();
            $conn = $db->connect();
            $result = $conn->query($sql);
            //if($result->rowCount() > 0)
            $categories = $result->fetchAll(PDO::FETCH_OBJ);
            $result = null;
            $db = null;

            return $categories;

        } catch (\PDOException $e) {
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }
}
