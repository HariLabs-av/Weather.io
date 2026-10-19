<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Please try again.';
    } elseif ($name === '' || strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a name and a valid email address.';
    } elseif (strlen($password) < 8 || $password !== $confirmation) {
        $error = 'Passwords must match and contain at least 8 characters.';
    } else {
        try {
            $statement = database()->prepare(
                'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, "user")'
            );
            $statement->execute([
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            header('Location: login.php?registered=1');
            exit;
        } catch (PDOException $exception) {
            $error = $exception->getCode() === '23000'
                ? 'An account with this email already exists.'
                : 'Unable to create your account. Check the database configuration.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create account - Weather.io</title>
    <link rel="stylesheet" href="partial/darkstyle.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-sm-10 col-md-6 col-lg-5">
            <div class="card border-0">
                <div class="card-body p-4">
                    <a href="index.php" class="text-decoration-none text-secondary">← Back to Weather.io</a>
                    <h1 class="h3 mt-4 mb-1">Create your account</h1>
                    <p class="text-secondary mb-4">Register as a Weather.io user.</p>
                    <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                    <form method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <div class="mb-3"><label class="form-label" for="name">Name</label><input class="form-control" id="name" name="name" value="<?= e($name) ?>" maxlength="100" required autofocus></div>
                        <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" value="<?= e($email) ?>" required></div>
                        <div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" minlength="8" required></div>
                        <div class="mb-4"><label class="form-label" for="password_confirmation">Confirm password</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="8" required></div>
                        <button class="btn btn-primary w-100" type="submit">Create account</button>
                    </form>
                    <p class="small text-secondary mt-4 mb-0">Already registered? <a href="login.php">Log in</a></p>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="partial/darkstyle.js"></script>
</body>
</html>
