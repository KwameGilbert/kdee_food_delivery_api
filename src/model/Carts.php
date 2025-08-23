<?php

declare(strict_types=1);

require_once CONFIG . 'Database.php';

/**
 * Carts model matching database/schema.sql
 *
 * Table: carts(id, user_id, created_at)
 */
class Carts
{
    /** @var PDO */
    protected PDO $db;

    /** @var string */
    private string $tableName = 'carts';

    /** @var string */
    private string $lastError = '';

    public function __construct()
    {
        try {
            $database = new Database();
            $connection = $database->getConnection();
            if (!$connection) {
                throw new PDOException('Database connection is null');
            }
            $this->db = $connection;
        } catch (PDOException $e) {
            $this->lastError = 'Database connection failed: ' . $e->getMessage();
            error_log($this->lastError);
            throw $e;
        }
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    protected function executeQuery(\PDOStatement $statement, array $params = []): bool
    {
        try {
            return $statement->execute($params);
        } catch (PDOException $e) {
            $this->lastError = 'Query execution failed: ' . $e->getMessage();
            error_log($this->lastError . ' - SQL: ' . $statement->queryString);
            return false;
        }
    }

    public function getAll(): array
    {
        try {
            $sql = "SELECT id, user_id, created_at FROM {$this->tableName} ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt)) {
                return [];
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get carts: ' . $e->getMessage();
            error_log($this->lastError);
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $sql = "SELECT id, user_id, created_at FROM {$this->tableName} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['id' => $id])) {
                return null;
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get cart by ID: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    public function getByUserId(int $userId): array
    {
        try {
            $sql = "SELECT id, user_id, created_at FROM {$this->tableName} WHERE user_id = :user_id ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['user_id' => $userId])) {
                return [];
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get carts by user: ' . $e->getMessage();
            error_log($this->lastError);
            return [];
        }
    }

    /**
     * Create a new cart for a user
     * @param int $userId
     * @return int|false inserted id or false
     */
    public function create(int $userId): int|false
    {
        try {
            if ($userId <= 0) {
                $this->lastError = 'Invalid user id';
                return false;
            }

            $sql = "INSERT INTO {$this->tableName} (user_id, created_at) VALUES (:user_id, :created_at)";
            $stmt = $this->db->prepare($sql);
            $params = [
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if (!$this->executeQuery($stmt, $params)) {
                return false;
            }

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = 'Failed to create cart: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            if (!$this->getById($id)) {
                $this->lastError = 'Cart not found';
                return false;
            }
            $stmt = $this->db->prepare("DELETE FROM {$this->tableName} WHERE id = :id");
            return $this->executeQuery($stmt, ['id' => $id]);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to delete cart: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }
}
