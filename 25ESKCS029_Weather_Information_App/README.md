# Weather.io

## PHP/MySQL authentication

The project includes a protected admin panel at `admin/index.php`.

## Deploying on Render

The included `render.yaml` deploys the PHP application as a Docker web service. Render does not offer MySQL, so create a MySQL database with a compatible provider such as Railway, Aiven, or PlanetScale and copy its connection values into the Render service environment:

```text
DB_HOST=<mysql-host>
DB_PORT=<mysql-port>
DB_NAME=<database-name>
DB_USER=<database-user>
DB_PASSWORD=<database-password>
```

In Render, create a Blueprint from this repository, or configure a Docker web service with root directory `25ESKCS029_Weather_Information_App`. After deployment, import `database.sql` into the external MySQL database, open `/admin/setup.php` on the deployed URL, create the administrator, and delete `admin/setup.php` from the repository.

Because the frontend and backend are deployed by the same service, login links remain same-origin and do not require CORS configuration.

## Local/setup instructions

1. Create the database by importing `database.sql` into MySQL.
2. Configure the connection with environment variables: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD`. Defaults are `127.0.0.1`, `3306`, `weather_io`, `root`, and an empty password.
3. Open `admin/setup.php` once and create the first administrator account.
4. Delete `admin/setup.php` after setup and use `login.php` to sign in.

Passwords are stored with PHP's `password_hash()` and all database queries use PDO prepared statements. The admin panel currently provides account counts and a list of registered accounts; only administrators can access it.
