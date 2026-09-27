<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth/auth.php';

if (isLoggedIn()) {
    header('Location: admin/index.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Please try again.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter a valid email address and password.';
    } else {
        try {
            $statement = database()->prepare(
                'SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1'
            );
            $statement->execute(['email' => strtolower($email)]);
            $user = $statement->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                unset($user['password_hash']);
                $_SESSION['user'] = $user;
                header('Location: admin/index.php');
                exit;
            }

            $error = 'The email or password is incorrect.';
        } catch (PDOException $exception) {
            $error = 'Unable to connect to the database. Check your configuration.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Weather.io</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-sm-10 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <a href="index.php" class="text-decoration-none text-secondary">← Back to Weather.io</a>
                    <h1 class="h3 mt-4 mb-1">Welcome back</h1>
                    <p class="text-secondary mb-4">Sign in to the Weather.io admin panel.</p>
                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?= e($error) ?></div>
                    <?php endif; ?>
                    <form method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" id="email" name="email" type="email" value="<?= e($email) ?>" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-control" id="password" name="password" type="password" required>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Log in</button>
                    </form>
                    <p class="small text-secondary mt-4 mb-0">First time here? Run <code>admin/setup.php</code> after importing the database schema.</p>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
