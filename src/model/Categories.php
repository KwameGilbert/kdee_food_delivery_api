<?php

declare(strict_types=1);

require_once CONFIG . 'Database.php';

/**
 * Categories model matching database/schema.sql
 *
 * Table: categories(id, name, description)
 */
class Categories
{
    /** @var PDO */
    protected PDO $db;

    /** @var string */
    private string $tableName = 'categories';

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
            $sql = "SELECT id, name, description FROM {$this->tableName} ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt)) {
                return [];
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get categories: ' . $e->getMessage();
            error_log($this->lastError);
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $sql = "SELECT id, name, description FROM {$this->tableName} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['id' => $id])) {
                return null;
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get category by ID: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    public function getByName(string $name): ?array
    {
        try {
            $sql = "SELECT id, name, description FROM {$this->tableName} WHERE name = :name LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['name' => $name])) {
                return null;
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get category by name: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    /**
     * Create a new category
     * @param array{name:string,description?:string} $data
     * @return int|false inserted id or false
     */
    public function create(array $data): int|false
    {
        try {
            $required = ['name'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $this->lastError = "Missing required field: {$field}";
                    return false;
                }
            }

            if ($this->getByName($data['name'])) {
                $this->lastError = 'Category already exists with this name';
                return false;
            }

            $sql = "INSERT INTO {$this->tableName} (name, description) VALUES (:name, :description)";
            $stmt = $this->db->prepare($sql);

            $params = [
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
            ];

            if (!$this->executeQuery($stmt, $params)) {
                return false;
            }

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = 'Failed to create category: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            if (!$this->getById($id)) {
                $this->lastError = 'Category not found';
                return false;
            }

            $allowed = ['name', 'description'];
            $sets = [];
            $params = ['id' => $id];

            foreach ($data as $k => $v) {
                if (in_array($k, $allowed, true)) {
                    $sets[] = "$k = :$k";
                    $params[$k] = $v;
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
            $this->lastError = 'Failed to update category: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            if (!$this->getById($id)) {
                $this->lastError = 'Category not found';
                return false;
            }
            $stmt = $this->db->prepare("DELETE FROM {$this->tableName} WHERE id = :id");
            return $this->executeQuery($stmt, ['id' => $id]);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to delete category: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }
}
