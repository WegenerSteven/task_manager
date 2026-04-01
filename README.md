# Task Manager — Laravel + Vue.js

A full-stack Task Management application built with **Laravel** (API) and **Vue.js 3** (frontend), using **MySQL** as the database.

---

## Features

- Create tasks with title, due date, and priority
- List tasks sorted by priority (high → low) then due date
- Filter tasks by status
- Update task status (pending → in_progress → done)
- Delete completed tasks only
- Daily report by date showing task counts per priority and status
- Responsive frontend built with Vue.js 3 (CDN) and Vanilla CSS

---

## Tech Stack

| Layer     | Technology          |
|-----------|---------------------|
| Backend   | PHP 8.2, Laravel 12 |
| Frontend  | Vue.js 3 (CDN), Vanilla CSS |
| Database  | MySQL               |
| Hosting   | Render (App), Railway (MySQL) |

---

## 1. Running Locally

### Prerequisites

- PHP 8.2+
- Composer
- MySQL instance (local or remote)

### Steps

1. **Clone the repository**
```bash
    git clone https://github.com/WegenerSteven/task_manager
    cd task_manager
```

2. **Install PHP dependencies**
```bash
    composer install
```

3. **Create and configure `.env`**
```bash
    cp .env.example .env
```

    Update the following in `.env`:
```env
    APP_NAME=TaskManager
    APP_ENV=local
    APP_URL=http://localhost:8000

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=task_manager
    DB_USERNAME=your_mysql_user
    DB_PASSWORD=your_mysql_password
```

4. **Generate application key**
```bash
    php artisan key:generate
```

5. **Run migrations**
```bash
    php artisan migrate
```

6. **Start the development server**
```bash
    php artisan serve
```

    App available at: `http://127.0.0.1:8000`

---

## 2. Project Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── TaskController.php       # API logic
│   │   └── SwaggerController.php    # OpenAPI schema definitions
│   └── Requests/
│       ├── StoreTaskRequest.php     # Create task validation
│       └── UpdateTaskStatusRequest.php
├── Models/
│   └── Task.php
public/
├── css/
│   └── tasks.css                    # Frontend styles
└── js/
    └── tasks.js                     # Vue.js logic
resources/
└── views/
    └── tasks.blade.php              # Frontend HTML (Vue template)
```

---

## 3. API Endpoints

Base URL (local): `http://127.0.0.1:8000/api`
Base URL (production): `https://task-manager-1hb4.onrender.com/api`

### Create Task
```http
POST /api/tasks
Content-Type: application/json

{
    "title": "Write report",
    "due_date": "2026-04-05",
    "priority": "high"
}
```
Rules:
- `title` + `due_date` combination must be unique
- `priority`: `low`, `medium`, or `high`
- `due_date` must be today or later

---

### List Tasks
```http
GET /api/tasks
GET /api/tasks?status=pending
```
- Sorted by priority (high → medium → low), then `due_date` ascending
- Optional `status` filter: `pending`, `in_progress`, `done`

---

### Update Task Status
```http
PATCH /api/tasks/{id}/status
Content-Type: application/json

{
    "status": "in_progress"
}
```
- Only forward transitions allowed: `pending` → `in_progress` → `done`
- No skipping or reverting

---

### Delete Task
```http
DELETE /api/tasks/{id}
```
- Only tasks with status `done` can be deleted
- Returns `403 Forbidden` otherwise

---

### Daily Report (Bonus)
```http
GET /api/tasks/report?date=2026-04-01
```

Response:
```json
{
    "date": "2026-04-01",
    "summary": {
        "high":   { "pending": 2, "in_progress": 1, "done": 0 },
        "medium": { "pending": 1, "in_progress": 0, "done": 3 },
        "low":    { "pending": 0, "in_progress": 0, "done": 1 }
    }
}
```

---

## 4. Example cURL Requests
```bash
# Create a task
curl -X POST https://task-manager-1hb4.onrender.com/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Write report","due_date":"2026-04-05","priority":"high"}'

# List all tasks
curl https://task-manager-1hb4.onrender.com/api/tasks

# List tasks filtered by status
curl "https://task-manager-1hb4.onrender.com/api/tasks?status=pending"

# Update task status
curl -X PATCH https://task-manager-1hb4.onrender.com/api/tasks/1/status \
  -H "Content-Type: application/json" \
  -d '{"status":"in_progress"}'

# Delete a task
curl -X DELETE https://task-manager-1hb4.onrender.com/api/tasks/1

# Daily report
curl "https://task-manager-1hb4.onrender.com/api/tasks/report?date=2026-04-01"
```

---

## 5. Deployment

### Live URLs
- **Frontend:** https://task-manager-1hb4.onrender.com
- **API Base:** https://task-manager-1hb4.onrender.com/api

### Stack
- **App Hosting:** [Render](https://render.com) — Docker-based deployment
- **Database:** [Railway](https://railway.app) — Managed MySQL

---

### Deploying Your Own Instance

#### Step 1 — Set up MySQL on Railway

1. Go to [railway.app](https://railway.app) → New Project → Deploy MySQL
2. Once created, go to the MySQL service → **Connect** tab
3. Copy the **public** host and port (under Public Networking)

#### Step 2 — Deploy on Render

1. Fork this repository to your GitHub account
2. Go to [render.com](https://render.com) → New → Web Service
3. Connect your GitHub repo
4. Set Runtime to **Docker**
5. Add the following environment variables:
```env
APP_NAME=TaskManager
APP_ENV=production
APP_KEY=base64:...            # generate with: php artisan key:generate --show
APP_DEBUG=false
APP_URL=https://your-app.onrender.com

DB_CONNECTION=mysql
DB_HOST=your-railway-public-host
DB_PORT=your-railway-public-port
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=your-railway-password
```

6. Deploy — Render will build the Docker image and run migrations automatically

#### The `Dockerfile` handles:
- Installing PHP extensions (`pdo`, `pdo_mysql`, `mbstring`)
- Installing Composer dependencies
- Running `php artisan migrate --force` on startup
- Starting the Laravel server on the assigned port

---

## 6. Database

- **Engine:** MySQL
- **Managed by:** Laravel Migrations
- **Schema:** Single `tasks` table

| Column | Type | Description |
|---|---|---|
| id | integer | Primary key |
| title | string | Task title |
| due_date | date | Deadline |
| priority | enum | `low`, `medium`, `high` |
| status | enum | `pending`, `in_progress`, `done` |
| created_at | timestamp | Auto-managed |
| updated_at | timestamp | Auto-managed |

To export the schema locally:
```bash
php artisan schema:dump
```
The SQL dump will be saved to `database/schema/mysql-schema.sql`.