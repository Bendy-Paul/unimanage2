<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/header.php';


$user = $_SESSION['user'] ?? null;
$userId = $user['id'] ?? $user['user_id'] ?? null;

$errors = [];
$success = false;

// fetch current data
$stmt = db_query('SELECT * FROM users WHERE user_id = ?  LIMIT 1', [$userId]);
$current = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $current_password = $_POST['current_password'] ?? '';

    if ($name === '') $errors['name'] = 'Name required';
    if ($email === '') $errors['email'] = 'Email required';

    // verify current password
    if (empty($current_password) || !password_verify($current_password, $current['password_hash'] ?? $current['password'])) {
        $errors['current_password'] = 'Current password is incorrect';
    }

    if (empty($errors)) {
        // build update
        db_query('UPDATE users SET name = ?, email = ?, contact = ?, address = ? WHERE user_id = ?', [$name, $email, $contact, $address, $userId]);

        // handle password change
        if (!empty($_POST['new_password'])) {
            $new = $_POST['new_password'];
            if (strlen($new) < 8) {
                $errors['new_password'] = 'New password must be at least 8 characters';
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                db_query('UPDATE users SET password_hash = ? WHERE user_id = ?', [$hash, $userId]);
            }
        }

        if (empty($errors)) {
            $success = true;
            // reload current
            $stmt = db_query('SELECT * FROM users WHERE user_id = ? LIMIT 1', [$userId]);
            $current = $stmt->fetch();
        }
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/student.css">
</head>

<body>
    <div class="container py-4">
        <h3>Edit Profile</h3>
        <div class="card col-md-6 mt-3 m-auto">
            <div class="card-body">
                <?php if ($success): ?><div class="alert alert-success">Profile updated.</div><?php endif; ?>
                <form method="POST" class="row">
                    <div class="mb-3">
                        <div class="">
                            <label class="form-label">Full name</label>
                            <input name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($current['name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="">
                            <label class="form-label">Email</label>
                            <input name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($current['email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="">
                            <label class="form-label">Contact</label>
                            <input name="contact" class="form-control" value="<?= htmlspecialchars($current['contact'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="5"><?= htmlspecialchars($current['address'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <hr>
                    <div class="mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Current Password (required to save changes)</label>
                            <input type="password" name="current_password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="col-md-8">
                            <label class="form-label">New password (leave empty to keep)</label>
                            <input type="password" name="new_password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>">
                        </div>
                    </div>
                    <div class="">
                        <div class="col-12">
                            <button class="btn btn-primary">Save</button>
                            <a href="profile.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer"></footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/student.js"></script>
</body>

</html>