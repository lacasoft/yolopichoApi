<?php

namespace Yolopicho\Models;

use App\Config\DB;
use PDO;

class StateModel
{
    public static function fetchall()
    {
        $sql = "SELECT * FROM State";
        try {
            $db = new Db();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $states = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            return $states;
        } catch (\PDOException $e) {
            throw new \Exception('Error al recuperar categorías: ' . $e->getMessage(), 400);
        }
    }
}
