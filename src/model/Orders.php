<?php

declare(strict_types=1);

require_once CONFIG . 'Database.php';
require_once MODEL . 'Users.php';
require_once MODEL . 'Addresses.php';

/**
 * Orders model
 * Table: orders(id, user_id, address_id, status, total_amount, delivery_fee, created_at)
 */
class Orders
{
    protected PDO $db;
    private string $tableName = 'orders';
    private string $lastError = '';

    public function __construct()
    {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
        } catch (PDOException $e) {
            $this->lastError = 'Database connection failed: ' . $e->getMessage();
            error_log($this->lastError);
            throw $e;
        }
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    protected function executeQuery(\PDOStatement $stmt, array $params = []): bool
    {
        try {
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->lastError = 'Query failed: ' . $e->getMessage();
            error_log($this->lastError . ' - SQL: ' . $stmt->queryString);
            return false;
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $sql = "SELECT id, user_id, address_id, status, total_amount, delivery_fee, created_at FROM {$this->tableName} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['id' => $id])) return null;
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get order: ' . $e->getMessage();
            return null;
        }
    }

    public function getByUserId(int $userId): array
    {
        try {
            $sql = "SELECT id, user_id, address_id, status, total_amount, delivery_fee, created_at FROM {$this->tableName} WHERE user_id = :user_id ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['user_id' => $userId])) return [];
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get orders by user: ' . $e->getMessage();
            return [];
        }
    }

    /**
     * Create order header. Does not create order items.
     */
    public function create(int $userId, int $addressId, float $totalAmount, float $deliveryFee = 0.00, string $status = 'pending'): int|false
    {
        try {
            // validate user and address
            try {
                $usersModel = new Users();
                $addressesModel = new Addresses();
            } catch (PDOException $e) {
                $this->lastError = 'Failed to initialise related models: ' . $e->getMessage();
                return false;
            }

            if (!$usersModel->getById($userId)) {
                $this->lastError = 'User not found';
                return false;
            }
            if (!$addressesModel->getById($addressId)) {
                $this->lastError = 'Address not found';
                return false;
            }

            $sql = "INSERT INTO {$this->tableName} (user_id, address_id, status, total_amount, delivery_fee, created_at) VALUES (:user_id, :address_id, :status, :total_amount, :delivery_fee, :created_at)";
            $stmt = $this->db->prepare($sql);
            $params = [
                'user_id' => $userId,
                'address_id' => $addressId,
                'status' => $status,
                'total_amount' => number_format($totalAmount, 2, '.', ''),
                'delivery_fee' => number_format($deliveryFee, 2, '.', ''),
                'created_at' => date('Y-m-d H:i:s'),
            ];
            if (!$this->executeQuery($stmt, $params)) return false;
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = 'Failed to create order: ' . $e->getMessage();
            return false;
        }
    }

    public function updateStatus(int $orderId, string $status): bool
    {
        try {
            $allowed = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
            if (!in_array($status, $allowed, true)) {
                $this->lastError = 'Invalid status';
                return false;
            }
            $stmt = $this->db->prepare("UPDATE {$this->tableName} SET status = :status WHERE id = :id");
            return $this->executeQuery($stmt, ['status' => $status, 'id' => $orderId]);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to update status: ' . $e->getMessage();
            return false;
        }
    }
}
