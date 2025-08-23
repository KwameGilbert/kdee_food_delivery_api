<?php

declare(strict_types=1);

require_once MODEL . 'Carts.php';

class CartsController
{
    protected Carts $model;

    public function __construct()
    {
        $this->model = new Carts();
    }

    public function createCartForUser(int $userId): string
    {
        $id = $this->model->create($userId);
        if ($id === false) {
            return json_encode(['status' => 'error', 'message' => 'Failed to create cart: ' . $this->model->getLastError()], JSON_PRETTY_PRINT);
        }
        $cart = $this->model->getById((int)$id);
        return json_encode(['status' => 'success', 'cart' => $cart, 'message' => 'Cart created'], JSON_PRETTY_PRINT);
    }

    public function getByUser(int $userId): string
    {
        $cart = $this->model->getByUserId($userId);
        return json_encode(['status' => $cart ? 'success' : 'error', 'cart' => $cart, 'message' => $cart ? null : 'No cart for user'], JSON_PRETTY_PRINT);
    }

    public function deleteCart(int $id): string
    {
        $ok = $this->model->delete($id);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Cart deleted' : ('Failed to delete cart: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }
}
