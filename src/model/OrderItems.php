<?php

declare(strict_types=1);

require_once CONFIG . 'Database.php';
require_once MODEL . 'Orders.php';
require_once MODEL . 'Foods.php';

/**
 * OrderItems model
 * Table: order_items(id, order_id, food_id, quantity, price)
 */
class OrderItems
{
    protected PDO $db;
    private string $tableName = 'order_items';
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

    public function addItem(int $orderId, int $foodId, int $quantity, float $price): int|false
    {
        try {
            try {
                $orders = new Orders();
                $foods = new Foods();
            } catch (PDOException $e) {
                $this->lastError = 'Failed to init related models: ' . $e->getMessage();
                return false;
            }
            if (!$orders->getById($orderId)) {
                $this->lastError = 'Order not found';
                return false;
            }
            if (!$foods->getById($foodId)) {
                $this->lastError = 'Food not found';
                return false;
            }
            $sql = "INSERT INTO {$this->tableName} (order_id, food_id, quantity, price) VALUES (:order_id, :food_id, :quantity, :price)";
            $stmt = $this->db->prepare($sql);
            $params = ['order_id' => $orderId, 'food_id' => $foodId, 'quantity' => $quantity, 'price' => number_format($price, 2, '.', '')];
            if (!$this->executeQuery($stmt, $params)) return false;
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = 'Failed to add order item: ' . $e->getMessage();
            return false;
        }
    }

    public function getByOrderId(int $orderId): array
    {
        try {
            $sql = "SELECT id, order_id, food_id, quantity, price FROM {$this->tableName} WHERE order_id = :order_id ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['order_id' => $orderId])) return [];
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get items: ' . $e->getMessage();
            return [];
        }
    }
}
