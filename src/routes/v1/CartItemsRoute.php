<?php

declare(strict_types=1);

require_once CONTROLLER . '/CartItemsController.php';

return function ($app): void {
    $ctrl = new CartItemsController();

    $app->get('/v1/carts/{cartId}/items', function ($request, $response, $args) use ($ctrl) {
        $cartId = (int) ($args['cartId'] ?? 0);
        $result = $ctrl->listByCart($cartId);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/v1/carts/{cartId}/items', function ($request, $response, $args) use ($ctrl) {
        $cartId = (int) ($args['cartId'] ?? 0);
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $foodId = (int) ($data['food_id'] ?? $data['foodId'] ?? 0);
        $quantity = (int) ($data['quantity'] ?? 1);
        $result = $ctrl->addItem($cartId, $foodId, $quantity);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->patch('/v1/cart-items/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $quantity = (int) ($data['quantity'] ?? 0);
        $result = $ctrl->updateQuantity($id, $quantity);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->delete('/v1/cart-items/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $result = $ctrl->deleteItem($id);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->delete('/v1/carts/{cartId}/items', function ($request, $response, $args) use ($ctrl) {
        $cartId = (int) ($args['cartId'] ?? 0);
        $result = $ctrl->clearCart($cartId);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });
};
