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
            $stmt = $conn->query($sql);
            $categories = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            return $categories;
        } catch (\PDOException $e) {
            throw new \Exception('Error al recuperar categorías: ' . $e->getMessage(), 400);
        }
    }
}
