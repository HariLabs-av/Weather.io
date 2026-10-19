<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/schema.php';
require_once __DIR__ . '/../auth/auth.php';

$error = '';
$success = '';

try {
    ensureSchema();
    $userCount = (int) database()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($userCount > 0) {
        $success = 'An account already exists. Delete this setup file and use the login page.';
    }
} catch (PDOException $exception) {
    $error = 'Database is not ready. Import database.sql and check your database settings.';
    $userCount = 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userCount === 0) {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Please try again.';
    } elseif ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Enter a name, a valid email, and a password of at least 8 characters.';
    } else {
        $statement = database()->prepare(
            'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, "admin")'
        );
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        $success = 'Admin account created. Delete admin/setup.php now, then log in.';
        $userCount = 1;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Initial setup - Weather.io</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5"><div class="row justify-content-center"><div class="col-sm-10 col-md-6 col-lg-5">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h1 class="h3">Create administrator</h1>
        <p class="text-secondary">This page is available only until the first account is created.</p>
        <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success !== ''): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
        <?php if ($userCount === 0): ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <div class="mb-3"><label class="form-label" for="name">Name</label><input class="form-control" id="name" name="name" required></div>
            <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" required></div>
            <div class="mb-4"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" minlength="8" required></div>
            <button class="btn btn-primary w-100" type="submit">Create admin account</button>
        </form>
        <?php else: ?><a class="btn btn-primary" href="../login.php">Go to login</a><?php endif; ?>
    </div></div>
</div></div></main>
</body>
</html>
