<?php

declare(strict_types=1);

require_once CONTROLLER . '/NotificationsController.php';

return function ($app): void {
    $ctrl = new NotificationsController();

    $app->get('/v1/users/{userId}/notifications', function ($request, $response, $args) use ($ctrl) {
        $userId = (int) ($args['userId'] ?? 0);
        $result = $ctrl->listByUser($userId);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/v1/users/{userId}/notifications', function ($request, $response, $args) use ($ctrl) {
        $userId = (int) ($args['userId'] ?? 0);
        $data = json_decode((string) $request->getBody(), true) ?? [];
        $title = (string) ($data['title'] ?? '');
        $message = (string) ($data['message'] ?? '');
        $result = $ctrl->createNotification($userId, $title, $message);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->patch('/v1/notifications/{id}/read', function ($request, $response, $args) use ($ctrl) {
        $id = (int) ($args['id'] ?? 0);
        $result = $ctrl->markRead($id);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    });
};
