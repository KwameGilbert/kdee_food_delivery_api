<?php

declare(strict_types=1);

require_once CONTROLLER . '/OrderItemsController.php';

return function ($app): void {
    $ctrl = new OrderItemsController();

    $app->get('/v1/orders/{orderId}/items', function ($request, $response, $args) use ($ctrl) {
        $orderId = (int) ($args['orderId'] ?? 0);
        $result = $ctrl->listByOrder($orderId);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/v1/orders/{orderId}/items', function ($request, $response, $args) use ($ctrl) {
        $orderId = (int) ($args['orderId'] ?? 0);
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $foodId = (int) ($data['food_id'] ?? 0);
        $quantity = (int) ($data['quantity'] ?? 1);
        $price = (float) ($data['price'] ?? 0.0);
        $result = $ctrl->addItem($orderId, $foodId, $quantity, $price);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });
};
