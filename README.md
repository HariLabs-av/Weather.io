# Weather.io

Weather.io is a responsive weather information application. The frontend pages and PHP authentication backend are deployed together as one Docker web service on Render. Weather data is fetched from Open-Meteo, while user accounts are stored in MySQL.

## Repository layout

```text
.
├── backend/
│   ├── api/             # JSON authentication endpoints
│   ├── admin/           # Protected dashboard and one-time setup
│   ├── auth/            # Session and CSRF helpers
│   ├── config/          # PDO connection and schema initializer
│   ├── partial/         # Shared PHP/CSS/JS
│   ├── database.sql     # MySQL schema
│   ├── Dockerfile       # PHP 8.2 + Apache + PDO MySQL
│   └── health.php       # Render health check
├── render.yaml
└── README.md
```

## How the database works

Render runs the application container but does not provide a built-in MySQL database. Create a MySQL database with an external provider, then connect it to Render using environment variables. The application uses one required table:

| Table | Purpose |
| --- | --- |
| `users` | Stores names, unique emails, password hashes, roles, and creation timestamps |

The full schema is in [`backend/database.sql`](backend/database.sql). The first-admin page also runs the equivalent `CREATE TABLE IF NOT EXISTS users` statement, so a fresh database can be initialized from the browser.

## Deploy everything on Render

### 1. Create the MySQL database

Create a normal MySQL database with a provider such as Railway, Aiven, DigitalOcean Managed MySQL, or another compatible service. Record:

```text
DB_HOST
DB_PORT
DB_NAME
DB_USER
DB_PASSWORD
```

If the provider gives you a connection URL, use the individual host, port, database, username, and password values from that URL. Do not commit credentials to GitHub.

You may import the schema from your computer:

```bash
mysql -h DB_HOST -P DB_PORT -u DB_USER -p DB_NAME < backend/database.sql
```

If you do not have the MySQL CLI, use the provider's SQL console and paste the contents of `backend/database.sql`.

### 2. Create the Render service

1. Push this repository to GitHub.
2. In Render, choose **New → Blueprint**.
3. Select `<user_repo>/Weather.io`.
4. Render reads [`render.yaml`](render.yaml), builds `backend/Dockerfile`, and checks `/health.php`.
5. Add these secret environment variables to the Render service:

| Variable | Value |
| --- | --- |
| `DB_HOST` | MySQL provider hostname |
| `DB_PORT` | Usually `3306` |
| `DB_NAME` | MySQL database name |
| `DB_USER` | MySQL username |
| `DB_PASSWORD` | MySQL password |

`render.yaml` supplies the default `DB_PORT=3306`; replace it if your provider uses another port.

There is no `FRONTEND_URL` setting in the production setup because the frontend and backend share the same origin. Same-origin requests automatically include the session cookie.

### 3. Initialize the tables and admin account

After the Render deployment succeeds:

1. Open `https://YOUR-SERVICE.onrender.com/admin/setup.php`.
2. The page creates the `users` table if it does not exist.
3. Enter the first administrator's name, email, and password.
4. Open `https://YOUR-SERVICE.onrender.com/login.php` and sign in.
5. Open `https://YOUR-SERVICE.onrender.com/admin/index.php` to verify the dashboard.
6. Delete `backend/admin/setup.php` locally, commit, push, and wait for Render to redeploy.

The setup page refuses to create another administrator once any account exists. Removing it after first use is still required because it is a deployment-only endpoint.

### 4. Verify the deployment

Open:

```text
https://YOUR-SERVICE.onrender.com/health.php
```

Expected response:

```json
{"status":"ok"}
```

Then verify the main page, login, registration, logout, and admin dashboard. If the setup page says that the database is not ready, check the five `DB_*` values, allow external connections in the MySQL provider, and inspect the Render deploy logs.

## Authentication API

The API is same-origin when called by the deployed frontend:

| Endpoint | Method | Purpose |
| --- | --- | --- |
| `/api/login.php` | `POST` | Validate credentials and create an HttpOnly session |
| `/api/me.php` | `GET` | Return the current logged-in user |
| `/api/logout.php` | `POST` | Validate CSRF token and destroy the session |
| `/health.php` | `GET` | Return service health |

Example login:

```js
const response = await fetch('/api/login.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email, password })
});

const result = await response.json();
```

Retain `result.csrf_token` and send it as `X-CSRF-Token` when logging out. If the frontend is later moved to another domain, configure `FRONTEND_URL` with the exact HTTPS origin and use `credentials: 'include'`.

## Local development

Requirements: PHP 8.2+, MySQL 8+, and the `pdo_mysql` PHP extension.

```bash
cd backend
mysql -u root -p -e "CREATE DATABASE weather_io CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p weather_io < database.sql
php -S localhost:8080
```

Open `http://localhost:8080/admin/setup.php` once to create the first administrator. Local defaults are:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=weather_io
DB_USER=root
DB_PASSWORD=
```

## Security notes

- Passwords use PHP `password_hash()` and `password_verify()`.
- SQL queries use PDO prepared statements.
- Sessions use HttpOnly cookies and regenerate the session ID after login.
- The setup page creates only the first administrator and must be deleted after initialization.
- Never commit MySQL credentials, `.env` files, or exported database dumps containing real user data.
