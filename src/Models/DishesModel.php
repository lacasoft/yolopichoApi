<?php

namespace Yolopicho\Models;

use Ramsey\Uuid\Uuid;
use App\Config\DB;
use PDO;

class DishesModel
{
    private static function fetchById(string $storeId, string $dishId)
    {
        $sql = "SELECT ds.id, ds.name, ds.cost FROM Dishes ds
            WHERE ds.storeId = :storeId
            AND ds.id = :dishId
            AND ds.deletedAt IS NULL";

        try {
            $db = new Db();
            $conn = $db->connect();

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':storeId', $storeId, PDO::PARAM_STR);
            $stmt->bindParam(':dishId', $dishId, PDO::PARAM_STR);
            $stmt->execute();

            $store = $stmt->fetch(PDO::FETCH_OBJ);

            $stmt = null;
            $db = null;
            return $store;

        } catch (\PDOException $e) {
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }

    public static function fetchall(string $storeId)
    {
        $sql = "SELECT ds.id, ds.name, ds.cost  FROM Dishes ds
            WHERE ds.storeId = :storeId
            AND ds.deletedAt IS NULL";

        try {
            $db = new Db();
            $conn = $db->connect();

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':storeId', $storeId, PDO::PARAM_STR);
            $stmt->execute();

            $dishes = $stmt->fetchAll(PDO::FETCH_OBJ);

            $stmt = null;
            $db = null;
            return $dishes;

        } catch (\PDOException $e) {
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }

    public static function add(string $storeId, string $name, float $cost)
    {
        $myDateTime = new \DateTime();
        $currentDateTime = $myDateTime->format('Y-m-d H:i:s');

        try {

            $db = new Db();
            $conn = $db->connect();

            $sql = "INSERT INTO Dishes (name, cost, storeId, createdAt) VALUES (:name, :cost, :storeId, :createdAt)";

            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':cost', $cost, PDO::PARAM_STR);
            $stmt->bindParam(':storeId', $storeId, PDO::PARAM_STR);
            $stmt->bindParam(':createdAt', $currentDateTime, PDO::PARAM_STR);

            $stmt->execute();

            $stmt = null;
            $db = null;

        } catch (\PDOException $e) {
            $conn->rollBack();
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }

    public static function deleteDish(string $storeId, string $dishId)
    {
        $myDateTime = new \DateTime();
        $currentDateTime = $myDateTime->format('Y-m-d H:i:s');

        try {

            $dish = self::fetchById($storeId, $dishId);
            if (!empty($dish)){
                $db = new Db();
                $conn = $db->connect();

                $sql = "UPDATE Dishes SET
                deletedAt  = :deletedAt
                WHERE id = :id";

                $stmt = $conn->prepare($sql);

                $stmt->bindParam(':deletedAt', $currentDateTime, PDO::PARAM_STR);
                $stmt->bindParam(':id', $dish->id, PDO::PARAM_STR);

                $stmt->execute();

                $stmt = null;
                $db = null;
            } else{
                throw new \Exception("El platillo no esta disponible", 400);
            }

        } catch (\PDOException $e) {
            $conn->rollBack();
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }

}
