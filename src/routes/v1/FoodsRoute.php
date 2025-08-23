<?php

declare(strict_types=1);

require_once CONTROLLER . '/FoodsController.php';

return function ($app): void {
    $ctrl = new FoodsController();

    $app->get('/v1/foods', function ($request, $response) use ($ctrl) {
        $result = $ctrl->listFoods();
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/v1/foods/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $result = $ctrl->getFood($id);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/v1/categories/{categoryId}/foods', function ($request, $response, $args) use ($ctrl) {
        $categoryId = (int) ($args['categoryId'] ?? 0);
        $result = $ctrl->listByCategory($categoryId);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/v1/foods', function ($request, $response) use ($ctrl) {
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $result = $ctrl->createFood($data);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->patch('/v1/foods/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $result = $ctrl->updateFood($id, $data);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->delete('/v1/foods/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $result = $ctrl->deleteFood($id);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });
};
