<?php

declare(strict_types=1);

require_once MODEL . 'OrderItems.php';

class OrderItemsController
{
    protected OrderItems $model;

    public function __construct()
    {
        $this->model = new OrderItems();
    }

    public function listByOrder(int $orderId): string
    {
        $rows = $this->model->getByOrderId($orderId) ?: [];
        return json_encode(['status' => !empty($rows) ? 'success' : 'error', 'items' => $rows, 'message' => empty($rows) ? 'No items for order' : null], JSON_PRETTY_PRINT);
    }

    public function addItem(int $orderId, int $foodId, int $quantity, float $price): string
    {
        $id = $this->model->addItem($orderId, $foodId, $quantity, $price);
        if ($id === false) {
            return json_encode(['status' => 'error', 'message' => 'Failed to add order item: ' . $this->model->getLastError()], JSON_PRETTY_PRINT);
        }
        return json_encode(['status' => 'success', 'id' => $id, 'message' => 'Order item added'], JSON_PRETTY_PRINT);
    }
}
