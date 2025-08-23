<?php

declare(strict_types=1);

require_once MODEL . 'Delivery.php';

class DeliveryController
{
    protected Delivery $model;

    public function __construct()
    {
        $this->model = new Delivery();
    }

    public function assignDelivery(int $orderId, string $name, string $phone): string
    {
        $id = $this->model->assign($orderId, $name, $phone);
        if ($id === false) {
            return json_encode(['status' => 'error', 'message' => 'Failed to assign delivery: ' . $this->model->getLastError()], JSON_PRETTY_PRINT);
        }
        $row = $this->model->getByOrderId($orderId);
        return json_encode(['status' => 'success', 'delivery' => $row, 'message' => 'Delivery assigned'], JSON_PRETTY_PRINT);
    }

    public function updateStatus(int $id, string $status): string
    {
        $ok = $this->model->updateStatus($id, $status);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Delivery status updated' : ('Failed to update: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }

    public function getByOrder(int $orderId): string
    {
        $row = $this->model->getByOrderId($orderId);
        return json_encode(['status' => $row ? 'success' : 'error', 'delivery' => $row, 'message' => $row ? null : 'No delivery for order'], JSON_PRETTY_PRINT);
    }
}
