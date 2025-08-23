<?php

declare(strict_types=1);

require_once CONTROLLER . '/OrdersController.php';

return function ($app): void {
    $ctrl = new OrdersController();

    $app->get('/v1/orders/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $result = $ctrl->getOrder($id);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/v1/users/{userId}/orders', function ($request, $response, $args) use ($ctrl) {
        $userId = (int) ($args['userId'] ?? 0);
        $result = $ctrl->listByUser($userId);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/v1/orders', function ($request, $response) use ($ctrl) {
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $userId = (int) ($data['user_id'] ?? 0);
        $addressId = (int) ($data['address_id'] ?? 0);
        $totalAmount = (float) ($data['total_amount'] ?? 0.0);
        $deliveryFee = (float) ($data['delivery_fee'] ?? 0.0);
        $status = (string) ($data['status'] ?? 'pending');
        $result = $ctrl->createOrder($userId, $addressId, $totalAmount, $deliveryFee, $status);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->patch('/v1/orders/{id}/status', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $status = (string) ($data['status'] ?? '');
        $result = $ctrl->updateStatus($id, $status);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });
};
