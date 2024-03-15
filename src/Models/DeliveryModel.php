<?php

namespace Yolopicho\Models;

use App\Config\DB;
use PDO;

class Status {
    const APPROVE = 'approve';
    const DELIVERY_PARTIAL = 'delivery_partial';
    const DELIVERY = 'delivery';
    const CANCEL = 'cancel';
}

class DeliveryModel
{
    public static function add(string $storeId, float $amount, string $photo, int $dishId, int $quantity)
    {

        $myDateTime = new \DateTime();
        $currentDateTime = $myDateTime->format('Y-m-d H:i:s');

        try {
            $db = new Db();
            $conn = $db->connect();
            $conn->beginTransaction();

            $sql = "SELECT id, receivedAmount, deliveredAmount, status FROM Donations
                    WHERE status IN (:approve, :deliveryPartial)
                    AND storeId = :storeId";

            $delivery = Status::DELIVERY;
            $aprove = Status::APPROVE;
            $partial = Status::DELIVERY_PARTIAL;

            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':approve', $aprove, PDO::PARAM_STR);
            $stmt->bindParam(':deliveryPartial', $partial, PDO::PARAM_STR);
            $stmt->bindParam(':storeId', $storeId, PDO::PARAM_STR);

            $stmt->execute();

            $donations = $stmt->fetchAll(PDO::FETCH_OBJ);

            $totalRest = $amount * $quantity;

            foreach ($donations as $register) {
                $difference = $register->receivedAmount - $register->deliveredAmount;

                if ($totalRest <= 0) {
                    break;
                }

                $donationId = $register->id;

                if ($totalRest >= $difference) {
                    $newStatus = $delivery;
                    $totalSpent = $difference;
                } else {
                    $newStatus = $partial;
                    $totalSpent = $totalRest;
                }

                $donationId = $register->id;

                $newDeliveredAmount = $register->deliveredAmount + $totalSpent;
                $totalRest -= $totalSpent;

                $updateSql = "UPDATE Donations SET status = :status, deliveredAmount = :deliveredAmount WHERE id = :id";

                $stmtDonations = $conn->prepare($updateSql);
                $stmtDonations->bindParam(':status', $newStatus, PDO::PARAM_STR);
                $stmtDonations->bindParam(':deliveredAmount', $newDeliveredAmount, PDO::PARAM_STR);
                $stmtDonations->bindParam(':id', $donationId, PDO::PARAM_STR);
                $stmtDonations->execute();


                $sqlDonationHistory = "INSERT INTO DonationsHistory (status, donationId, deliveredAmount, createdAt)
                VALUES (:status, :donationId, :deliveredAmount, :createdAt)";

                $stmtDonationHistory = $conn->prepare($sqlDonationHistory);
                $stmtDonationHistory->bindParam(':status', $newStatus, PDO::PARAM_STR);
                $stmtDonationHistory->bindParam(':donationId', $donationId, PDO::PARAM_STR);
                $stmtDonationHistory->bindParam(':deliveredAmount', $newDeliveredAmount, PDO::PARAM_STR);
                $stmtDonationHistory->bindParam(':createdAt', $currentDateTime, PDO::PARAM_STR);
                $stmtDonationHistory->execute();

                $sqlDdelivery = "INSERT INTO DeliveredDishes (donationId, dishId, quantity , equivalentMoney, deliveryPhoto, createdAt)
                VALUES (:donationId, :dishId, :quantity, :equivalentMoney, :deliveryPhoto, :createdAt)";

                $quantityDelivery = 1;
                $stmtDelivery = $conn->prepare($sqlDdelivery);
                $stmtDelivery->bindParam(':donationId', $donationId, PDO::PARAM_STR);
                $stmtDelivery->bindParam(':dishId', $dishId, PDO::PARAM_STR);
                $stmtDelivery->bindParam(':quantity', $quantityDelivery, PDO::PARAM_STR);
                $stmtDelivery->bindParam(':equivalentMoney', $amount, PDO::PARAM_STR);
                $stmtDelivery->bindParam(':deliveryPhoto', $photo, PDO::PARAM_STR);
                $stmtDelivery->bindParam(':createdAt', $currentDateTime, PDO::PARAM_STR);
                $stmtDelivery->execute();

                if ($totalRest <= 0 && $newStatus !== $partial) {
                    break;
                }
            }

            $conn->commit();
            $stmt = null;
            $stmtDonations = null;
            $stmtDonationHistory = null;
            $stmtDelivery = null;
            $db = null;

        } catch (\PDOException $e) {
            $conn->rollBack();
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 500);
        }

    }
}
