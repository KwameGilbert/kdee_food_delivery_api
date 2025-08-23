<?php

declare(strict_types=1);

require_once CONTROLLER . '/CartsController.php';

return function ($app): void {
    $ctrl = new CartsController();

    $app->post('/v1/users/{userId}/cart', function ($request, $response, $args) use ($ctrl) {
        $userId = (int) ($args['userId'] ?? 0);
        $result = $ctrl->createCartForUser($userId);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/v1/users/{userId}/cart', function ($request, $response, $args) use ($ctrl) {
        $userId = (int) ($args['userId'] ?? 0);
        $result = $ctrl->getByUser($userId);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->delete('/v1/carts/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $result = $ctrl->deleteCart($id);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });
};
