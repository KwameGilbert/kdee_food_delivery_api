<?php

declare(strict_types=1);

require_once CONFIG . 'Database.php';
require_once MODEL . 'Orders.php';

class Payments
{
    protected PDO $db;
    private string $tableName = 'payments';
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

    public function create(int $orderId, float $amount, string $method, string $transactionRef = null, string $status = 'pending'): int|false
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
            $allowed = ['card', 'cash', 'momo'];
            if (!in_array($method, $allowed, true)) {
                $this->lastError = 'Invalid payment method';
                return false;
            }
            $sql = "INSERT INTO {$this->tableName} (order_id, amount, method, status, transaction_ref, created_at) VALUES (:order_id, :amount, :method, :status, :transaction_ref, :created_at)";
            $stmt = $this->db->prepare($sql);
            $params = ['order_id' => $orderId, 'amount' => number_format($amount, 2, '.', ''), 'method' => $method, 'status' => $status, 'transaction_ref' => $transactionRef, 'created_at' => date('Y-m-d H:i:s')];
            if (!$this->executeQuery($stmt, $params)) return false;
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = 'Failed to create payment: ' . $e->getMessage();
            return false;
        }
    }

    public function getByOrderId(int $orderId): array
    {
        try {
            $sql = "SELECT id, order_id, amount, method, status, transaction_ref, created_at FROM {$this->tableName} WHERE order_id = :order_id ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['order_id' => $orderId])) return [];
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get payments: ' . $e->getMessage();
            return [];
        }
    }

    public function updateStatus(int $id, string $status): bool
    {
        try {
            $allowed = ['pending', 'completed', 'failed'];
            if (!in_array($status, $allowed, true)) {
                $this->lastError = 'Invalid status';
                return false;
            }
            $stmt = $this->db->prepare("UPDATE {$this->tableName} SET status = :status WHERE id = :id");
            return $this->executeQuery($stmt, ['status' => $status, 'id' => $id]);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to update payment status: ' . $e->getMessage();
            return false;
        }
    }
}
