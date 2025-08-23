<?php

declare(strict_types=1);

require_once CONFIG . 'Database.php';

/**
 * Users Model aligned to database/schema.sql
 *
 * Tables used:
 * - users(user_id, role, username, email, password_hash, profile_image, created_at, updated_at)
 * - password_resets(reset_id, user_id, otp, expires_at, used)
 */
class Users
{
    /** @var PDO */
    protected PDO $db;

    /** @var string */
    private string $tableName = 'users';

    /** @var string */
    private string $lastError = '';

    public function __construct()
    {
        try {
            $database = new Database();
            $connection = $database->getConnection();
            if (!$connection) {
                throw new \PDOException('Database connection is null');
            }
            $this->db = $connection;
        } catch (\PDOException $e) {
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
        } catch (\PDOException $e) {
            $this->lastError = 'Query execution failed: ' . $e->getMessage();
            error_log($this->lastError . ' - SQL: ' . $statement->queryString);
            return false;
        }
    }

    public function getAll(): array
    {
        try {
            $sql = "SELECT user_id AS id, username, email, role, profile_image, created_at FROM {$this->tableName} ORDER BY user_id DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt)) {
                return [];
            }
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            $this->lastError = 'Failed to get users: ' . $e->getMessage();
            error_log($this->lastError);
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $sql = "SELECT user_id AS id, username, email, role, profile_image, created_at FROM {$this->tableName} WHERE user_id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['id' => $id])) {
                return null;
            }
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (\PDOException $e) {
            $this->lastError = 'Failed to get user by ID: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    public function getByEmail(string $email): ?array
    {
        try {
            $sql = "SELECT user_id AS id, username, email, role, profile_image, created_at FROM {$this->tableName} WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['email' => $email])) {
                return null;
            }
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (\PDOException $e) {
            $this->lastError = 'Failed to get user by email: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    public function getByUsername(string $username): ?array
    {
        try {
            $sql = "SELECT user_id AS id, username, email, role, profile_image, created_at FROM {$this->tableName} WHERE username = :username LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['username' => $username])) {
                return null;
            }
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (\PDOException $e) {
            $this->lastError = 'Failed to get user by username: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    /**
     * Create a new user
     * @param array{username:string,email:string,password:string,role?:string,profile_image?:string} $data
     * @return int|false Inserted id or false on failure
     */
    public function create(array $data): int|false
    {
        try {
            $required = ['username', 'email', 'password'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $this->lastError = "Missing required field: {$field}";
                    return false;
                }
            }

            if ($this->getByEmail($data['email']) || $this->getByUsername($data['username'])) {
                $this->lastError = 'User already exists with this email or username';
                return false;
            }

            $sql = "INSERT INTO {$this->tableName} (username, email, password_hash, role, profile_image, created_at) VALUES (:username, :email, :password_hash, :role, :profile_image, :created_at)";
            $stmt = $this->db->prepare($sql);

            $params = [
                'username'      => $data['username'],
                'email'         => $data['email'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role'          => $data['role'] ?? 'user',
                'profile_image' => $data['profile_image'] ?? null,
                'created_at'    => date('Y-m-d H:i:s'),
            ];

            if (!$this->executeQuery($stmt, $params)) {
                return false;
            }

            return (int) $this->db->lastInsertId();
        } catch (\PDOException $e) {
            $this->lastError = 'Failed to create user: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            if (!$this->getById($id)) {
                $this->lastError = 'User not found';
                return false;
            }

            $allowedFields = ['username', 'email', 'role', 'profile_image'];
            $sets = [];
            $params = ['id' => $id];

            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields, true)) {
                    $sets[] = "$key = :$key";
                    $params[$key] = $value;
                }
            }

            if (empty($sets)) {
                $this->lastError = 'No valid fields provided for update.';
                return false;
            }

            $sql = 'UPDATE ' . $this->tableName . ' SET ' . implode(', ', $sets) . ', updated_at = :updated_at WHERE user_id = :id';
            $params['updated_at'] = date('Y-m-d H:i:s');
            $stmt = $this->db->prepare($sql);
            return $this->executeQuery($stmt, $params);
        } catch (\PDOException $e) {
            $this->lastError = 'Failed to update user: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            if (!$this->getById($id)) {
                $this->lastError = 'User not found';
                return false;
            }
            $stmt = $this->db->prepare("DELETE FROM {$this->tableName} WHERE user_id = :id");
            return $this->executeQuery($stmt, ['id' => $id]);
        } catch (\PDOException $e) {
            $this->lastError = 'Failed to delete user: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function login(string $nameOrEmail, string $password): ?array
    {
        try {
            if ($nameOrEmail === '' || $password === '') {
                $this->lastError = 'Username/email and password are required';
                return null;
            }

            $stmt = $this->db->prepare("SELECT * FROM {$this->tableName} WHERE username = :username OR email = :email LIMIT 1");
            if (!$this->executeQuery($stmt, ['username' => $nameOrEmail, 'email' => $nameOrEmail])) {
                $this->lastError = 'Database error during login';
                return null;
            }

            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$user) {
                $this->lastError = 'User not found';
                return null;
            }

            if (!isset($user['password_hash']) || !password_verify($password, (string) $user['password_hash'])) {
                $this->lastError = 'Invalid password';
                return null;
            }

            if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
                $this->updatePassword((int) $user['user_id'], $password);
            }

            unset($user['password_hash']);
            return $user;
        } catch (\PDOException $e) {
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
            $stmt = $this->db->prepare("UPDATE {$this->tableName} SET password_hash = :password_hash, updated_at = :updated_at WHERE user_id = :id");
            return $this->executeQuery($stmt, [
                'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                'updated_at'    => date('Y-m-d H:i:s'),
                'id'            => $id,
            ]);
        } catch (\PDOException $e) {
            $this->lastError = 'Failed to update password: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function generateOtp(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }
}
