# Weather.io backend

The root [`README.md`](../README.md) contains the complete deployment and database setup guide.

This directory is the Docker document root for the Render web service. It contains:

- Server-rendered Weather.io pages
- PHP/MySQL login, registration, logout, and session handling
- JSON authentication endpoints under `api/`
- A protected administrator dashboard under `admin/`
- `database.sql`, the repeatable MySQL schema
- `admin/setup.php`, which creates the `users` table and the first administrator

After creating the first administrator, remove `admin/setup.php` from the repository and redeploy.
