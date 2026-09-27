<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/auth.php';
requireAdmin();

$userCount = (int) database()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$admins = (int) database()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$users = database()->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin panel - Weather.io</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-primary"><div class="container">
    <a class="navbar-brand fw-bold" href="../index.php">Weather.io Admin</a>
    <div class="d-flex align-items-center gap-3 text-white"><span><?= e((string) currentUser()['name']) ?></span><a class="btn btn-sm btn-light" href="../logout.php">Log out</a></div>
</div></nav>
<main class="container py-4">
    <h1 class="h3 mb-4">Dashboard</h1>
    <div class="row g-3 mb-4">
        <div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-secondary">Total accounts</div><div class="display-6 fw-bold"><?= $userCount ?></div></div></div></div>
        <div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-secondary">Administrators</div><div class="display-6 fw-bold"><?= $admins ?></div></div></div></div>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-body">
        <h2 class="h5 mb-3">Accounts</h2>
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th></tr></thead><tbody>
        <?php foreach ($users as $user): ?><tr><td><?= e($user['name']) ?></td><td><?= e($user['email']) ?></td><td><span class="badge text-bg-<?= $user['role'] === 'admin' ? 'primary' : 'secondary' ?>"><?= e($user['role']) ?></span></td><td><?= e($user['created_at']) ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    </div></div>
</main>
</body>
</html>
