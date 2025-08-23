<?php

declare(strict_types=1);

require_once CONFIG . 'Database.php';
require_once MODEL . 'Users.php';

class Addresses
{
    protected PDO $db;
    private string $tableName = 'addresses';
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

    public function getById(int $id): ?array
    {
        try {
            $sql = "SELECT id, user_id, name, contact_number, address_line, landmark, latitude, longitude, is_default, created_at FROM {$this->tableName} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['id' => $id])) return null;
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get address: ' . $e->getMessage();
            return null;
        }
    }

    public function getByUserId(int $userId): array
    {
        try {
            $sql = "SELECT id, user_id, name, contact_number, address_line, landmark, latitude, longitude, is_default, created_at FROM {$this->tableName} WHERE user_id = :user_id ORDER BY is_default DESC, id DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['user_id' => $userId])) return [];
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get addresses: ' . $e->getMessage();
            return [];
        }
    }

    public function getAll(): array
    {
        try {
            $sql = "SELECT id, user_id, name, contact_number, address_line, landmark, latitude, longitude, is_default, created_at FROM {$this->tableName} ORDER BY is_default DESC, id DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt)) return [];
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get addresses: ' . $e->getMessage();
            return [];
        }
    }

    public function create(int $userId, array $data): int|false
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
            $sql = "INSERT INTO {$this->tableName} (user_id, name, contact_number, address_line, landmark, latitude, longitude, is_default, created_at) VALUES (:user_id, :name, :contact_number, :address_line, :landmark, :latitude, :longitude, :is_default, :created_at)";
            $stmt = $this->db->prepare($sql);
            $params = [
                'user_id' => $userId,
                'name' => $data['name'] ?? null,
                'contact_number' => $data['contact_number'] ?? null,
                'address_line' => $data['address_line'] ?? null,
                'landmark' => $data['landmark'] ?? null,
                'latitude' => isset($data['latitude']) ? (float)$data['latitude'] : null,
                'longitude' => isset($data['longitude']) ? (float)$data['longitude'] : null,
                'is_default' => isset($data['is_default']) ? (int)(bool)$data['is_default'] : 0,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            if (!$this->executeQuery($stmt, $params)) return false;
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = 'Failed to create address: ' . $e->getMessage();
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            if (!$this->getById($id)) {
                $this->lastError = 'Address not found';
                return false;
            }
            $allowed = ['name', 'contact_number', 'address_line', 'landmark', 'latitude', 'longitude', 'is_default'];
            $sets = [];
            $params = ['id' => $id];
            foreach ($data as $k => $v) {
                if (in_array($k, $allowed, true)) {
                    $sets[] = "$k = :$k";
                    $params[$k] = $v;
                }
            }
            if (empty($sets)) {
                $this->lastError = 'No valid fields';
                return false;
            }
            $sql = 'UPDATE ' . $this->tableName . ' SET ' . implode(', ', $sets) . ' WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            return $this->executeQuery($stmt, $params);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to update address: ' . $e->getMessage();
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            if (!$this->getById($id)) {
                $this->lastError = 'Address not found';
                return false;
            }
            $stmt = $this->db->prepare("DELETE FROM {$this->tableName} WHERE id = :id");
            return $this->executeQuery($stmt, ['id' => $id]);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to delete address: ' . $e->getMessage();
            return false;
        }
    }
}
