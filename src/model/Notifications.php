<?php

declare(strict_types=1);

require_once CONFIG . 'Database.php';
require_once MODEL . 'Users.php';

class Notifications
{
    protected PDO $db;
    private string $tableName = 'notifications';
    private string $lastError = '';

    public function __construct()
    {
        try {
            $this->db = (new Database())->getConnection();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }
    protected function executeQuery(\PDOStatement $stmt, array $params = []): bool
    {
        try {
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->lastError = 'Query failed: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function create(int $userId, string $title, string $message): int|false
    {
        try {
            try {
                $users = new Users();
            } catch (PDOException $e) {
                $this->lastError = 'Failed to init Users model';
                return false;
            }
            if (!$users->getById($userId)) {
                $this->lastError = 'User not found';
                return false;
            }
            $sql = "INSERT INTO {$this->tableName} (user_id, title, message, is_read, created_at) VALUES (:user_id, :title, :message, 0, :created_at)";
            $stmt = $this->db->prepare($sql);
            $params = ['user_id' => $userId, 'title' => $title, 'message' => $message, 'created_at' => date('Y-m-d H:i:s')];
            if (!$this->executeQuery($stmt, $params)) return false;
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = 'Failed to create notification: ' . $e->getMessage();
            return false;
        }
    }

    public function getByUserId(int $userId): array
    {
        try {
            $sql = "SELECT id, user_id, title, message, is_read, created_at FROM {$this->tableName} WHERE user_id = :user_id ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['user_id' => $userId])) return [];
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get notifications: ' . $e->getMessage();
            return [];
        }
    }

    public function markAsRead(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->tableName} SET is_read = 1 WHERE id = :id");
            return $this->executeQuery($stmt, ['id' => $id]);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to mark notification: ' . $e->getMessage();
            return false;
        }
    }
}
