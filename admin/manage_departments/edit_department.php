<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

$dept_id = $_GET['id'] ?? null;
if (!$dept_id) {
    header('Location: index.php');
    exit();
}

$stmt = db_query("SELECT * FROM departments WHERE dept_id = ?", [$dept_id]);
$dept = $stmt->fetch();
if (!$dept) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($code)) $errors['code'] = 'Department code is required';
    if (empty($name)) $errors['name'] = 'Department name is required';

    if (empty($errors)) {
        db_query("UPDATE departments SET code = ?, name = ?, description = ?, updated_at = NOW() WHERE dept_id = ?", [$code, $name, $description, $dept_id]);
        $success = true;
        $stmt = db_query("SELECT * FROM departments WHERE dept_id = ?", [$dept_id]);
        $dept = $stmt->fetch();
    }
}
?>

<div class="d-flex">
    <div class="main-content w-100" id="mainContent">
        <?php include_once('../../includes/navbar.php') ?>

        <h3>Edit Department</h3>

        <div class="card col-6 m-auto mt-4">
            <?php if ($success): ?>
                <div class="alert alert-success">Department updated successfully.</div>
            <?php endif; ?>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Department Code *</label>
                        <input type="text" name="code" class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['code'] ?? $dept['code']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department Name *</label>
                        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['name'] ?? $dept['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($_POST['description'] ?? $dept['description']) ?></textarea>
                    </div>
                    <button class="btn btn-primary">Save Changes</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
