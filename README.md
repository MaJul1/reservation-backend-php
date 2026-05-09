# Resource Reservation System Backend

This is a Laravel-based backend for a resource reservation system, using SQLite as the database.

## Prerequisites

- PHP >= 8.2
- Composer
- SQLite3

## Setup Instructions

1. **Clone the repository** (if you haven't already).

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Environment Setup**:
   - Copy `.env.example` to `.env`.
   - Ensure `DB_CONNECTION` is set to `sqlite`.
   - The database file is located at `database/database.sqlite`. If it doesn't exist, create it:
     ```bash
     touch database/database.sqlite
     ```

4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

6. **Generate Swagger Documentation**:
   ```bash
   php artisan l5-swagger:generate
   ```

7. **Start the Development Server**:
   ```bash
   php artisan serve --port=5007
   ```

## API Documentation

Once the server is running, you can access the Swagger UI at:
[http://localhost:5007/api/documentation](http://localhost:5007/api/documentation)

## Running Tests

To run the automated tests:
```bash
php artisan test
```
