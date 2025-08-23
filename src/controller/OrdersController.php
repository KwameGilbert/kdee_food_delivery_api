<?php

declare(strict_types=1);

require_once MODEL . 'Orders.php';

class OrdersController
{
    protected Orders $model;

    public function __construct()
    {
        $this->model = new Orders();
    }

    public function getOrder(int $id): string
    {
        $row = $this->model->getById($id);
        return json_encode(['status' => $row ? 'success' : 'error', 'order' => $row, 'message' => $row ? null : "Order not found: {$id}"], JSON_PRETTY_PRINT);
    }

    public function listByUser(int $userId): string
    {
        $rows = $this->model->getByUserId($userId) ?: [];
        return json_encode(['status' => !empty($rows) ? 'success' : 'error', 'orders' => $rows, 'message' => empty($rows) ? 'No orders' : null], JSON_PRETTY_PRINT);
    }

    public function createOrder(int $userId, int $addressId, float $totalAmount, float $deliveryFee = 0.00, string $status = 'pending'): string
    {
        $id = $this->model->create($userId, $addressId, $totalAmount, $deliveryFee, $status);
        if ($id === false) {
            return json_encode(['status' => 'error', 'message' => 'Failed to create order: ' . $this->model->getLastError()], JSON_PRETTY_PRINT);
        }
        $order = $this->model->getById((int)$id);
        return json_encode(['status' => 'success', 'order' => $order, 'message' => 'Order created'], JSON_PRETTY_PRINT);
    }

    public function updateStatus(int $orderId, string $status): string
    {
        $ok = $this->model->updateStatus($orderId, $status);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Order status updated' : ('Failed to update: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }
}
