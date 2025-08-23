<?php

declare(strict_types=1);

require_once MODEL . 'CartItems.php';

class CartItemsController
{
    protected CartItems $model;

    public function __construct()
    {
        $this->model = new CartItems();
    }

    public function listByCart(int $cartId): string
    {
        $items = $this->model->getByCartId($cartId) ?: [];
        return json_encode(['status' => !empty($items) ? 'success' : 'error', 'items' => $items, 'message' => empty($items) ? 'No items in cart' : null], JSON_PRETTY_PRINT);
    }

    public function addItem(int $cartId, int $foodId, int $quantity = 1): string
    {
        $id = $this->model->addItem($cartId, $foodId, $quantity);
        if ($id === false) {
            return json_encode(['status' => 'error', 'message' => 'Failed to add item: ' . $this->model->getLastError()], JSON_PRETTY_PRINT);
        }
        $item = $this->model->getById((int)$id);
        return json_encode(['status' => 'success', 'item' => $item, 'message' => 'Item added to cart'], JSON_PRETTY_PRINT);
    }

    public function updateQuantity(int $id, int $quantity): string
    {
        $ok = $this->model->updateQuantity($id, $quantity);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Quantity updated' : ('Failed to update: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }

    public function deleteItem(int $id): string
    {
        $ok = $this->model->delete($id);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Item deleted' : ('Failed to delete: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }

    public function clearCart(int $cartId): string
    {
        $ok = $this->model->deleteByCartId($cartId);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Cart cleared' : ('Failed to clear cart: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }
}
