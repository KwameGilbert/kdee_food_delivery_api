<?php

declare(strict_types=1);

require_once MODEL . 'Notifications.php';

class NotificationsController
{
    protected Notifications $model;

    public function __construct()
    {
        $this->model = new Notifications();
    }

    public function listByUser(int $userId): string
    {
        $rows = $this->model->getByUserId($userId) ?: [];
        return json_encode(['status' => !empty($rows) ? 'success' : 'error', 'notifications' => $rows, 'message' => empty($rows) ? 'No notifications' : null], JSON_PRETTY_PRINT);
    }

    public function createNotification(int $userId, string $title, string $message): string
    {
        $id = $this->model->create($userId, $title, $message);
        if ($id === false) {
            return json_encode(['status' => 'error', 'message' => 'Failed to create notification: ' . $this->model->getLastError()], JSON_PRETTY_PRINT);
        }
        return json_encode(['status' => 'success', 'id' => $id, 'message' => 'Notification created'], JSON_PRETTY_PRINT);
    }

    public function markRead(int $id): string
    {
        $ok = $this->model->markAsRead($id);
        return json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Marked as read' : ('Failed to mark: ' . $this->model->getLastError())], JSON_PRETTY_PRINT);
    }
}
