# E-commerce Order Management System

A complete Laravel-based E-commerce Order Management System with RESTful APIs, authentication, role-based access control, and order processing.

## Features

- **Authentication & Authorization** (Sanctum)
- **Product & Category Management**
- **Shopping Cart**
- **Order Processing**
- **Mock Payment System**
- **Role-based Access Control** (Admin/Customer)
- **Notifications & Email**
- **Caching & Performance Optimization**
- **Comprehensive Testing**

## Tech Stack

- **Backend:** Laravel v12.32.2, PHP 8.3.25
- **Authentication:** Laravel Sanctum
- **Database:** MySQL
- **Testing:** PHPUnit
- **Queue:** Database Queue
- **Caching:** Laravel Cache

## Installation

1. **Clone the repository**
   ```bash
   gh repo clone Moin2525/Ecommerce_order_system
   cd ecommerce-order-system
Install dependencies

bash
composer install
Environment setup

bash
cp .env.example .env
php artisan key:generate
Database configuration

bash
# Update .env with your database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_order_system
DB_USERNAME=root
DB_PASSWORD=root
Run migrations and seeders

bash
php artisan migrate
php artisan db:seed
Start the server

bash
php artisan serve
Seeded Data
The system comes with pre-seeded data:

2 Admin users

10 Customer users

5 Categories

20 Products

15 Orders with payments
