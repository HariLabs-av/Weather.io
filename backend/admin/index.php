<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/auth.php';
requireAdmin();

$error = '';
$success = '';
$editUser = null;
$currentUserId = (int) (currentUser()['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Please try again.';
    } else {
        try {
            if ($action === 'create') {
                $name = trim((string) ($_POST['name'] ?? ''));
                $email = strtolower(trim((string) ($_POST['email'] ?? '')));
                $password = (string) ($_POST['password'] ?? '');
                $role = (string) ($_POST['role'] ?? 'user');

                if ($name === '' || strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Enter a name and a valid email address.';
                } elseif (strlen($password) < 8) {
                    $error = 'The password must contain at least 8 characters.';
                } elseif (!in_array($role, ['admin', 'user'], true)) {
                    $error = 'Choose a valid account role.';
                } else {
                    $statement = database()->prepare(
                        'INSERT INTO users (name, email, password_hash, role)
                         VALUES (:name, :email, :password_hash, :role)'
                    );
                    $statement->execute([
                        'name' => $name,
                        'email' => $email,
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        'role' => $role,
                    ]);
                    $success = 'User account created successfully.';
                }
            } elseif ($action === 'update') {
                $userId = (int) ($_POST['user_id'] ?? 0);
                $name = trim((string) ($_POST['name'] ?? ''));
                $email = strtolower(trim((string) ($_POST['email'] ?? '')));
                $password = (string) ($_POST['password'] ?? '');
                $role = (string) ($_POST['role'] ?? 'user');

                if ($userId < 1) {
                    $error = 'Invalid user account.';
                } elseif ($name === '' || strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Enter a name and a valid email address.';
                } elseif ($password !== '' && strlen($password) < 8) {
                    $error = 'A new password must contain at least 8 characters.';
                } elseif (!in_array($role, ['admin', 'user'], true)) {
                    $error = 'Choose a valid account role.';
                } elseif ($userId === $currentUserId && $role !== 'admin') {
                    $error = 'You cannot remove administrator access from your own account.';
                } else {
                    $targetStatement = database()->prepare('SELECT id, role FROM users WHERE id = :id LIMIT 1');
                    $targetStatement->execute(['id' => $userId]);
                    $target = $targetStatement->fetch();

                    if (!$target) {
                        $error = 'User account not found.';
                    } elseif ($target['role'] === 'admin' && $role === 'user') {
                        $adminCount = (int) database()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
                        $error = $adminCount <= 1 ? 'The last administrator cannot be demoted.' : '';
                    }

                    if ($error === '') {
                        if ($password !== '') {
                            $statement = database()->prepare(
                                'UPDATE users
                                 SET name = :name, email = :email, password_hash = :password_hash, role = :role
                                 WHERE id = :id'
                            );
                            $statement->execute([
                                'name' => $name,
                                'email' => $email,
                                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                                'role' => $role,
                                'id' => $userId,
                            ]);
                        } else {
                            $statement = database()->prepare(
                                'UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id'
                            );
                            $statement->execute([
                                'name' => $name,
                                'email' => $email,
                                'role' => $role,
                                'id' => $userId,
                            ]);
                        }

                        if ($userId === $currentUserId) {
                            $_SESSION['user']['name'] = $name;
                            $_SESSION['user']['email'] = $email;
                        }
                        $success = 'User account updated successfully.';
                    }
                }
            } elseif ($action === 'delete') {
                $userId = (int) ($_POST['user_id'] ?? 0);

                if ($userId === $currentUserId) {
                    $error = 'You cannot delete your own administrator account.';
                } elseif ($userId < 1) {
                    $error = 'Invalid user account.';
                } else {
                    $targetStatement = database()->prepare('SELECT role FROM users WHERE id = :id LIMIT 1');
                    $targetStatement->execute(['id' => $userId]);
                    $targetRole = $targetStatement->fetchColumn();

                    if ($targetRole === false) {
                        $error = 'User account not found.';
                    } elseif ($targetRole === 'admin') {
                        $adminCount = (int) database()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
                        $error = $adminCount <= 1 ? 'The last administrator cannot be deleted.' : '';
                    }

                    if ($error === '') {
                        $statement = database()->prepare('DELETE FROM users WHERE id = :id');
                        $statement->execute(['id' => $userId]);
                        $success = 'User account deleted successfully.';
                    }
                }
            } else {
                $error = 'Unsupported account action.';
            }
        } catch (PDOException $exception) {
            $error = $exception->getCode() === '23000'
                ? 'An account with that email already exists.'
                : 'Unable to update accounts. Check the database configuration.';
        }
    }
}

$userCount = (int) database()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$admins = (int) database()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$users = database()->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();

if (isset($_GET['edit'])) {
    $editStatement = database()->prepare('SELECT id, name, email, role FROM users WHERE id = :id LIMIT 1');
    $editStatement->execute(['id' => (int) $_GET['edit']]);
    $editUser = $editStatement->fetch() ?: null;
}
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
    <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success !== ''): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <div class="row g-3 mb-4">
        <div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-secondary">Total accounts</div><div class="display-6 fw-bold"><?= $userCount ?></div></div></div></div>
        <div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-secondary">Administrators</div><div class="display-6 fw-bold"><?= $admins ?></div></div></div></div>
    </div>

    <div class="card border-0 shadow-sm mb-4"><div class="card-body">
        <h2 class="h5 mb-3">Create account</h2>
        <form method="post" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action" value="create">
            <div class="col-md-3"><label class="form-label" for="create-name">Name</label><input class="form-control" id="create-name" name="name" maxlength="100" required></div>
            <div class="col-md-3"><label class="form-label" for="create-email">Email</label><input class="form-control" id="create-email" name="email" type="email" required></div>
            <div class="col-md-2"><label class="form-label" for="create-role">Role</label><select class="form-select" id="create-role" name="role"><option value="user">User</option><option value="admin">Admin</option></select></div>
            <div class="col-md-2"><label class="form-label" for="create-password">Password</label><input class="form-control" id="create-password" name="password" type="password" minlength="8" required></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100" type="submit">Create user</button></div>
        </form>
    </div></div>

    <?php if ($editUser !== null): ?>
    <div class="card border-primary shadow-sm mb-4"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Edit account</h2><a class="btn btn-sm btn-outline-secondary" href="index.php">Cancel</a></div>
        <form method="post" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="user_id" value="<?= (int) $editUser['id'] ?>">
            <div class="col-md-3"><label class="form-label" for="edit-name">Name</label><input class="form-control" id="edit-name" name="name" value="<?= e($editUser['name']) ?>" maxlength="100" required></div>
            <div class="col-md-3"><label class="form-label" for="edit-email">Email</label><input class="form-control" id="edit-email" name="email" type="email" value="<?= e($editUser['email']) ?>" required></div>
            <div class="col-md-2"><label class="form-label" for="edit-role">Role</label><select class="form-select" id="edit-role" name="role" <?= (int) $editUser['id'] === $currentUserId ? 'disabled' : '' ?>><option value="user" <?= $editUser['role'] === 'user' ? 'selected' : '' ?>>User</option><option value="admin" <?= $editUser['role'] === 'admin' ? 'selected' : '' ?>>Admin</option></select><?php if ((int) $editUser['id'] === $currentUserId): ?><input type="hidden" name="role" value="admin"><?php endif; ?></div>
            <div class="col-md-2"><label class="form-label" for="edit-password">New password</label><input class="form-control" id="edit-password" name="password" type="password" minlength="8"><div class="form-text">Leave blank to keep it.</div></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100" type="submit">Save changes</button></div>
        </form>
    </div></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm"><div class="card-body">
        <h2 class="h5 mb-3">Accounts</h2>
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th class="text-end">Actions</th></tr></thead><tbody>
        <?php foreach ($users as $user): ?><tr>
            <td><?= e($user['name']) ?><?= (int) $user['id'] === $currentUserId ? ' <span class="badge text-bg-info">You</span>' : '' ?></td>
            <td><?= e($user['email']) ?></td>
            <td><span class="badge text-bg-<?= $user['role'] === 'admin' ? 'primary' : 'secondary' ?>"><?= e($user['role']) ?></span></td>
            <td><?= e($user['created_at']) ?></td>
            <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-primary" href="?edit=<?= (int) $user['id'] ?>">Edit</a>
                <?php if ((int) $user['id'] !== $currentUserId): ?><form method="post" class="d-inline" onsubmit="return confirm('Delete this account?');"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Delete</button></form><?php endif; ?>
            </td>
        </tr><?php endforeach; ?>
        </tbody></table></div>
    </div></div>
</main>
</body>
</html>
