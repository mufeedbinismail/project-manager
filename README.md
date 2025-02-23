# Project Manager
ProjectManager is a project management system built with Laravel. It features users, project management, timesheet logging, dynamic attributes for projects, and flexible filtering. The project includes RESTful API endpoints with authentication using Laravel Passport.

## Requirements

- PHP 7.4+
- Laravel 8.x
- MySQL
- Composer

## Setup Instructions

1. Clone the repository:
    ```bash
    git clone https://github.com/mufeedbinismail/project-manager.git
    cd project-manager
    ```

2. Install dependencies:
    ```bash
    composer install
    ```

3. Configure write permissions for these directories (Linux/macOS):
    - `bootstrap/cache`
    - `storage`
    ```bash
    for i in bootstrap/cache storage; do
        find "$i" -type d | xargs -I {} sudo chmod 775 "{}"
        find "$i" -type d | xargs -I {} sudo chown $USER:www-data "{}"
    done
    ```

4. Copy the `.env.example` file to `.env` and update your environment variables:
    ```bash
    cp .env.example .env
    ```

5. Update the database connection configurations in the `.env` file:
    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_database_user
    DB_PASSWORD=your_database_password
    ```

6. Generate the application key:
    ```bash
    php artisan key:generate
    ```

7. Run the database migrations and seeders:
    ```bash
    php artisan migrate --seed
    ```

9. Start the development server:
    ```bash
    php artisan serve
    ```
