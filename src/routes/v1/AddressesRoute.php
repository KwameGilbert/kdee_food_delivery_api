<?php

declare(strict_types=1);

require_once CONTROLLER . '/AddressesController.php';

return function ($app): void {
    $ctrl = new AddressesController();

    $app->get('/v1/addresses', function ($request, $response) use ($ctrl) {
        $result = $ctrl->listAddresses();
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/v1/addresses/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $result = $ctrl->getAddressById($id);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/v1/users/{userId}/addresses', function ($request, $response, $args) use ($ctrl) {
        $userId = (int) ($args['userId'] ?? 0);
        $result = $ctrl->getByUserId($userId);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/v1/users/{userId}/addresses', function ($request, $response, $args) use ($ctrl) {
        $userId = (int) ($args['userId'] ?? 0);
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $result = $ctrl->createForUser($userId, $data);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->patch('/v1/addresses/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $result = $ctrl->updateAddress($id, $data);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->delete('/v1/addresses/{id}', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $result = $ctrl->deleteAddress($id);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });
};
