<?php

declare(strict_types=1);

require_once CONFIG . 'Database.php';
require_once MODEL . 'Orders.php';

class Delivery
{
    protected PDO $db;
    private string $tableName = 'delivery';
    private string $lastError = '';

    public function __construct()
    {
        try {
            $this->db = (new Database())->getConnection();
        } catch (PDOException $e) {
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
            error_log($this->lastError);
            return false;
        }
    }

    public function assign(int $orderId, ?string $name = null, ?string $phone = null, string $status = 'assigned'): int|false
    {
        try {
            try {
                $orders = new Orders();
            } catch (PDOException $e) {
                $this->lastError = 'Failed to init Orders model';
                return false;
            }
            if (!$orders->getById($orderId)) {
                $this->lastError = 'Order not found';
                return false;
            }
            $sql = "INSERT INTO {$this->tableName} (order_id, delivery_person_name, delivery_person_phone, status) VALUES (:order_id, :name, :phone, :status)";
            $stmt = $this->db->prepare($sql);
            $params = ['order_id' => $orderId, 'name' => $name, 'phone' => $phone, 'status' => $status];
            if (!$this->executeQuery($stmt, $params)) return false;
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = 'Failed to assign delivery: ' . $e->getMessage();
            return false;
        }
    }

    public function updateStatus(int $id, string $status): bool
    {
        try {
            $allowed = ['assigned', 'on_the_way', 'delivered'];
            if (!in_array($status, $allowed, true)) {
                $this->lastError = 'Invalid status';
                return false;
            }
            $stmt = $this->db->prepare("UPDATE {$this->tableName} SET status = :status WHERE id = :id");
            return $this->executeQuery($stmt, ['status' => $status, 'id' => $id]);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to update delivery status: ' . $e->getMessage();
            return false;
        }
    }

    public function getByOrderId(int $orderId): ?array
    {
        try {
            $sql = "SELECT id, order_id, delivery_person_name, delivery_person_phone, status FROM {$this->tableName} WHERE order_id = :order_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['order_id' => $orderId])) return null;
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get delivery: ' . $e->getMessage();
            return null;
        }
    }
}
