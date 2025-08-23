<?php

declare(strict_types=1);

require_once CONFIG . 'Database.php';
require_once MODEL . 'Carts.php';
require_once MODEL . 'Foods.php';

/**
 * CartItems model matching database/schema.sql
 *
 * Table: cart_items(id, cart_id, food_id, quantity)
 */
class CartItems
{
    /** @var PDO */
    protected PDO $db;

    /** @var string */
    private string $tableName = 'cart_items';

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
            $sql = "SELECT id, cart_id, food_id, quantity FROM {$this->tableName} ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt)) {
                return [];
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get cart items: ' . $e->getMessage();
            error_log($this->lastError);
            return [];
        }
    }

    public function getById(int $id): ?array
    {
        try {
            $sql = "SELECT id, cart_id, food_id, quantity FROM {$this->tableName} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['id' => $id])) {
                return null;
            }
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get cart item by ID: ' . $e->getMessage();
            error_log($this->lastError);
            return null;
        }
    }

    public function getByCartId(int $cartId): array
    {
        try {
            // Ensure cart exists
            try {
                $cartsModel = new Carts();
            } catch (PDOException $e) {
                $this->lastError = 'Failed to initialise Carts model: ' . $e->getMessage();
                error_log($this->lastError);
                return [];
            }

            if (!$cartsModel->getById($cartId)) {
                $this->lastError = 'Cart not found';
                return [];
            }

            $sql = "SELECT id, cart_id, food_id, quantity FROM {$this->tableName} WHERE cart_id = :cart_id ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['cart_id' => $cartId])) {
                return [];
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            $this->lastError = 'Failed to get cart items by cart: ' . $e->getMessage();
            error_log($this->lastError);
            return [];
        }
    }

    /**
     * Add an item to a cart. If the same food already exists in the cart, increase quantity.
     * @param int $cartId
     * @param int $foodId
     * @param int $quantity
     * @return int|false inserted or updated row id, or false on failure
     */
    public function addItem(int $cartId, int $foodId, int $quantity = 1): int|false
    {
        try {
            if ($cartId <= 0 || $foodId <= 0 || $quantity <= 0) {
                $this->lastError = 'Invalid cart_id, food_id or quantity';
                return false;
            }
            // Validate referenced cart and food exist
            try {
                $cartsModel = new Carts();
                $foodsModel = new Foods();
            } catch (PDOException $e) {
                $this->lastError = 'Failed to initialise related models: ' . $e->getMessage();
                error_log($this->lastError);
                return false;
            }

            if (!$cartsModel->getById($cartId)) {
                $this->lastError = 'Cart not found';
                return false;
            }

            if (!$foodsModel->getById($foodId)) {
                $this->lastError = 'Food not found';
                return false;
            }

            // Check if item exists
            $sql = "SELECT id, quantity FROM {$this->tableName} WHERE cart_id = :cart_id AND food_id = :food_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$this->executeQuery($stmt, ['cart_id' => $cartId, 'food_id' => $foodId])) {
                return false;
            }
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $newQty = (int)$existing['quantity'] + $quantity;
                $updateSql = "UPDATE {$this->tableName} SET quantity = :quantity WHERE id = :id";
                $updateStmt = $this->db->prepare($updateSql);
                if (!$this->executeQuery($updateStmt, ['quantity' => $newQty, 'id' => (int)$existing['id']])) {
                    return false;
                }
                return (int)$existing['id'];
            }

            $insertSql = "INSERT INTO {$this->tableName} (cart_id, food_id, quantity) VALUES (:cart_id, :food_id, :quantity)";
            $insertStmt = $this->db->prepare($insertSql);
            if (!$this->executeQuery($insertStmt, ['cart_id' => $cartId, 'food_id' => $foodId, 'quantity' => $quantity])) {
                return false;
            }
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->lastError = 'Failed to add item to cart: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    /**
     * Update cart item quantity by item id
     */
    public function updateQuantity(int $id, int $quantity): bool
    {
        try {
            if ($quantity <= 0) {
                $this->lastError = 'Quantity must be positive';
                return false;
            }
            $item = $this->getById($id);
            if (!$item) {
                $this->lastError = 'Cart item not found';
                return false;
            }

            // Ensure the parent cart exists
            try {
                $cartsModel = new Carts();
            } catch (PDOException $e) {
                $this->lastError = 'Failed to initialise Carts model: ' . $e->getMessage();
                error_log($this->lastError);
                return false;
            }

            if (!$cartsModel->getById((int)$item['cart_id'])) {
                $this->lastError = 'Parent cart not found';
                return false;
            }
            $sql = "UPDATE {$this->tableName} SET quantity = :quantity WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $this->executeQuery($stmt, ['quantity' => $quantity, 'id' => $id]);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to update quantity: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $item = $this->getById($id);
            if (!$item) {
                $this->lastError = 'Cart item not found';
                return false;
            }

            // Ensure parent cart exists
            try {
                $cartsModel = new Carts();
            } catch (PDOException $e) {
                $this->lastError = 'Failed to initialise Carts model: ' . $e->getMessage();
                error_log($this->lastError);
                return false;
            }

            if (!$cartsModel->getById((int)$item['cart_id'])) {
                $this->lastError = 'Parent cart not found';
                return false;
            }

            $stmt = $this->db->prepare("DELETE FROM {$this->tableName} WHERE id = :id");
            return $this->executeQuery($stmt, ['id' => $id]);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to delete cart item: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function deleteByCartId(int $cartId): bool
    {
        try {
            // Ensure cart exists
            try {
                $cartsModel = new Carts();
            } catch (PDOException $e) {
                $this->lastError = 'Failed to initialise Carts model: ' . $e->getMessage();
                error_log($this->lastError);
                return false;
            }

            if (!$cartsModel->getById($cartId)) {
                $this->lastError = 'Cart not found';
                return false;
            }

            $stmt = $this->db->prepare("DELETE FROM {$this->tableName} WHERE cart_id = :cart_id");
            return $this->executeQuery($stmt, ['cart_id' => $cartId]);
        } catch (PDOException $e) {
            $this->lastError = 'Failed to delete cart items by cart: ' . $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }
}
