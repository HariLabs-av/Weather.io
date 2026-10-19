<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    apiResponse(['error' => 'Method not allowed.'], 405);
}

$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
if (!verifyCsrfToken($token)) {
    apiResponse(['error' => 'Invalid CSRF token.'], 419);
}

$_SESSION = [];
session_destroy();
apiResponse(['message' => 'Logged out.']);
