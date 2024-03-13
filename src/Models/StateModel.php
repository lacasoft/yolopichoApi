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
            $result = $conn->query($sql);
            //if($result->rowCount() > 0)
            $categories = $result->fetchAll(PDO::FETCH_OBJ);
            $result = null;
            $db = null;

            return $categories;

        } catch (\PDOException $e) {
            return $e;
        }
    }
}
