<?php

declare(strict_types=1);

require_once MODEL . 'Payments.php';

class PaymentsController
{
    protected Payments $model;

    public function __construct()
    {
        $this->model = new Payments();
    }

    public function createPayment(int $orderId, float $amount, string $method, string $transactionRef = '', string $status = 'pending'): string
    {
        $id = $this->model->create($orderId, $amount, $method, $transactionRef, $status);
        if ($id === false) {
            return json_encode(['status' => 'error', 'message' => 'Failed to create payment: ' . $this->model->getLastError()], JSON_PRETTY_PRINT);
        }
        return json_encode(['status' => 'success', 'id' => $id, 'message' => 'Payment recorded'], JSON_PRETTY_PRINT);
    }

    public function getByOrder(int $orderId): string
    {
        $rows = $this->model->getByOrderId($orderId) ?: [];
        return json_encode(['status' => !empty($rows) ? 'success' : 'error', 'payments' => $rows, 'message' => empty($rows) ? 'No payments for order' : null], JSON_PRETTY_PRINT);
    }

    public function updateStatus(int $id, string $status): string
    {
        $ok = $this->model->updateStatus($id, $status);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Payment status updated' : ('Failed to update: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }
}
