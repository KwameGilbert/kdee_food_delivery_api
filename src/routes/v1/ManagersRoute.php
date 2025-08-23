<?php

declare(strict_types=1);

require_once CONTROLLER . '/ManagersController.php';

return function ($app): void {
    $ctrl = new ManagersController();

    $app->get('/v1/managers', function ($request, $response) use ($ctrl) {
        $result = $ctrl->listManagers();
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/v1/managers/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $result = $ctrl->getManager($id);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/v1/managers', function ($request, $response) use ($ctrl) {
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $result = $ctrl->createManager($data);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->patch('/v1/managers/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $result = $ctrl->updateManager($id, $data);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->delete('/v1/managers/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $result = $ctrl->deleteManager($id);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/v1/managers/login', function ($request, $response) use ($ctrl) {
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $identifier = (string) ($data['username'] ?? $data['email'] ?? '');
        $password = (string) ($data['password'] ?? '');
        $result = $ctrl->login($identifier, $password);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });
};
