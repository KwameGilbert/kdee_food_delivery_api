<?php

declare(strict_types=1);

require_once MODEL . 'Categories.php';

class CategoriesController
{
    protected Categories $model;

    public function __construct()
    {
        $this->model = new Categories();
    }

    public function listCategories(): string
    {
        $rows = $this->model->getAll() ?: [];
        return json_encode(['status' => !empty($rows) ? 'success' : 'error', 'categories' => $rows, 'message' => empty($rows) ? 'No categories' : null], JSON_PRETTY_PRINT);
    }

    public function getCategory(int $id): string
    {
        $row = $this->model->getById($id);
        return json_encode(['status' => $row ? 'success' : 'error', 'category' => $row, 'message' => $row ? null : "Category not found: {$id}"], JSON_PRETTY_PRINT);
    }

    public function createCategory(array $data): string
    {
        $id = $this->model->create($data);
        if ($id === false) {
            return json_encode(['status' => 'error', 'message' => 'Failed to create category: ' . $this->model->getLastError()], JSON_PRETTY_PRINT);
        }
        $cat = $this->model->getById((int)$id);
        return json_encode(['status' => 'success', 'category' => $cat, 'message' => 'Category created'], JSON_PRETTY_PRINT);
    }

    public function updateCategory(int $id, array $data): string
    {
        $ok = $this->model->update($id, $data);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Category updated' : ('Failed to update: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }

    public function deleteCategory(int $id): string
    {
        $ok = $this->model->delete($id);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Category deleted' : ('Failed to delete: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }
}
