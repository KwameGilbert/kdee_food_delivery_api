<?php

declare(strict_types=1);

require_once MODEL . 'Managers.php';

class ManagersController
{
    protected Managers $model;

    public function __construct()
    {
        $this->model = new Managers();
    }

    public function listManagers(): string
    {
        $rows = $this->model->getAll() ?: [];
        return json_encode(['status' => !empty($rows) ? 'success' : 'error', 'managers' => $rows, 'message' => empty($rows) ? 'No managers' : null], JSON_PRETTY_PRINT);
    }

    public function getManager(int $id): string
    {
        $row = $this->model->getById($id);
        return json_encode(['status' => $row ? 'success' : 'error', 'manager' => $row, 'message' => $row ? null : "Manager not found: {$id}"], JSON_PRETTY_PRINT);
    }

    public function createManager(array $data): string
    {
        $id = $this->model->create($data);
        if ($id === false) {
            return json_encode(['status' => 'error', 'message' => 'Failed to create manager: ' . $this->model->getLastError()], JSON_PRETTY_PRINT);
        }
        $row = $this->model->getById((int)$id);
        return json_encode(['status' => 'success', 'manager' => $row, 'message' => 'Manager created'], JSON_PRETTY_PRINT);
    }

    public function updateManager(int $id, array $data): string
    {
        $ok = $this->model->update($id, $data);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Manager updated' : ('Failed to update: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }

    public function deleteManager(int $id): string
    {
        $ok = $this->model->delete($id);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Manager deleted' : ('Failed to delete: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }

    public function login(string $identifier, string $password): string
    {
        $row = $this->model->login($identifier, $password);
        return json_encode(['status' => $row ? 'success' : 'error', 'manager' => $row, 'message' => $row ? 'Login successful' : ('Login failed: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }
}
