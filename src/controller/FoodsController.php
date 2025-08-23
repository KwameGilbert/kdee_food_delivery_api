<?php

declare(strict_types=1);

require_once MODEL . 'Foods.php';

class FoodsController
{
    protected Foods $model;

    public function __construct()
    {
        $this->model = new Foods();
    }

    public function listFoods(): string
    {
        $rows = $this->model->getAll() ?: [];
        return json_encode(['status' => !empty($rows) ? 'success' : 'error', 'foods' => $rows, 'message' => empty($rows) ? 'No foods' : null], JSON_PRETTY_PRINT);
    }

    public function getFood(int $id): string
    {
        $row = $this->model->getById($id);
        return json_encode(['status' => $row ? 'success' : 'error', 'food' => $row, 'message' => $row ? null : "Food not found: {$id}"], JSON_PRETTY_PRINT);
    }

    public function listByCategory(int $categoryId): string
    {
        $rows = $this->model->getByCategoryId($categoryId) ?: [];
        return json_encode(['status' => !empty($rows) ? 'success' : 'error', 'foods' => $rows, 'message' => empty($rows) ? 'No foods for category' : null], JSON_PRETTY_PRINT);
    }

    public function createFood(array $data): string
    {
        $id = $this->model->create($data);
        if ($id === false) {
            return json_encode(['status' => 'error', 'message' => 'Failed to create food: ' . $this->model->getLastError()], JSON_PRETTY_PRINT);
        }
        $food = $this->model->getById((int)$id);
        return json_encode(['status' => 'success', 'food' => $food, 'message' => 'Food created'], JSON_PRETTY_PRINT);
    }

    public function updateFood(int $id, array $data): string
    {
        $ok = $this->model->update($id, $data);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Food updated' : ('Failed to update: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }

    public function deleteFood(int $id): string
    {
        $ok = $this->model->delete($id);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Food deleted' : ('Failed to delete: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }
}
