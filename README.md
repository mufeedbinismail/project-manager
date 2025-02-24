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

## API Documentation

### Authentication

#### Register
- **URL:** `/api/register`
- **Method:** `POST`
- **Request Body:**
    ```json
    {
        "name": "John Doe",
        "email": "john.doe@example.com",
        "password": "password",
        "password_confirmation": "password"
    }
    ```
- **Response:**
    ```json
    {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john.doe@example.com",
            "created_at": "2025-02-25 00:00:00.000000",
            "updated_at": "2025-02-25 00:00:00.000000"
        },
        "token": "your_access_token"
    }
    ```

#### Login
- **URL:** `/api/login`
- **Method:** `POST`
- **Request Body:**
    ```json
    {
        "email": "john.doe@example.com",
        "password": "password"
    }
    ```
- **Response:**
    ```json
    {
        "token": "your_access_token"
    }
    ```

#### Logout
- **URL:** `/api/logout`
- **Method:** `POST`
- **Headers:**
    ```http
    Authorization: Bearer your_access_token
    ```
- **Response:**
    ```json
    {
        "message": "Logged out"
    }
    ```

### Projects

#### List Projects
- **URL:** `/api/projects`
- **Method:** `GET`
- **Headers:**
    ```http
    Authorization: Bearer your_access_token
    ```
- **Query Parameters:**
    - `filters`: An array of filters to apply to the query. Each filter should be an array with a single key-value pair where the key is the column name and the value is an array with a single key-value pair representing the operator and value.
    - Supported operators: `=`, `>`, `>=`, `<`, `<=`, `!=`, `like`
- **Example Request:**
    ```http
    GET /api/projects?filters[name][like]=%Project%
    ```
- **Example Response:**
    ```json
    [
        {
            "id": 1,
            "name": "Project A",
            "status": "active",
            "created_at": "2025-02-25 00:00:00.000000",
            "updated_at": "2025-02-25 00:00:00.000000",
            "entityAttributes": [
                {
                    "id": 1,
                    "attribute_name": "budget",
                    "attribute_type": "number",
                    "value": "10000",
                    "value_description": null,
                    "created_at": "2025-02-25 00:00:00.000000",
                    "updated_at": "2025-02-25 00:00:00.000000"
                },
                {
                    "id": 2,
                    "attribute_name": "priority",
                    "attribute_type": "select",
                    "value": "high",
                    "value_description": "High Priority",
                    "created_at": "2025-02-25 00:00:00.000000",
                    "updated_at": "2025-02-25 00:00:00.000000"
                }
            ]
        }
    ]
    ```
- **Possible Errors:**
    - `422 Unprocessable Entity`: Invalid filters format. Filters should be an array.
    - `422 Unprocessable Entity`: Invalid filter format. Filters should be an array with exactly one element.
    - `422 Unprocessable Entity`: Invalid operator for filter key.
    - `422 Unprocessable Entity`: Invalid value for filter key.
    - `422 Unprocessable Entity`: Invalid filter key.

### Users
- **URL:** `/api/users`
- **Method:** `GET`, `POST`, `PUT`, `DELETE`
- **Headers:**
    ```http
    Authorization: Bearer your_access_token
    ```

### Timesheets
- **URL:** `/api/timesheets`
- **Method:** `GET`, `POST`, `PUT`, `DELETE`
- **Headers:**
    ```http
    Authorization: Bearer your_access_token
    ```

### Attributes
- **URL:** `/api/attributes`
- **Method:** `POST`, `PUT`
- **Headers:**
    ```http
    Authorization: Bearer your_access_token
    ```

### Project User Assignment
#### Assign Users to Project
- **URL:** `/api/projects/{project}/users`
- **Method:** `POST`
- **Headers:**
    ```http
    Authorization: Bearer your_access_token
    ```
- **Request Body:**
    ```json
    {
        "user_ids": [1, 2, 3]
    }
    ```
- **Response:**
    ```json
    {
        "message": "Users assigned to project successfully"
    }
    ```

#### Unassign Users from Project
- **URL:** `/api/projects/{project}/users`
- **Method:** `DELETE`
- **Headers:**
    ```http
    Authorization: Bearer your_access_token
    ```
- **Request Body:**
    ```json
    {
        "user_ids": [1, 2, 3]
    }
    ```
- **Response:**
    ```json
    {
        "message": "Users unassigned from project successfully"
    }
    ```
