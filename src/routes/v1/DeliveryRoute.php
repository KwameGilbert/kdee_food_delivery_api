<?php

declare(strict_types=1);

require_once CONTROLLER . '/DeliveryController.php';

return function ($app): void {
    $ctrl = new DeliveryController();

    $app->post('/v1/orders/{orderId}/delivery', function ($request, $response, $args) use ($ctrl) {
        $orderId = (int) ($args['orderId'] ?? 0);
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $name = (string) ($data['name'] ?? '');
        $phone = (string) ($data['phone'] ?? '');
        $result = $ctrl->assignDelivery($orderId, $name, $phone);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->patch('/v1/delivery/{id}/status', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $status = (string) ($data['status'] ?? '');
        $result = $ctrl->updateStatus($id, $status);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/v1/orders/{orderId}/delivery', function ($request, $response, $args) use ($ctrl) {
        $orderId = (int) ($args['orderId'] ?? 0);
        $result = $ctrl->getByOrder($orderId);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });
};
