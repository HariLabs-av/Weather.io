# Weather.io backend

See the repository root [`README.md`](../README.md) for the complete project layout, Render deployment instructions, MySQL setup, Netlify integration, and local development guide.

## PHP/MySQL authentication

This repository contains the deployable PHP/MySQL authentication backend for the Weather.io frontend. It includes a protected admin panel at `admin/index.php` and JSON endpoints for a separately deployed frontend:

```text
POST /api/login.php
GET  /api/me.php
POST /api/logout.php
```

The API uses an HttpOnly session cookie. Send `credentials: 'include'` from the Netlify frontend. Set `FRONTEND_URL` to the exact Netlify origin (for example `https://weather-io.netlify.app`; multiple origins may be comma-separated).

Visitors can create regular user accounts from `register.php`; public registration never allows an administrator role.

## Deploying on Render

The included `render.yaml` deploys the PHP application as a Docker web service. Render does not offer MySQL, so create a MySQL database with a compatible provider such as Railway, Aiven, or PlanetScale and copy its connection values into the Render service environment:

```text
DB_HOST=<mysql-host>
DB_PORT=<mysql-port>
DB_NAME=<database-name>
DB_USER=<database-user>
DB_PASSWORD=<database-password>
FRONTEND_URL=https://your-site.netlify.app
```

In Render, create a Blueprint from this repository, or configure a Docker web service with root directory `backend`. After deployment, import `database.sql` into the external MySQL database, open `/admin/setup.php` on the deployed URL, create the administrator, and delete `admin/setup.php` from the repository.

The Docker image installs `pdo_mysql` automatically. `FRONTEND_URL` enables credentialed CORS for the Netlify frontend; never use `*` with authenticated requests. The Render health check is `/health.php`.

## Local/setup instructions

1. Create the database by importing `database.sql` into MySQL.
2. Configure the connection with environment variables: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD`. Defaults are `127.0.0.1`, `3306`, `weather_io`, `root`, and an empty password.
3. Open `admin/setup.php` once and create the first administrator account.
4. Delete `admin/setup.php` after setup and use `login.php` to sign in.

For the Netlify frontend, call the backend URL with credentials enabled:

```js
fetch(`${BACKEND_URL}/api/login.php`, {
  method: 'POST',
  credentials: 'include',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({email, password})
});
```

Passwords are stored with PHP's `password_hash()` and all database queries use PDO prepared statements. The admin panel currently provides account counts and a list of registered accounts; only administrators can access it.
