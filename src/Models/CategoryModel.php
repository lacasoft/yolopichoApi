<?php

namespace Yolopicho\Models;

use App\Config\DB;
use PDO;

class CategoryModel
{
    public static function fetchall()
    {
        $sql = "SELECT * FROM Categories";
        try {
            $db = new Db();
            $conn = $db->connect();
            $result = $conn->query($sql);
            $categories = $result->fetchAll(PDO::FETCH_OBJ);

            $conn = null;

            return $categories;
        } catch (\PDOException $e) {
            throw new \Exception('Error al recuperar categorías: ' . $e->getMessage());
        }
    }
}
