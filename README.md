# Weather.io

Weather.io is a responsive weather information application with a PHP/MySQL authentication backend. The frontend displays current weather for a searched city using the Open-Meteo geocoding and forecast APIs. The backend provides login, registration, session authentication, a protected administrator dashboard, and JSON endpoints that can be called from the Netlify frontend.

## Repository layout

```text
.
├── backend/
│   ├── api/             # JSON authentication endpoints
│   ├── admin/           # Protected admin dashboard and one-time setup
│   ├── auth/            # Session and CSRF helpers
│   ├── config/          # PDO/MySQL connection
│   ├── partial/         # Shared frontend PHP/CSS/JS
│   ├── database.sql     # MySQL schema
│   ├── Dockerfile       # PHP 8.2 + Apache + PDO MySQL
│   └── health.php       # Deployment health check
├── render.yaml          # Render Blueprint configuration
└── README.md
```

The backend directory includes the existing server-rendered Weather.io pages as well as the API. The Netlify frontend can use the API without moving or duplicating authentication code.

## Backend API

| Endpoint | Method | Authentication | Purpose |
| --- | --- | --- | --- |
| `/api/login.php` | `POST` | No | Validate credentials and create an HttpOnly session |
| `/api/me.php` | `GET` | Yes | Return the current logged-in user |
| `/api/logout.php` | `POST` | CSRF header | Destroy the current session |
| `/health.php` | `GET` | No | Return backend health status |

Login request example:

```js
const response = await fetch(`${BACKEND_URL}/api/login.php`, {
  method: 'POST',
  credentials: 'include',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email, password })
});

const result = await response.json();
```

After login, retain the returned `csrf_token` and send it as `X-CSRF-Token` when calling logout. Always use `credentials: 'include'` for requests that use the session cookie.

## Deploy with Render and an external MySQL database

Render runs the PHP service, while MySQL must be hosted separately. Railway, Aiven, DigitalOcean Managed MySQL, and PlanetScale-compatible MySQL providers are possible choices. Use a provider that supplies a normal MySQL connection and allows the Render service to connect.

### 1. Create the MySQL database

Create a database and note its host, port, database name, username, and password. Import the schema from `backend/database.sql` using the provider's SQL console or a local MySQL client:

```bash
mysql -h DB_HOST -P DB_PORT -u DB_USER -p DB_NAME < backend/database.sql
```

Do not commit database passwords or `.env` files.

### 2. Create the Render service

1. Push this repository to GitHub.
2. In Render, choose **New → Blueprint** and select this repository.
3. Render reads `render.yaml`, builds the Docker service from `backend/`, and checks `/health.php`.
4. Set these environment variables in the Render dashboard:

| Variable | Value |
| --- | --- |
| `DB_HOST` | MySQL provider hostname |
| `DB_PORT` | Usually `3306` |
| `DB_NAME` | MySQL database name |
| `DB_USER` | MySQL username |
| `DB_PASSWORD` | MySQL password |
| `FRONTEND_URL` | Exact Netlify origin, for example `https://your-site.netlify.app` |

Do not set `FRONTEND_URL` to `*`. Multiple exact origins may be separated by commas if needed.

### 3. Create the first administrator

After the Render service is live:

1. Open `https://YOUR-BACKEND.onrender.com/admin/setup.php`.
2. Create the first administrator account.
3. Confirm that `/login.php` and `/admin/index.php` work.
4. Delete `backend/admin/setup.php` from the repository and deploy again. This one-time setup page is intentionally not needed after the first admin exists.

### 4. Connect Netlify

Set the frontend's backend URL to the Render service URL, for example:

```js
const BACKEND_URL = 'https://weather-io-backend.onrender.com';
```

Use the API endpoints above with `credentials: 'include'`. Set `FRONTEND_URL` in Render to the exact public Netlify URL, including `https://` and excluding a trailing slash.

Because the frontend and backend are on different domains, production must use HTTPS. The backend configures credentialed CORS only for the origin in `FRONTEND_URL`.

## Local development

Requirements: PHP 8.2+, MySQL 8+, and the `pdo_mysql` PHP extension.

```bash
cd backend
mysql -u root -p -e "CREATE DATABASE weather_io CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p weather_io < database.sql
php -S localhost:8080
```

Open `http://localhost:8080/admin/setup.php` once to create an admin. For local development, the backend defaults to:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=weather_io
DB_USER=root
DB_PASSWORD=
```

To test the API from a local frontend, set `FRONTEND_URL=http://localhost:3000` and send requests with `credentials: 'include'`.

## Security notes

- Passwords are hashed with PHP `password_hash()` and verified with `password_verify()`.
- Database operations use PDO prepared statements.
- Sessions use HttpOnly cookies and regenerate the session ID after login.
- API CORS is allowlisted through `FRONTEND_URL`; wildcard CORS is not supported for authenticated requests.
- Remove `backend/admin/setup.php` after creating the first administrator.

## License

This project is an internship project by Anant Vijay.
