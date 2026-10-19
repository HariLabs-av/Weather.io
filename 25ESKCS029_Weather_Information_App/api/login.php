<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiResponse(['error' => 'Method not allowed.'], 405);
}

$body = requestBody();
$email = strtolower(trim((string) ($body['email'] ?? '')));
$password = (string) ($body['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    apiResponse(['error' => 'Enter a valid email address and password.'], 422);
}

try {
    $statement = database()->prepare(
        'SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1'
    );
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();
} catch (PDOException $exception) {
    apiResponse(['error' => 'The database is unavailable.'], 503);
}

if (!$user || !password_verify($password, $user['password_hash'])) {
    apiResponse(['error' => 'The email or password is incorrect.'], 401);
}

session_regenerate_id(true);
$_SESSION['user'] = publicUser($user);
apiResponse(['user' => $_SESSION['user'], 'csrf_token' => csrfToken()]);
