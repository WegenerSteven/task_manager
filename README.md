## Task Manager API (Laravel)

This project is a simple Task Management API built with Laravel and MySQL for the **Laravel Engineer Intern Take-Home Assignment**.

It exposes endpoints to:

- Create tasks
- List tasks
- Update task status
- Delete tasks
- Generate a daily task report (bonus)

The database schema is managed via Laravel migrations. The main table is `tasks` with the following columns:

- `id` (primary key)
- `title` (string)
- `due_date` (date)
- `priority` (enum: `low`, `medium`, `high`)
- `status` (enum: `pending`, `in_progress`, `done`)
- `created_at`, `updated_at` (timestamps)

---

## 1. Running Locally (MySQL)

### Prerequisites

- PHP 8.2+
- Composer
- MySQL instance (local or remote)

### Steps

1. **Install PHP dependencies**

    ```bash
    composer install
    ```

2. **Create `.env` and configure MySQL**

    ```bash
    cp .env.example .env
    ```

    Then edit `.env` and set your database connection (example):

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=task_manager
    DB_USERNAME=your_mysql_user
    DB_PASSWORD=your_mysql_password
    ```

3. **Generate application key**

    ```bash
    php artisan key:generate
    ```

4. **Run migrations (and seeders if added)**

    ```bash
    php artisan migrate
    # php artisan db:seed   # only if you later add seeders
    ```

5. **Start the development server**

    ```bash
    php artisan serve
    ```

    The API will be available at:
    - `http://127.0.0.1:8000/api`

---

## 2. API Endpoints

Base URL (local): `http://127.0.0.1:8000/api`

### 2.1 Create Task

- **Endpoint:** `POST /tasks`
- **Body (JSON):**

    ```json
    {
        "title": "Write report",
        "due_date": "2026-04-05",
        "priority": "high"
    }
    ```

- **Rules:**
    - `title` + `due_date` combination must be unique.
    - `priority` must be one of: `low`, `medium`, `high`.
    - `due_date` must be today or later.

- **Response:** 201 Created with the created task.

### 2.2 List Tasks

- **Endpoint:** `GET /tasks`
- **Query parameters (optional):**
    - `status` – `pending`, `in_progress`, or `done`.

- **Behavior:**
    - Sorted by `priority` (high → medium → low), then `due_date` ascending.
    - Returns a meaningful JSON response when there are no tasks.

### 2.3 Update Task Status

- **Endpoint:** `PATCH /tasks/{id}/status`
- **Body (JSON):**

    ```json
    {
        "status": "in_progress"
    }
    ```

- **Rules:**
    - Status can only progress: `pending` → `in_progress` → `done`.
    - No skipping or reverting allowed.

### 2.4 Delete Task

- **Endpoint:** `DELETE /tasks/{id}`

- **Rules:**
    - Only tasks with status `done` can be deleted.
    - Otherwise, returns **403 Forbidden**.

### 2.5 Daily Report (Bonus)

- **Endpoint:** `GET /tasks/report?date=YYYY-MM-DD`

- **Example:**

    ```http
    GET /api/tasks/report?date=2026-03-28
    ```

- **Response shape:**

    ```json
    {
        "date": "2026-03-28",
        "summary": {
            "high": { "pending": 2, "in_progress": 1, "done": 0 },
            "medium": { "pending": 1, "in_progress": 0, "done": 3 },
            "low": { "pending": 0, "in_progress": 0, "done": 1 }
        }
    }
    ```

---

## 3. Example cURL Requests

Assuming the app is running at `http://127.0.0.1:8000`.

```bash
# Create a task
curl -X POST http://127.0.0.1:8000/api/tasks \
	-H "Content-Type: application/json" \
	-d '{"title":"Write report","due_date":"2026-04-05","priority":"high"}'

# List tasks (all)
curl http://127.0.0.1:8000/api/tasks

# List tasks by status
curl "http://127.0.0.1:8000/api/tasks?status=pending"

# Update task status
curl -X PATCH http://127.0.0.1:8000/api/tasks/1/status \
	-H "Content-Type: application/json" \
	-d '{"status":"in_progress"}'

# Delete a task
curl -X DELETE http://127.0.0.1:8000/api/tasks/1

# Daily report
curl "http://127.0.0.1:8000/api/tasks/report?date=2026-03-28"
```

---

## 4. Deploying Online (e.g. Render / Railway)

Below is a high-level guide to host the API online with MySQL.

### 4.1 Common Steps

1. Push this repository to GitHub.
2. Create a **MySQL** database in your chosen platform (or use their managed MySQL add-on).
3. Note the connection details: host, port, database name, username, password.

### 4.2 Example: Render

1. Create a new **Web Service** from this GitHub repo.
2. Set the **Build Command**:

    ```bash
    composer install --no-dev --optimize-autoloader
    php artisan key:generate
    php artisan migrate --force
    ```

3. Set the **Start Command**:

    ```bash
    php artisan serve --host=0.0.0.0 --port=8080
    ```

4. Configure environment variables in Render to match your MySQL database:

    ```env
    APP_ENV=production
    APP_KEY=base64:...        # from php artisan key:generate
    APP_DEBUG=false
    APP_URL=https://your-app.onrender.com

    DB_CONNECTION=mysql
    DB_HOST=your-mysql-host
    DB_PORT=3306
    DB_DATABASE=your-db-name
    DB_USERNAME=your-db-user
    DB_PASSWORD=your-db-password
    ```

5. Deploy. Once live, your base URL will look like:
    - `https://your-app.onrender.com/api`

You can then run the same API requests against the hosted URL.

### 4.3 Example: Railway (Alternative)

1. Create a new **Project** and add a **GitHub Service** pointing to this repo.
2. Add a **MySQL** plugin and copy its connection credentials.
3. Configure environment variables (`DB_*`, `APP_KEY`, etc.) similarly to the Render example.
4. Add appropriate `Build` and `Start` commands (same as above) in the Railway service settings.
5. Deploy and test the API via the Railway-provided URL.

---

This README summarizes how to run, test, and deploy the Task Manager API as required by the assignment.
