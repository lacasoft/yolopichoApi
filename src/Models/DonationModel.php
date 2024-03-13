<?php

namespace Yolopicho\Models;

use Ramsey\Uuid\Uuid;
use App\Config\DB;
use PDO;

class Status {
    const APPROVE = 'approve';
    const DELIVERY_PARTIAL = 'delivery partial';
    const DELIVERY = 'delivery';
    const CANCEL = 'cancel';
}

class DonationModel
{
    public static function fetchall(string $storeId, array $queryFilters)
    {
        $sql = "SELECT don.id, d.email, don.receivedAmount, don.deliveredAmount, don.status, don.note, don.createdAt FROM Donations don
        INNER JOIN Donators d ON d.id = don.donatorId
        WHERE don.storeId = :storeId";

        if (!empty($queryFilters['status'])) {
            $sql .= " AND don.status = :status";
        }
        if (!empty($queryFilters['date'])) {
            $startDate = $queryFilters['date'] . ' 00:00:00';
            $endDate = $queryFilters['date'] . ' 23:59:59';

            // Ajusta tu consulta para filtrar por el rango de fecha
            $sql .= " AND don.createdAt BETWEEN STR_TO_DATE(:startDate, '%Y-%m-%d %H:%i:%s') AND STR_TO_DATE(:endDate, '%Y-%m-%d %H:%i:%s')";
        }

        try {
            $db = new Db();
            $conn = $db->connect();

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':storeId', $storeId, PDO::PARAM_STR);
            if (!empty($queryFilters['status'])) {
                $stmt->bindParam(':status', $queryFilters['status'], PDO::PARAM_STR);
            }
            if (!empty($queryFilters['date'])) {
                $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
                $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
            }
            $stmt->execute();

            $donations = $stmt->fetchAll(PDO::FETCH_OBJ);

            $stmt = null;
            $conn = null;
            return $donations;

        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private static function fetchByEmail(string $email)
    {
        $searchEmail = '%' . $email . '%';

        $sql = "SELECT * FROM Donators
            WHERE email LIKE :searchEmail
            LIMIT 1";

        try {
            $db = new Db();
            $conn = $db->connect();

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':searchEmail', $searchEmail, PDO::PARAM_STR);
            $stmt->execute();

            $donator = $stmt->fetch(PDO::FETCH_OBJ);

            $stmt = null;
            $db = null;
            return $donator;

        } catch (\PDOException $e) {
            return $e;
        }
    }

    private static function fetchByDonationId(string $donation)
    {
        $statusOk = Status::APPROVE;
        $statusDev = Status::DELIVERY;
        $statusFail = Status::CANCEL;

        $sql = "SELECT * FROM donations
            WHERE id = :donation
            AND status <> :statusOk
            AND status <> :statusDev
            AND status <> :statusFail
            LIMIT 1";

        try {
            $db = new Db();
            $conn = $db->connect();

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':donation', $donation, PDO::PARAM_STR);
            $stmt->bindParam(':statusOk', $statusOk, PDO::PARAM_STR);
            $stmt->bindParam(':statusDev', $statusDev, PDO::PARAM_STR);
            $stmt->bindParam(':statusFail', $statusFail, PDO::PARAM_STR);
            $stmt->execute();

            $donation = $stmt->fetchAll(PDO::FETCH_OBJ);

            $stmt = null;
            $db = null;


            return $donation;

        } catch (\PDOException $e) {
            return $e;
        }
    }

    public static function add(string $email, string $storeId, float $amount)
    {
        $myDateTime = new \DateTime();
        $currentDateTime = $myDateTime->format('Y-m-d H:i:s');

        try {

            $donator = self::fetchByEmail($email);

            $db = new Db();
            $conn = $db->connect();
            $conn->beginTransaction();

            if (empty($donator)){
                $sql = "INSERT INTO Donators (id, email, createdAt) VALUES (:id, :email, :createdAt)";

                $uuid = Uuid::uuid4();
                $donatorId = $uuid->toString();

                $stmt = $conn->prepare($sql);

                $stmt->bindParam(':id', $donatorId, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':createdAt', $currentDateTime, PDO::PARAM_STR);

                $stmt->execute();
            }
            else{
                $donatorId = $donator->id;
            }

            $sqlDonation = "INSERT INTO Donations (receivedAmount, status, storeId, donatorId, createdAt)
            VALUES (:receivedAmount, :status, :storeId, :donatorId, :createdAt)";

            $stmtDonation = $conn->prepare($sqlDonation);

            $status = Status::APPROVE;

            $stmtDonation->bindParam(':receivedAmount', $amount, PDO::PARAM_STR);
            $stmtDonation->bindParam(':status', $status, PDO::PARAM_STR);
            $stmtDonation->bindParam(':storeId', $storeId, PDO::PARAM_STR);
            $stmtDonation->bindParam(':donatorId', $donatorId, PDO::PARAM_STR);
            $stmtDonation->bindParam(':createdAt', $currentDateTime, PDO::PARAM_STR);

            $stmtDonation->execute();
            $donationId = $conn->lastInsertId();

            $sqlDonationHistory = "INSERT INTO DonationsHistory (status, donationId, createdAt)
            VALUES (:status, :donationId, :createdAt)";


            $stmtDonationHistory = $conn->prepare($sqlDonationHistory);

            $stmtDonationHistory->bindParam(':status', $status, PDO::PARAM_STR);
            $stmtDonationHistory->bindParam(':donationId', $donationId, PDO::PARAM_STR);
            $stmtDonationHistory->bindParam(':createdAt', $currentDateTime, PDO::PARAM_STR);

            $stmtDonationHistory->execute();

            $conn->commit();
            $stmt = null;
            $stmtDonation = null;
            $stmtDonationHistory = null;
            $db = null;

        } catch (\PDOException $e) {
            $conn->rollBack();
            return $e;
        }
    }

    public static function cancelDonation(string $donationId, string $note)
    {
        $myDateTime = new \DateTime();
        $currentDateTime = $myDateTime->format('Y-m-d H:i:s');
        $status = Status::CANCEL;

        try {

            $donation = self::fetchByDonationId($donationId);

            if (!empty($donation)){
                $db = new Db();
                $conn = $db->connect();
                $conn->beginTransaction();


                $sql = "UPDATE Donations SET
                status = :status,
                note = :note
                WHERE id = :id";

                $stmt = $conn->prepare($sql);

                $stmt->bindParam(':status', $status, PDO::PARAM_STR);
                $stmt->bindParam(':note', $note, PDO::PARAM_STR);
                $stmt->bindParam(':id', $donationId, PDO::PARAM_STR);

                $result = $stmt->execute();

                $sqlDonationHistory = "INSERT INTO DonationsHistory (status, donationId, createdAt)
                VALUES (:status, :donationId, :createdAt)";

                $stmtDonationHistory = $conn->prepare($sqlDonationHistory);

                $stmtDonationHistory->bindParam(':status', $status, PDO::PARAM_STR);
                $stmtDonationHistory->bindParam(':donationId', $donationId, PDO::PARAM_STR);
                $stmtDonationHistory->bindParam(':createdAt', $currentDateTime, PDO::PARAM_STR);

                $stmtDonationHistory->execute();

                $conn->commit();
                $stmt = null;
                $stmtDonationHistory = null;
                $db = null;
            } else{
                throw new \Exception("El donativo no esta disponible");
            }
        } catch (\PDOException $e) {
            $conn->rollBack();
            return $e;
        }
    }

    private static function addHistoryDonation(string $donation){

        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }

    }
}
