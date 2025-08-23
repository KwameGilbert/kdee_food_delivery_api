# API Documentation

## Users

### Controller: UsersController — Routes in UsersRoute.php

#### List users
- **Method**: GET
- **Path**: `/v1/users`
- **Description**: Return list of users
- **Request**: None
- **Success**:
  ```json
  {
    "status": "success",
    "users": [
      {
        "id": 1,
        "username": "alice",
        "email": "a@x.com",
        "role": "user",
        "profile_image": null,
        "created_at": "2025-08-21 10:00:00"
      },
      ...
    ],
    "message": null
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "users": [],
    "message": "No users found"
  }
  ```

#### Get user by id
- **Method**: GET
- **Path**: `/v1/users/{id}`
- **Path params**: `id` (int)
- **Success**:
  ```json
  {
    "status": "success",
    "user": {
      "id": 1,
      "username": "alice",
      "email": "a@x.com",
      "role": "user",
      "profile_image": null,
      "created_at": "..."
    },
    "message": null
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "user": null,
    "message": "User not found with id {id}"
  }
  ```

#### Get user by email
- **Method**: GET
- **Path**: `/v1/users/email/{email}`
- **Success / Error**: Similar to "Get user by id", field `user` returned.

#### Get user by username
- **Method**: GET
- **Path**: `/v1/users/username/{username}`
- **Success / Error**: Similar to "Get user by id".

#### Create user
- **Method**: POST
- **Path**: `/v1/users`
- **Body (JSON)**:
  ```json
  {
    "username": "alice",
    "email": "alice@example.com",
    "password": "P@ssw0rd",
    "role": "admin" | "officer" | "user", // optional
    "profile_image": "https://.../img.jpg" // optional
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "user": {
      "id": 12,
      "username": "alice",
      "email": "alice@example.com",
      "role": "user",
      "profile_image": null,
      "created_at": "2025-08-23 12:00:00"
    },
    "message": "User created successfully"
  }
  ```
- **Error (missing fields)**:
  ```json
  {
    "status": "error",
    "message": "Missing required fields: username, email"
  }
  ```
- **Error (duplicate)**:
  ```json
  {
    "status": "error",
    "field": "email",
    "message": "Email already in use by another account"
  }
  ```
- **Notes**: Role defaults to "user" if not provided. Password is hashed server-side.

#### Update user (partial)
- **Method**: PATCH
- **Path**: `/v1/users/{id}`
- **Body (JSON)**: Allowed fields:
  ```json
  {
    "username": "...",
    "email": "...",
    "role": "admin|officer|user",
    "profile_image": "..."
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "user": { ...updated user... },
    "message": "User updated successfully"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "message": "Failed to update user: <reason>"
  }
  ```

#### Delete user
- **Method**: DELETE
- **Path**: `/v1/users/{id}`
- **Success**:
  ```json
  {
    "status": "success",
    "message": "User deleted successfully"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "message": "Failed to delete user: <reason>"
  }
  ```

#### Login
- **Method**: POST
- **Path**: `/v1/users/login`
- **Body**:
  ```json
  {
    "username": "alice" // OR
    "email": "alice@x.com",
    "password": "P@ssw0rd"
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "user": {
      "id": 1,
      "username": "alice",
      "email": "a@x.com",
      "role": "user",
      "profile_image": null,
      "created_at": "..."
    },
    "message": "Login successful"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "user": null,
    "message": "Login failed: Invalid password"
  }
  ```
- **Notes**: Controller returns user record without password hash. Use returned user data to create session/JWT (not implemented here).

#### Request password reset (OTP)
- **Method**: POST
- **Path**: `/v1/users/password/request-reset`
- **Body**:
  ```json
  {
    "email": "alice@example.com",
    "ttl_minutes": 15 // optional
  }
  ```
- **Response**:
  ```json
  {
    "status": "error",
    "message": "Password reset via OTP is not implemented in the Users model. Implement setOtpCode/findByOtpCode/markOtpAsUsed on the model."
  }
  ```
- **Note**: Backend model does not persist OTPs yet. Frontend should treat this endpoint as not available until model is implemented.

#### Verify OTP, Reset with OTP
- **Paths**: `/v1/users/password/verify-otp` and `/v1/users/password/reset`
- **Body/behavior**: Same note as "Request password reset (OTP)". Controller returns error explaining OTP persistence missing.

#### Update password with current-password confirmation
- **Method**: POST
- **Path**: `/v1/users/password/update`
- **Body**:
  ```json
  {
    "user_id": 1,
    "current_password": "old",
    "new_password": "newStrongPwd"
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Password updated successfully"
  }
  ```
- **Error (wrong current)**:
  ```json
  {
    "status": "error",
    "message": "Current password is incorrect"
  }
  ```
- **Notes**: Controller verifies current password with login() then calls updatePassword().

---

## Addresses

### Controller: AddressesController — Routes in AddressesRoute.php

#### List all addresses
- **Method**: GET
- **Path**: `/v1/addresses`
- **Success**:
  ```json
  {
    "status": "success",
    "addresses": [
      {
        "id": 1,
        "user_id": 2,
        "name": "Home",
        "contact_number": "024...",
        "address_line": "Street 1",
        "landmark": null,
        "latitude": null,
        "longitude": null,
        "is_default": 1,
        "created_at": "2025-08-..."
      },
      ...
    ]
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "addresses": [],
    "message": "No addresses found"
  }
  ```

#### Get address by id
- **Method**: GET
- **Path**: `/v1/addresses/{id}`
- **Success**:
  ```json
  {
    "status": "success",
    "address": { ... },
    "message": null
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "address": null,
    "message": "Address not found with id {id}"
  }
  ```

#### Get addresses for user
- **Method**: GET
- **Path**: `/v1/users/{userId}/addresses`
- **Success**: Addresses array or empty array + message.

#### Create address for user
- **Method**: POST
- **Path**: `/v1/users/{userId}/addresses`
- **Body**:
  ```json
  {
    "name": "Home",
    "contact_number": "024...",
    "address_line": "12 Baker St",
    "landmark": "Near Mall",
    "latitude": 5.6037,
    "longitude": -0.1870,
    "is_default": 1
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "address": {
      "id": 123,
      "user_id": 5,
      ...
    },
    "message": "Address created"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "message": "Failed to create address: <reason>"
  }
  ```

#### Update address (partial)
- **Method**: PATCH
- **Path**: `/v1/addresses/{id}`
- **Body**: Any of allowed fields: `name`, `contact_number`, `address_line`, `landmark`, `latitude`, `longitude`, `is_default`.
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Address updated"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "message": "Address not found"
  }
  ```

#### Delete address
- **Method**: DELETE
- **Path**: `/v1/addresses/{id}`
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Address deleted"
  }
  ```

---

## Carts

### Controller: CartsController — Routes in CartsRoute.php

#### Create cart for user
- **Method**: POST
- **Path**: `/v1/users/{userId}/cart`
- **Body**: None
- **Success**:
  ```json
  {
    "status": "success",
    "cart": {
      "id": 5,
      "user_id": 3,
      "created_at": "..."
    },
    "message": "Cart created"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "message": "Failed to create cart: <reason>"
  }
  ```

#### Get user's cart
- **Method**: GET
- **Path**: `/v1/users/{userId}/cart`
- **Success**:
  ```json
  {
    "status": "success",
    "cart": {
      "id": 5,
      "user_id": 3,
      ...
    },
    "message": null
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "cart": null,
    "message": "No cart for user"
  }
  ```

#### Delete cart
- **Method**: DELETE
- **Path**: `/v1/carts/{id}`
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Cart deleted"
  }
  ```

---

## Cart Items

### Controller: CartItemsController — Routes in CartItemsRoute.php

#### List items in cart
- **Method**: GET
- **Path**: `/v1/carts/{cartId}/items`
- **Success**:
  ```json
  {
    "status": "success",
    "items": [
      {
        "id": 1,
        "cart_id": 5,
        "food_id": 12,
        "quantity": 2
      },
      ...
    ],
    "message": null
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "items": [],
    "message": "No items in cart"
  }
  ```
  or cart-not-found specifics via model lastError.

#### Add item to cart
- **Method**: POST
- **Path**: `/v1/carts/{cartId}/items`
- **Body**:
  ```json
  {
    "food_id": 12,
    "quantity": 2 // quantity optional defaults to 1
  }
  ```
- **Success** (if added or incremented):
  ```json
  {
    "status": "success",
    "item": {
      "id": 34,
      "cart_id": 5,
      "food_id": 12,
      "quantity": 3
    },
    "message": "Item added to cart"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "message": "Failed to add item: Cart not found"
  }
  ```
- **Notes**: If same food already in cart, the model increases quantity and returns existing item id.

#### Update item quantity
- **Method**: PATCH
- **Path**: `/v1/cart-items/{id}`
- **Body**:
  ```json
  {
    "quantity": 5
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Quantity updated"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "message": "Failed to update: <reason>"
  }
  ```
  (e.g., parent cart missing, invalid quantity)

#### Delete cart item
- **Method**: DELETE
- **Path**: `/v1/cart-items/{id}`
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Item deleted"
  }
  ```

#### Clear cart
- **Method**: DELETE
- **Path**: `/v1/carts/{cartId}/items`
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Cart cleared"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "message": "Failed to clear cart: <reason>"
  }
  ```

---

## Categories

### Controller: CategoriesController — Routes in CategoriesRoute.php

#### List categories
- **Method**: GET
- **Path**: `/v1/categories`
- **Success**:
  ```json
  {
    "status": "success",
    "categories": [
      {
        "id": 1,
        "name": "Pizza",
        "description": "..."
      },
      ...
    ]
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "categories": [],
    "message": "No categories"
  }
  ```

#### Get category
- **Method**: GET
- **Path**: `/v1/categories/{id}`
- **Success**:
  ```json
  {
    "status": "success",
    "category": { ... }
  }
  ```

#### Create category
- **Method**: POST
- **Path**: `/v1/categories`
- **Body**:
  ```json
  {
    "name": "Pizza",
    "description": "..."
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "category": { ... },
    "message": "Category created"
  }
  ```

#### Update category
- **Method**: PATCH
- **Path**: `/v1/categories/{id}`
- **Body**:
  ```json
  {
    "name": "...",
    "description": "..."
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Category updated"
  }
  ```

#### Delete category
- **Method**: DELETE
- **Path**: `/v1/categories/{id}`
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Category deleted"
  }
  ```

---

## Foods

### Controller: FoodsController — Routes in FoodsRoute.php

#### List foods
- **Method**: GET
- **Path**: `/v1/foods`
- **Success**:
  ```json
  {
    "status": "success",
    "foods": [
      {
        "id": 1,
        "name": "Margherita",
        "category_id": 2,
        "price": 7.50,
        "description": "...",
        "image": null
      },
      ...
    ]
  }
  ```

#### Get food
- **Method**: GET
- **Path**: `/v1/foods/{id}`
- **Success**:
  ```json
  {
    "status": "success",
    "food": { ... }
  }
  ```

#### List foods by category
- **Method**: GET
- **Path**: `/v1/categories/{categoryId}/foods`
- **Success**: Foods array.

#### Create food
- **Method**: POST
- **Path**: `/v1/foods`
- **Body**:
  ```json
  {
    "name": "Margherita",
    "category_id": 2,
    "price": 7.5,
    "description": "...",
    "image": "https://..."
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "food": { ...created... },
    "message": "Food created"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "message": "Failed to create food: <reason>"
  }
  ```

#### Update food
- **Method**: PATCH
- **Path**: `/v1/foods/{id}`
- **Body**: Any of `{ name, category_id, price, description, image }`
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Food updated"
  }
  ```

#### Delete food
- **Method**: DELETE
- **Path**: `/v1/foods/{id}`
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Food deleted"
  }
  ```

---

## Orders

### Controller: OrdersController — Routes in OrdersRoute.php

#### Get order
- **Method**: GET
- **Path**: `/v1/orders/{id}`
- **Success**:
  ```json
  {
    "status": "success",
    "order": {
      "id": 101,
      "user_id": 5,
      "address_id": 12,
      "status": "pending",
      "total_amount": 45.00,
      "delivery_fee": 3.00,
      "created_at": "..."
    }
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "order": null,
    "message": "Order not found: {id}"
  }
  ```

#### List orders by user
- **Method**: GET
- **Path**: `/v1/users/{userId}/orders`
- **Success**: Orders array.

#### Create order (order header)
- **Method**: POST
- **Path**: `/v1/orders`
- **Body**:
  ```json
  {
    "user_id": 5,
    "address_id": 12,
    "total_amount": 45.00,
    "delivery_fee": 3.00, // optional; defaults to 0.00
    "status": "pending" // optional
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "order": {
      "id": 101,
      "user_id": 5,
      "address_id": 12,
      "status": "pending",
      "total_amount": 45.00,
      "delivery_fee": 3.00,
      "created_at": "..."
    },
    "message": "Order created"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "message": "Failed to create order: User not found"
  }
  ```
- **Notes**: This creates only the order header. Use OrderItems endpoints to add items.

#### Update order status
- **Method**: PATCH
- **Path**: `/v1/orders/{id}/status`
- **Body**:
  ```json
  {
    "status": "confirmed" | "preparing" | "out_for_delivery" | "delivered" | "cancelled"
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Order status updated"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "message": "Invalid status"
  }
  ```

---

## Order Items

### Controller: OrderItemsController — Routes in OrderItemsRoute.php

#### List order items
- **Method**: GET
- **Path**: `/v1/orders/{orderId}/items`
- **Success**:
  ```json
  {
    "status": "success",
    "items": [
      {
        "id": 1,
        "order_id": 101,
        "food_id": 12,
        "quantity": 2,
        "price": 7.50
      },
      ...
    ]
  }
  ```

#### Add item to order
- **Method**: POST
- **Path**: `/v1/orders/{orderId}/items`
- **Body**:
  ```json
  {
    "food_id": 12,
    "quantity": 2,
    "price": 7.50
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "id": 201,
    "message": "Order item added"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "message": "Failed to add order item: <reason>"
  }
  ```

---

## Payments

### Controller: PaymentsController — Routes in PaymentsRoute.php

#### Create payment (record)
- **Method**: POST
- **Path**: `/v1/orders/{orderId}/payments`
- **Body**:
  ```json
  {
    "amount": 45.00,
    "method": "card" | "mobile_money" | "cash",
    "transaction_ref": "tx123",
    "status": "pending"
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "id": 301,
    "message": "Payment recorded"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "message": "Failed to create payment: <reason>"
  }
  ```

#### Get payments for order
- **Method**: GET
- **Path**: `/v1/orders/{orderId}/payments`
- **Success**:
  ```json
  {
    "status": "success",
    "payments": [
      {
        "id": 301,
        "order_id": 101,
        "amount": 45.00,
        "method": "card",
        "transaction_ref": "tx123",
        "status": "pending",
        "created_at": "..."
      }
    ]
  }
  ```

#### Update payment status
- **Method**: PATCH
- **Path**: `/v1/payments/{id}/status`
- **Body**:
  ```json
  {
    "status": "completed" | "failed" | "pending"
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Payment status updated"
  }
  ```

---

## Delivery

### Controller: DeliveryController — Routes in DeliveryRoute.php

#### Assign delivery to order
- **Method**: POST
- **Path**: `/v1/orders/{orderId}/delivery`
- **Body**:
  ```json
  {
    "name": "Rider Joe",
    "phone": "024..."
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "delivery": {
      "id": 401,
      "order_id": 101,
      "name": "Rider Joe",
      "phone": "024...",
      "status": "assigned"
    },
    "message": "Delivery assigned"
  }
  ```
- **Error**:
  ```json
  {
    "status": "error",
    "message": "Failed to assign delivery: <reason>"
  }
  ```

#### Update delivery status
- **Method**: PATCH
- **Path**: `/v1/delivery/{id}/status`
- **Body**:
  ```json
  {
    "status": "assigned" | "in_transit" | "delivered" | "cancelled"
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Delivery status updated"
  }
  ```

#### Get delivery by order
- **Method**: GET
- **Path**: `/v1/orders/{orderId}/delivery`
- **Success**:
  ```json
  {
    "status": "success",
    "delivery": { ... }
  }
  ```

---

## Managers

### Controller: ManagersController — Routes in ManagersRoute.php

#### List managers
- **Method**: GET
- **Path**: `/v1/managers`
- **Success**:
  ```json
  {
    "status": "success",
    "managers": [ { ... }, ... ]
  }
  ```

#### Get manager
- **Method**: GET
- **Path**: `/v1/managers/{id}`
- **Success**: Manager object.

#### Create manager
- **Method**: POST
- **Path**: `/v1/managers`
- **Body**: Similar to users (`username`, `email`, `password`, `role`, ...)
- **Success**:
  ```json
  {
    "status": "success",
    "manager": { ... },
    "message": "Manager created"
  }
  ```

#### Update / Delete manager
- **Method**: PATCH / DELETE
- **Path**: `/v1/managers/{id}`
- **Success / Error**: As per other CRUD endpoints.

#### Manager login
- **Method**: POST
- **Path**: `/v1/managers/login`
- **Body**:
  ```json
  {
    "username": "or email",
    "password": "..."
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "manager": { ... },
    "message": "Login successful"
  }
  ```

---

## Notifications

### Controller: NotificationsController — Routes in NotificationsRoute.php

#### List user notifications
- **Method**: GET
- **Path**: `/v1/users/{userId}/notifications`
- **Success**:
  ```json
  {
    "status": "success",
    "notifications": [
      {
        "id": 1,
        "user_id": 5,
        "title": "Order #101",
        "message": "Your order is out for delivery",
        "read": 0,
        "created_at": "..."
      },
      ...
    ]
  }
  ```

#### Create notification
- **Method**: POST
- **Path**: `/v1/users/{userId}/notifications`
- **Body**:
  ```json
  {
    "title": "Order update",
    "message": "Your order was confirmed"
  }
  ```
- **Success**:
  ```json
  {
    "status": "success",
    "id": 501,
    "message": "Notification created"
  }
  ```

#### Mark as read
- **Method**: PATCH
- **Path**: `/v1/notifications/{id}/read`
- **Success**:
  ```json
  {
    "status": "success",
    "message": "Marked as read"
  }
  ```

---

## Implementation notes & recommendations
- OTP/password reset endpoints currently return a descriptive error because the Users model lacks OTP persistence methods (`setOtpCode`, `findByOtpCode`, `markOtpAsUsed`). Implement these server-side before enabling OTP flows.
- Authentication/authorization: controllers currently do not enforce auth. Add middleware (JWT or session) to protect endpoints (user updates, create/delete manager, create order/payment, etc.).
- Input validation: controllers decode JSON and call models; front-end should validate fields/format before sending. Back-end should be hardened with server-side validation too.
- Error details: models populate `getLastError()` and controllers return that in error messages. Frontend should show user-friendly messages and possibly log details for debugging.
- Routes and path naming: Some endpoints accept snake_case keys (e.g., `user_id`) in request bodies. Use exactly the keys shown in the docs.