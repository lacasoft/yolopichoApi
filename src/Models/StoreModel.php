<?php

namespace Yolopicho\Models;

use Ramsey\Uuid\Uuid;
use App\Config\DB;
use PDO;

class Status {
    const ACTIVE = 'active';
    const SUSPENDED = 'suspended';
}


class StoreModel
{
    public static function fetchById(string $storeId)
    {
        $sql = "SELECT sp.*, ad.* FROM StoreProfile sp
            INNER JOIN Store s ON s.id = sp.storeId
            INNER JOIN Address ad ON ad.id = sp.addressId
            WHERE sp.storeId = :storeId
            AND s.deletedAt IS NULL
            LIMIT 1";

        try {
            $db = new Db();
            $conn = $db->connect();

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':storeId', $storeId, PDO::PARAM_STR);
            $stmt->execute();

            $store = $stmt->fetch(PDO::FETCH_OBJ);

            $stmt = null;
            $db = null;
            return $store;

        } catch (\PDOException $e) {
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }

    public static function fetchall()
    {
        $sql = "SELECT cp.*, ca.name AS category_name, mp.name AS municipality_name FROM commerces_profiles cp
            INNER JOIN municipalities mp ON cp.municipality_id = mp.id
            INNER JOIN categories ca ON cp.category_id = ca.id
            INNER JOIN commerces co ON co.id = cp.commerce_id
            WHERE co.deleted_at IS NULL";

        try {
            $db = new Db();
            $conn = $db->connect();
            $result = $conn->query($sql);
            $commerces = $result->fetchAll(PDO::FETCH_OBJ);
            $result = null;
            $db = null;

            return $commerces;

        } catch (\PDOException $e) {
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }

    public static function fetchByName(string $name)
    {
        $searchName = '%' . $name . '%';

        $sql = "SELECT cp.*, ca.name AS category_name, mp.name AS municipality_name FROM commerces_profiles cp
            INNER JOIN municipalities mp ON cp.municipality_id = mp.id
            INNER JOIN categories ca ON cp.category_id = ca.id
            INNER JOIN commerces co ON co.id = cp.commerce_id
            WHERE cp.name LIKE :searchName
            AND co.deleted_at IS NULL";

        try {
            $db = new Db();
            $conn = $db->connect();

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':searchName', $searchName, PDO::PARAM_STR);
            $stmt->execute();

            $commerces = $stmt->fetchAll(PDO::FETCH_OBJ);

            $stmt = null;
            $db = null;


            return $commerces;

        } catch (\PDOException $e) {
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }

    public static function fetchByCityName(string $cityName)
    {
        $searchName = '%' . $cityName . '%';

        $sql = "SELECT cp.*, ca.name AS category_name, mp.name AS municipality_name FROM commerces_profiles cp
            INNER JOIN municipalities mp ON cp.municipality_id = mp.id
            INNER JOIN categories ca ON cp.category_id = ca.id
            INNER JOIN commerces co ON co.id = cp.commerce_id
            WHERE mp.name LIKE :searchName
            AND co.deleted_at IS NULL";

        try {
            $db = new Db();
            $conn = $db->connect();

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':searchName', $searchName, PDO::PARAM_STR);
            $stmt->execute();

            $commerces = $stmt->fetchAll(PDO::FETCH_OBJ);

            $stmt = null;
            $db = null;


            return $commerces;

        } catch (\PDOException $e) {
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }

    public static function fetchByCategoryName(string $categoryName)
    {
        $searchName = '%' . $categoryName . '%';

        $sql = "SELECT cp.*, ca.name AS category_name, mp.name AS municipality_name FROM commerces_profiles cp
            INNER JOIN municipalities mp ON cp.municipality_id = mp.id
            INNER JOIN categories ca ON cp.category_id = ca.id
            INNER JOIN commerces co ON co.id = cp.commerce_id
            WHERE ca.name LIKE :searchName
            AND co.deleted_at IS NULL";

        try {
            $db = new Db();
            $conn = $db->connect();

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':searchName', $searchName, PDO::PARAM_STR);
            $stmt->execute();

            $commerces = $stmt->fetchAll(PDO::FETCH_OBJ);

            $stmt = null;
            $db = null;

            return $commerces;

        } catch (\PDOException $e) {
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }

    public static function add(string $name, int $categoryId, string $email, string $password, string $phone, string $streetAddress, string $streetNumber, string $streetIntNumber, int $cityId, int $stateId)
    {
        try {
            $db = new Db();
            $conn = $db->connect();
            $conn->beginTransaction();

            $sqlAddress = "INSERT INTO Address
            (streetAddress, streetNumber, cityId, stateId)
            VALUES
            (:streetAddress, :streetNumber, :cityId, :stateId)";

            $stmtAddress = $conn->prepare($sqlAddress);

            if ($streetIntNumber != '') {
                $streetNumber .= " " . $streetIntNumber;
            }

            $stmtAddress->bindParam(':streetAddress', $streetAddress, PDO::PARAM_STR);
            $stmtAddress->bindParam(':streetNumber', $streetNumber, PDO::PARAM_STR);
            $stmtAddress->bindParam(':cityId', $cityId, PDO::PARAM_STR);
            $stmtAddress->bindParam(':stateId', $stateId, PDO::PARAM_STR);

            $resultAddress = $stmtAddress->execute();
            $addressId = $conn->lastInsertId();

            if($resultAddress){
                $uuid = Uuid::uuid4();
                $storeId = $uuid->toString();

                $sqlProfile = "INSERT INTO StoreProfile
                (storeId, name, categoryId, phone, addressId)
                VALUES
                (:storeId, :name, :categoryId, :phone, :addressId)";

                $stmtProfile = $conn->prepare($sqlProfile);

                if($streetIntNumber == '') $streetIntNumber = null;
                $stmtProfile->bindParam(':storeId', $storeId, PDO::PARAM_STR);
                $stmtProfile->bindParam(':name', $name, PDO::PARAM_STR);
                $stmtProfile->bindParam(':categoryId', $categoryId, PDO::PARAM_STR);
                $stmtProfile->bindParam(':phone', $phone, PDO::PARAM_STR);
                $stmtProfile->bindParam(':addressId', $addressId, PDO::PARAM_STR);

                $resultProfile = $stmtProfile->execute();
            }

            if($resultProfile){
                $sql = "INSERT INTO Store(id, email, passwordHash, status, addressId, createdAt, updatedAt) VALUES (:id, :email, :password, :status, :addressId, :createdAt, :updatedAt)";
                $myDateTime = new \DateTime();
                $currentDateTime = $myDateTime->format('Y-m-d H:i:s');

                $stmt = $conn->prepare($sql);

                $status = Status::ACTIVE;

                $stmt->bindParam(':id', $storeId, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                $stmt->bindParam(':status', $status, PDO::PARAM_STR);
                $stmt->bindParam(':addressId', $addressId, PDO::PARAM_STR);
                $stmt->bindParam(':createdAt', $currentDateTime, PDO::PARAM_STR);
                $stmt->bindParam(':updatedAt', $currentDateTime, PDO::PARAM_STR);

                $stmt->execute();
            }

            $conn->commit();
            $stmtAddress = null;
            $stmt = null;
            $stmtProfile = null;
            $db = null;

        } catch (\PDOException $e) {
            $conn->rollBack();
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }

    public static function login(string $email)
    {
        $sql = "SELECT s.* FROM Store s
            WHERE s.email = :email
            AND s.status = 'active'
            AND s.deletedAt IS NULL
            LIMIT 1";

        try {
            $db = new Db();
            $conn = $db->connect();

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            $store = $stmt->fetch(PDO::FETCH_OBJ);
            $stmt = null;
            $db = null;
            return $store;

        } catch (\PDOException $e) {
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }

    public static function update(string $storeId, string $name, int $categoryId, string $phone, string $streetAddress, string $streetNumber, string $streetIntNumber, int $cityId, int $stateId)
    {
        try {
            $store = self::fetchById($storeId);

            if (empty($store))
                throw new \Exception('Recurso no encontrado', 400);


            $db = new Db();
            $conn = $db->connect();
            $conn->beginTransaction();

            if($name != '' || $categoryId != 0 || $phone != '') {
                $sql = "UPDATE StoreProfile SET
                name = :name,
                categoryId = :categoryId,
                phone = :phone
                WHERE storeId = :id";

                $newName = $name == '' ? $store->name : $name;
                $newCategoryId = $categoryId == 0 ? $store->categoryId : $categoryId;
                $newPhone = $phone == '' ? $store->phone : $phone;
                $id = $store->storeId;

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $newName, PDO::PARAM_STR);
                $stmt->bindParam(':categoryId', $newCategoryId, PDO::PARAM_STR);
                $stmt->bindParam(':phone', $newPhone, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_STR);

                $stmt->execute();
            }

            if($streetAddress != '' || $streetNumber != '' || $streetIntNumber != '' || $cityId != 0 || $stateId != 0) {
                $streetNumberFull = $streetIntNumber != '' ? $streetNumber . " - " . $streetIntNumber : $streetNumber;

                $sqlAddress = "UPDATE Address SET
                streetAddress = :streetAddress,
                streetNumber = :streetNumber,
                cityId = :cityId,
                stateId = :stateId
                WHERE id = :id";

                $newStreetAddress = $streetAddress == '' ? $store->streetAddress : $streetAddress;
                $newStreetNumber = $streetNumberFull == '' ? $store->streetIntNumber : $streetNumberFull;
                $newCityId = $cityId == 0 ? $store->cityId : $cityId;
                $newStateId = $stateId == 0 ? $store->stateId : $stateId;
                $id = $store->addressId;

                $stmtAddress = $conn->prepare($sqlAddress);
                $stmtAddress->bindParam(':streetAddress', $newStreetAddress, PDO::PARAM_STR);
                $stmtAddress->bindParam(':streetNumber', $newStreetNumber, PDO::PARAM_STR);
                $stmtAddress->bindParam(':cityId', $newCityId, PDO::PARAM_STR);
                $stmtAddress->bindParam(':stateId', $newStateId, PDO::PARAM_STR);
                $stmtAddress->bindParam(':id', $id, PDO::PARAM_STR);

                $stmtAddress->execute();
            }

            $conn->commit();
            $stmtAddress = null;
            $stmt = null;
            $db = null;


        } catch (\PDOException $e) {
            $conn->rollBack();
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }

    public static function updatePassword(string $storeId, string $passwordHash)
    {
        $store = self::fetchById($storeId);

        if (empty($store))
            throw new \Exception('Recurso no encontrado', 400);

        $sql = "UPDATE Store SET
        passwordHash = :passwordHash,
        updatedAt = :updatedAt
        WHERE id = :id";

        $myDateTime = new \DateTime();
        $currentDateTime = $myDateTime->format('Y-m-d H:i:s');

        try {

            $db = new Db();
            $conn = $db->connect();

            $stmt = $conn->prepare($sql);
            $newPasswordHash = $passwordHash;
            $newUpdatedAt = $currentDateTime;
            $id = $store->storeId;

            $stmt->bindParam(':passwordHash', $newPasswordHash, PDO::PARAM_STR);
            $stmt->bindParam(':updatedAt', $newUpdatedAt, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);

            $stmt->execute();
            $stmt = null;
            $db = null;

        } catch (\PDOException $e) {
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }

    }

    public static function updateLogo(string $storeId, string $logo)
    {
        $store = self::fetchById($storeId);

        if (empty($store))
            throw new \Exception('Recurso no encontrado', 400);

        $sql = "UPDATE StoreProfile SET
        logo = :logo
        WHERE storeId = :id";

        try {

            $db = new Db();
            $conn = $db->connect();

            $stmt = $conn->prepare($sql);
            $newLogo = $logo;
            $id = $store->storeId;

            $stmt->bindParam(':logo', $newLogo, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);

            $stmt->execute();
            $stmt = null;
            $db = null;

        } catch (\PDOException $e) {
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }
    }


}
