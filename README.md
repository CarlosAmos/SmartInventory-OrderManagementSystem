# Smart Inventory & Order Management System

Technical assessment demonstrating a decoupled Laravel API + Vue.js SPA architecture with robust stock concurrency management.

## Overview
Full-stack inventory management system for a PC parts shop featuring:
- Real-time stock monitoring with auto-refresh
- Concurrent order handling with pessimistic locking
- Token-based API authentication
- Client-side cart with state persistence
- Comprehensive error handling and validation

## Tech Stack
### Backend
- **Laravel 12** - PHP Framework
- **SQLite** - Database (in-memory for tests)
- **Laravel Sanctum** - API Token Authentication
- **PHPUnit** - Testing Framework

### Frontend
- **Vue 3** - Frontend Framework (Composition API)
- **Pinia** - State Management (Auth + Cart)
- **TanStack Query** - Data Fetching & Caching
- **Tailwind CSS** - Styling
- **Vue Router** - Client-side Routing
- **Axios** - HTTP Client

## Installation
### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- npm
- git bash (To run setup commands)

### BACKEND Setup

```bash
cd backend-laravel
composer install
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

The API will be available at `http://localhost:8001`

### FRONTEND SETUP

```bash
cd frontend-vue
npm install
npm run dev
```

The SPA will be available at `http://localhost:5173`

## Login Credentials

- **Username:** `admin@email.com`
- **Password:** `password123`

OR

- **Username:** `testuser@email.com`
- **Password:** `password123`

## Running Tests

```bash
cd backend-laravel
php artisan test
```
