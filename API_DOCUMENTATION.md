# API Documentation

## Base URL
{{base_url}} -> http://localhost:8000/api

## Authentication
All protected endpoints require Bearer token in Authorization header.

## Complete Endpoint List

### Authentication
- `POST /register` - User registration
- `POST /login` - User login  
- `POST /logout` - User logout
- `GET /me` - Get user profile

### Categories
- `GET /categories` - List categories (Public)
- `POST /categories` - Create category (Admin)
- `PUT /categories/{id}` - Update category (Admin)
- `DELETE /categories/{id}` - Delete category (Admin)

### Products
- `GET /products` - List products with filters (Public)
- `GET /products/{id}` - Get product details (Public)
- `POST /products` - Create product (Admin)
- `PUT /products/{id}` - Update product (Admin)
- `DELETE /products/{id}` - Delete product (Admin)

### Cart
- `GET /cart` - Get cart items (Customer)
- `POST /cart` - Add to cart (Customer)
- `PUT /cart/{id}` - Update cart item (Customer)
- `DELETE /cart/{id}` - Remove from cart (Customer)

### Orders
- `GET /orders` - Get user orders (Customer)
- `POST /orders` - Create order from cart (Customer)
- `PUT /orders/{id}/status` - Update order status (Admin)

### Payments
- `POST /orders/{id}/payment` - Process payment (Customer)
- `GET /payments/{id}` - Get payment details (Customer)

## Request/Response Examples

### User Registration
**Request:**
```json
POST /api/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
