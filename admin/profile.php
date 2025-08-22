<?php
include_once '../includes/sidebar.php';


require_once '../includes/db_connect.php';



$user = $_SESSION['user'] ?? null;
$userId = $user['id'] ?? $user['user_id'] ?? null;

$errors = [];
$success = false;

// fetch current data
$stmt = db_query('SELECT * FROM users WHERE user_id = ? LIMIT 1', [$userId]);
$current = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $current_password = $_POST['current_password'] ?? '';

    if ($name === '') $errors['name'] = 'Name required';
    if ($email === '') $errors['email'] = 'Email required';

    // verify current password
    if (empty($current_password) || !password_verify($current_password, $current['password_hash'] ?? $current['password'])) {
        $errors['current_password'] = 'Current password is incorrect';
    }

    if (empty($errors)) {
        db_query('UPDATE users SET name = ?, email = ?, contact = ? WHERE user_id = ? OR id = ?', [$name, $email, $contact, $userId, $userId]);

        if (!empty($_POST['new_password'])) {
            $new = $_POST['new_password'];
            if (strlen($new) < 8) {
                $errors['new_password'] = 'New password must be at least 8 characters';
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                db_query('UPDATE users SET password_hash = ? WHERE user_id = ? OR id = ?', [$hash, $userId, $userId]);
            }
        }

        if (empty($errors)) {
            $success = true;
            // reload current
            $stmt = db_query('SELECT * FROM users WHERE user_id = ? OR id = ? LIMIT 1', [$userId, $userId]);
            $current = $stmt->fetch();
        }
    }
}
?>
<div class="d-flex">
    <div class="main-content w-100" id="mainContent">

        <?php include_once('../includes/navbar.php') ?>

        <div class="container-fluid mt-4">
            <h3>My Profile</h3>
            <div class="card col-md-8 mt-3  m-auto">
                <div class="card-body">
                    <?php if ($success): ?><div class="alert alert-success">Profile updated.</div><?php endif; ?>
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full name</label>
                            <input name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($current['name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($current['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact</label>
                            <input name="contact" class="form-control" value="<?= htmlspecialchars($current['contact'] ?? '') ?>">
                        </div>

                        <div class="col-12 mt-3">
                            <label class="form-label">Current Password (required)</label>
                            <input type="password" name="current_password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">New Password (leave empty to keep)</label>
                            <input type="password" name="new_password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary">Save</button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>