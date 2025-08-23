<?php

declare(strict_types=1);

require_once CONFIG . 'Database.php';

/**
 * Foods model matching database/schema.sql
 *
 * Table: foods(id, category_id, name, description, price, image_url, created_at)
 */
class Foods
{
    /** @var PDO */
    protected PDO $db;

    /** @var string */
    private string $tableName = 'foods';

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
            $sql = "SELECT id, category_id, name, description, price, image_url, created_at FROM {$this->tableName} ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt)) {
                return [];
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get foods: ' . $e->getMessage();
            error_log($this->lastError);
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $sql = "SELECT id, category_id, name, description, price, image_url, created_at FROM {$this->tableName} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['id' => $id])) {
                return null;
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get food by ID: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    public function getByCategoryId(int $categoryId): array
    {
        try {
            $sql = "SELECT id, category_id, name, description, price, image_url, created_at FROM {$this->tableName} WHERE category_id = :category_id ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['category_id' => $categoryId])) {
                return [];
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get foods by category: ' . $e->getMessage();
            error_log($this->lastError);
            return [];
        }
    }

    public function getByName(string $name): ?array
    {
        try {
            $sql = "SELECT id, category_id, name, description, price, image_url, created_at FROM {$this->tableName} WHERE name = :name LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['name' => $name])) {
                return null;
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get food by name: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    /**
     * Create a new food item
     * @param array{category_id?:int,name:string,description?:string,price:string|float,image_url?:string} $data
     * @return int|false inserted id or false
     */
    public function create(array $data): int|false
    {
        try {
            $required = ['name', 'price'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $this->lastError = "Missing required field: {$field}";
                    return false;
                }
            }

            if (!is_numeric($data['price'])) {
                $this->lastError = 'Price must be a numeric value';
                return false;
            }

            $sql = "INSERT INTO {$this->tableName} (category_id, name, description, price, image_url, created_at) VALUES (:category_id, :name, :description, :price, :image_url, :created_at)";
            $stmt = $this->db->prepare($sql);

            $params = [
                'category_id' => isset($data['category_id']) ? (int)$data['category_id'] : null,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => number_format((float)$data['price'], 2, '.', ''),
                'image_url' => $data['image_url'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if (!$this->executeQuery($stmt, $params)) {
                return false;
            }

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = 'Failed to create food: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            if (!$this->getById($id)) {
                $this->lastError = 'Food not found';
                return false;
            }

            $allowed = ['category_id', 'name', 'description', 'price', 'image_url'];
            $sets = [];
            $params = ['id' => $id];

            foreach ($data as $k => $v) {
                if (in_array($k, $allowed, true)) {
                    if ($k === 'price' && $v !== null && !is_numeric($v)) {
                        $this->lastError = 'Price must be numeric';
                        return false;
                    }
                    $sets[] = "$k = :$k";
                    if ($k === 'price') {
                        $params[$k] = number_format((float)$v, 2, '.', '');
                    } else {
                        $params[$k] = $v;
                    }
                }
            }

            if (empty($sets)) {
                $this->lastError = 'No valid fields provided for update.';
                return false;
            }

            $sql = 'UPDATE ' . $this->tableName . ' SET ' . implode(', ', $sets) . ' WHERE id = :id';
            $stmt = $this->db->prepare($sql);
            return $this->executeQuery($stmt, $params);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to update food: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            if (!$this->getById($id)) {
                $this->lastError = 'Food not found';
                return false;
            }
            $stmt = $this->db->prepare("DELETE FROM {$this->tableName} WHERE id = :id");
            return $this->executeQuery($stmt, ['id' => $id]);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to delete food: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }
}
