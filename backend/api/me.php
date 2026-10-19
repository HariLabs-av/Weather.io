<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    apiResponse(['error' => 'Method not allowed.'], 405);
}

if (!isLoggedIn()) {
    apiResponse(['error' => 'Authentication required.'], 401);
}

apiResponse(['user' => publicUser(currentUser()), 'csrf_token' => csrfToken()]);
