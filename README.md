# Market API

A RESTful e-commerce API built with Laravel.

This project provides authentication, product and category management, order management, role-based authorization, stock management, request validation, API resources, and automated testing.

---

## Features

* User registration and login
* Laravel Sanctum authentication
* Token-based authentication
* Role-based authorization
* Admin-only product management
* Admin-only category management
* Product listing and details
* Category listing and details
* Order creation
* Order listing and details
* Order cancellation
* Order completion by admin
* Stock management
* Stock validation before creating orders
* Database transactions
* Row-level locking using `lockForUpdate()`
* Form Request validation
* API Resources
* Custom business exceptions
* API filtering and sorting
* Pagination for orders
* Product image upload and deletion
* Automated API tests

---

## Technologies

* PHP
* Laravel
* Laravel Sanctum
* MySQL
* REST API
* Postman
* PHPUnit
* Git
* GitHub

---

## Project Architecture

The project follows a simple layered structure to keep responsibilities separated.

```text
Request
   ↓
Controller
   ↓
Action
   ↓
Model / Database
   ↓
Resource
   ↓
JSON Response
```

Authorization is handled separately using middleware and policies.

Business exceptions are handled through Laravel's exception handling system.

---

## Authentication

The API uses **Laravel Sanctum** for token-based authentication.

Users can:

* Register
* Login
* Logout

After registration or login, the API returns an authentication token.

For protected endpoints, send the token using:

```text
Authorization: Bearer YOUR_TOKEN
```

---

## User Roles

The API currently supports two roles:

* `admin`
* `user`

### Admin

Admins can:

* Create products
* Update products
* Delete products
* Create categories
* Update categories
* Delete categories
* View all orders
* Complete pending orders

### User

Regular users can:

* View products
* View categories
* Create orders
* View their own orders
* Cancel their own pending orders

---

## API Endpoints

### Authentication

| Method | Endpoint        | Description         | Authentication |
| ------ | --------------- | ------------------- | -------------- |
| POST   | `/api/register` | Register a new user | No             |
| POST   | `/api/login`    | Login               | No             |
| POST   | `/api/logout`   | Logout              | Yes            |

---

### Products

| Method | Endpoint                  | Description            | Authentication |
| ------ | ------------------------- | ---------------------- | -------------- |
| GET    | `/api/products`           | Get all products       | Yes            |
| GET    | `/api/products/{product}` | Get a specific product | Yes            |
| POST   | `/api/products`           | Create a product       | Admin          |
| PUT    | `/api/products/{product}` | Update a product       | Admin          |
| DELETE | `/api/products/{product}` | Delete a product       | Admin          |

---

### Categories

| Method | Endpoint                     | Description             | Authentication |
| ------ | ---------------------------- | ----------------------- | -------------- |
| GET    | `/api/categories`            | Get all categories      | Yes            |
| GET    | `/api/categories/{category}` | Get a specific category | Yes            |
| POST   | `/api/categories`            | Create a category       | Admin          |
| PUT    | `/api/categories/{category}` | Update a category       | Admin          |
| DELETE | `/api/categories/{category}` | Delete a category       | Admin          |

---

### Orders

| Method | Endpoint                       | Description              | Authentication |
| ------ | ------------------------------ | ------------------------ | -------------- |
| GET    | `/api/orders`                  | Get orders               | Yes            |
| GET    | `/api/orders/{order}`          | Get a specific order     | Yes            |
| POST   | `/api/orders`                  | Create an order          | Yes            |
| POST   | `/api/orders/{order}/cancel`   | Cancel a pending order   | Owner          |
| POST   | `/api/orders/{order}/complete` | Complete a pending order | Admin          |

---

## Order Management

When creating an order, the client sends the requested products and quantities.

Example:

```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        },
        {
            "product_id": 3,
            "quantity": 1
        }
    ]
}
```

The server handles:

1. Creating the order.
2. Checking product stock.
3. Locking the selected products for update.
4. Creating the order items.
5. Storing the product price at the time of purchase.
6. Decreasing product stock.
7. Calculating the order total.
8. Updating the order total.

---

## Stock Management

Stock operations are protected using database transactions and row-level locking.

The order creation process uses:

```php
lockForUpdate()
```

This helps prevent multiple concurrent orders from incorrectly consuming the same available stock.

If the requested quantity is greater than the available stock, the API throws a custom `InsufficientStockException`.

The API returns a `422` response for insufficient stock.

---

## Order Status

Orders can have one of the following statuses:

```text
pending
completed
cancelled
```

### Pending

The order has been created and has not been completed or cancelled.

### Completed

The order has been completed by an admin.

### Cancelled

The order has been cancelled by its owner while it is still pending.

When an order is cancelled, its quantities are returned to the product stock.

---

## Validation

The API uses Laravel Form Requests to validate incoming data.

Examples include:

* `RegisterRequest`
* `LoginRequest`
* `StoreProductRequest`
* `UpdateProductRequest`
* `StoreCategoryRequest`
* `StoreOrderRequest`
* `OrderIndexRequest`

Validation is handled before the request reaches the business logic.

---

## API Resources

Laravel API Resources are used to control the structure of API responses.

The project includes resources for:

* Users
* Products
* Categories
* Orders
* Order Items

This keeps database models separate from the API response structure.

---

## Database Structure

The main entities are:

```text
Users
  │
  │ 1:N
  ▼
Orders
  │
  │ 1:N
  ▼
Order Items
  │
  │ N:1
  ▼
Products
  │
  │ N:1
  ▼
Categories
```

### Main Relationships

* A user has many orders.
* An order belongs to a user.
* An order has many order items.
* An order item belongs to an order.
* An order item belongs to a product.
* A product has many order items.
* A product belongs to a category.
* A category has many products.

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/Tarek-Hatem-46/market-api
```

### 2. Navigate to the project

```bash
cd Market
```

### 3. Install PHP dependencies

```bash
composer install
```

### 4. Create the environment file

Copy:

```text
.env.example
```

to:

```text
.env
```

### 5. Generate the application key

```bash
php artisan key:generate
```

### 6. Configure the database

Update the database settings in `.env`.

Example:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=market
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Run migrations

```bash
php artisan migrate
```

### 8. Create the storage link

```bash
php artisan storage:link
```

### 9. Start the Laravel server

```bash
php artisan serve
```

The API will be available at:

```text
http://127.0.0.1:8000
```

---

## Testing

The project includes automated tests covering authentication, products, categories, orders, authorization, validation, and business error handling.

Run the test suite with:

```bash
php artisan test
```

Current test result:

```text
59 tests passed
130 assertions
```

---

## API Testing

The API can be tested using **Postman**.

Recommended testing flow:

```text
Register
   ↓
Login
   ↓
Copy authentication token
   ↓
Set Bearer Token
   ↓
Test protected endpoints
```

For admin endpoints, use an account with the `admin` role.

---

## Error Handling

The API uses appropriate HTTP status codes for different situations.

Examples:

```text
201 Created
200 OK
401 Unauthorized
403 Forbidden
404 Not Found
422 Unprocessable Entity
```

Business-specific errors are handled using custom exceptions.

For example, when there is insufficient stock:

```json
{
    "success": false,
    "message": "Insufficient stock"
}
```

---

## Future Improvements

Possible future improvements include:

* Product pagination
* Product search
* More advanced filtering
* Product soft deletes
* Order status enum
* Improved money/decimal handling
* API versioning
* More comprehensive API documentation
* Rate limiting
* Email notifications
* Order history improvements
* Docker configuration
* CI/CD pipeline

---

## Project Purpose

This project was built as a backend learning project to practice building RESTful APIs with Laravel and to apply concepts such as authentication, authorization, validation, database relationships, transactions, stock management, clean architecture, and automated testing.

---

## Author

**Tarek Hatem**

Backend Developer | PHP & Laravel

GitHub: [Tarek Hatem](https://github.com/Tarek-Hatem-46)
