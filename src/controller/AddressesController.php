<?php

declare(strict_types=1);

require_once MODEL . 'Addresses.php';

class AddressesController
{
    protected Addresses $model;

    public function __construct()
    {
        $this->model = new Addresses();
    }

    public function listAddresses(): string
    {
        $rows = $this->model->getAll() ?? [];
        return json_encode(['status' => !empty($rows) ? 'success' : 'error', 'addresses' => $rows, 'message' => empty($rows) ? 'No addresses found' : null], JSON_PRETTY_PRINT);
    }

    public function getAddressById(int $id): string
    {
        $row = $this->model->getById($id);
        return json_encode(['status' => $row ? 'success' : 'error', 'address' => $row, 'message' => $row ? null : "Address not found with id {$id}"], JSON_PRETTY_PRINT);
    }

    public function getByUserId(int $userId): string
    {
        $rows = $this->model->getByUserId($userId) ?: [];
        return json_encode(['status' => !empty($rows) ? 'success' : 'error', 'addresses' => $rows, 'message' => empty($rows) ? 'No addresses for user' : null], JSON_PRETTY_PRINT);
    }

    public function createForUser(int $userId, array $data): string
    {
        $id = $this->model->create($userId, $data);
        if ($id === false) {
            return json_encode(['status' => 'error', 'message' => 'Failed to create address: ' . $this->model->getLastError()], JSON_PRETTY_PRINT);
        }
        $address = $this->model->getById((int)$id);
        return json_encode(['status' => 'success', 'address' => $address, 'message' => 'Address created'], JSON_PRETTY_PRINT);
    }

    public function updateAddress(int $id, array $data): string
    {
        $existing = $this->model->getById($id);
        if (!$existing) {
            return json_encode(['status' => 'error', 'message' => 'Address not found'], JSON_PRETTY_PRINT);
        }
        $ok = $this->model->update($id, $data);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Address updated' : ('Failed to update: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }

    public function deleteAddress(int $id): string
    {
        $existing = $this->model->getById($id);
        if (!$existing) {
            return json_encode(['status' => 'error', 'message' => 'Address not found'], JSON_PRETTY_PRINT);
        }
        $ok = $this->model->delete($id);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Address deleted' : ('Failed to delete: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }
}
