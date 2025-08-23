<?php

declare(strict_types=1);

require_once CONFIG . 'Database.php';

/**
 * Managers model matching database/schema.sql
 *
 * Table: managers(id, name, email, password, phone, created_at)
 */
class Managers
{
    /** @var PDO */
    protected PDO $db;

    /** @var string */
    private string $tableName = 'managers';

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
            $sql = "SELECT id, name, email, phone, created_at FROM {$this->tableName} ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt)) {
                return [];
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get managers: ' . $e->getMessage();
            error_log($this->lastError);
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $sql = "SELECT id, name, email, phone, created_at FROM {$this->tableName} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['id' => $id])) {
                return null;
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get manager by ID: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    public function getByEmail(string $email): ?array
    {
        try {
            $sql = "SELECT id, name, email, phone, created_at FROM {$this->tableName} WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['email' => $email])) {
                return null;
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get manager by email: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    public function getByName(string $name): ?array
    {
        try {
            $sql = "SELECT id, name, email, phone, created_at FROM {$this->tableName} WHERE name = :name LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['name' => $name])) {
                return null;
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get manager by name: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    /**
     * Create a new manager
     * @param array{name:string,email:string,password:string,phone?:string} $data
     * @return int|false inserted id or false
     */
    public function create(array $data): int|false
    {
        try {
            $required = ['name', 'email', 'password'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $this->lastError = "Missing required field: {$field}";
                    return false;
                }
            }

            if ($this->getByEmail($data['email']) || $this->getByName($data['name'])) {
                $this->lastError = 'Manager already exists with this email or name';
                return false;
            }

            $sql = "INSERT INTO {$this->tableName} (name, email, password, phone, created_at) VALUES (:name, :email, :password, :phone, :created_at)";
            $stmt = $this->db->prepare($sql);

            $params = [
                'name'       => $data['name'],
                'email'      => $data['email'],
                'password'   => password_hash($data['password'], PASSWORD_DEFAULT),
                'phone'      => $data['phone'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if (!$this->executeQuery($stmt, $params)) {
                return false;
            }

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = 'Failed to create manager: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            if (!$this->getById($id)) {
                $this->lastError = 'Manager not found';
                return false;
            }

            $allowed = ['name', 'email', 'phone'];
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
            $this->lastError = 'Failed to update manager: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            if (!$this->getById($id)) {
                $this->lastError = 'Manager not found';
                return false;
            }
            $stmt = $this->db->prepare("DELETE FROM {$this->tableName} WHERE id = :id");
            return $this->executeQuery($stmt, ['id' => $id]);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to delete manager: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    /**
     * Authenticate manager by name or email
     */
    public function login(string $nameOrEmail, string $password): ?array
    {
        try {
            if ($nameOrEmail === '' || $password === '') {
                $this->lastError = 'Name/email and password are required';
                return null;
            }

            $stmt = $this->db->prepare("SELECT * FROM {$this->tableName} WHERE name = :name OR email = :email LIMIT 1");
            if (!$this->executeQuery($stmt, ['name' => $nameOrEmail, 'email' => $nameOrEmail])) {
                $this->lastError = 'Database error during login';
                return null;
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                $this->lastError = 'Manager not found';
                return null;
            }

            if (!isset($user['password']) || !password_verify($password, (string) $user['password'])) {
                $this->lastError = 'Invalid password';
                return null;
            }

            if (password_needs_rehash((string) $user['password'], PASSWORD_DEFAULT)) {
                $this->updatePassword((int) $user['id'], $password);
            }

            unset($user['password']);
            return $user;
        } catch (PDOException $e) {
            $this->lastError = 'Login failed: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        try {
            if (strlen($newPassword) < 8) {
                $this->lastError = 'Password must be at least 8 characters';
                return false;
            }
            $stmt = $this->db->prepare("UPDATE {$this->tableName} SET password = :password WHERE id = :id");
            return $this->executeQuery($stmt, [
                'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                'id' => $id,
            ]);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to update password: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }
}
