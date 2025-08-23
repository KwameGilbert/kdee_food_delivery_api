<?php

declare(strict_types=1);

require_once CONTROLLER . '/CategoriesController.php';

return function ($app): void {
    $ctrl = new CategoriesController();

    $app->get('/v1/categories', function ($request, $response) use ($ctrl) {
        $result = $ctrl->listCategories();
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/v1/categories/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $result = $ctrl->getCategory($id);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/v1/categories', function ($request, $response) use ($ctrl) {
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $result = $ctrl->createCategory($data);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->patch('/v1/categories/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $result = $ctrl->updateCategory($id, $data);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->delete('/v1/categories/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $result = $ctrl->deleteCategory($id);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });
};
