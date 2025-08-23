<?php

declare(strict_types=1);

require_once CONTROLLER . '/PaymentsController.php';

return function ($app): void {
    $ctrl = new PaymentsController();

    $app->post('/v1/orders/{orderId}/payments', function ($request, $response, $args) use ($ctrl) {
        $orderId = (int) ($args['orderId'] ?? 0);
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $amount = (float) ($data['amount'] ?? 0.0);
        $method = (string) ($data['method'] ?? '');
        $txRef = (string) ($data['transaction_ref'] ?? '');
        $status = (string) ($data['status'] ?? 'pending');
        $result = $ctrl->createPayment($orderId, $amount, $method, $txRef, $status);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/v1/orders/{orderId}/payments', function ($request, $response, $args) use ($ctrl) {
        $orderId = (int) ($args['orderId'] ?? 0);
        $result = $ctrl->getByOrder($orderId);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->patch('/v1/payments/{id}/status', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $status = (string) ($data['status'] ?? '');
        $result = $ctrl->updateStatus($id, $status);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });
};
