<?php
include_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    header('Location: announcements.php');
    exit();
}

$id = (int)$_GET['id'];
$errors = [];
$success = false;

// Fetch announcement data
$announcement = db_query(
    "SELECT * FROM announcements WHERE annc_id = ? AND dept_id = ?",
    [$id, $_SESSION['user']['dept_id']]
)->fetch();

if (!$announcement) {
    header('Location: announcements.php');
    exit();
}

// Form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $publish_date = $_POST['publish_date'] ?? date('Y-m-d H:i:s');
    $expiry_date = $_POST['expiry_date'] ?? '';

    // Validation
    if (empty($title)) $errors['title'] = 'Title is required';
    if (empty($content)) $errors['content'] = 'Content is required';
    if (empty($expiry_date)) $errors['expiry_date'] = 'Expiry date is required';

    if (empty($errors)) {
        try {
            // Update database
            $result = db_query(
                "UPDATE announcements 
                 SET title = ?, content = ?, priority = ?, publish_date = ?, expiry_date = ?
                 WHERE annc_id = ? AND dept_id = ?",
                [
                    $title,
                    $content,
                    $status,
                    $publish_date,
                    $expiry_date,
                    $id,
                    $_SESSION['user']['dept_id']
                ]
            );

            if ($result) {
                $success = true;
                // Refresh announcement data
                $announcement = db_query(
                    "SELECT * FROM announcements WHERE annc_id = ? AND dept_id = ?",
                    [$id, $_SESSION['user']['dept_id']]
                );
            }
        } catch (Exception $e) {
            $errors['general'] = 'Error updating announcement: ' . $e->getMessage();
        }
    }
} else {
    // Pre-populate form with existing data
    $title = $announcement['title'];
    $content = $announcement['content'];
    $status = $announcement['priority'];
    $publish_date = date('Y-m-d\TH:i', strtotime($announcement['publish_date']));
    $expiry_date = date('Y-m-d\TH:i', strtotime($announcement['expiry_date']));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Announcement - UniPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo file_exists('../assets/css/admin.css') ? '../assets/css/admin.css' : '../../assets/css/admin.css'; ?>">
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <div class="d-flex">
        <div class="main-content w-100" id="mainContent">

            <?php include_once('../../includes/navbar.php') ?>

            <div class="container-fluid mt-4">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="form-container">
                            <h2 class="mb-4"><i class="bi bi-pencil me-2"></i>Edit Announcement</h2>

                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    Announcement updated successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($errors['general'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <?= htmlspecialchars($errors['general']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title *</label>
                                    <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                                        id="title" name="title" value="<?= htmlspecialchars($title) ?>" required>
                                    <?php if (isset($errors['title'])): ?>
                                        <div class="invalid-feedback"><?= $errors['title'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label">Content *</label>
                                    <textarea class="form-control <?= isset($errors['content']) ? 'is-invalid' : '' ?>"
                                        id="content" name="content" rows="6" required><?= htmlspecialchars($content) ?></textarea>
                                    <?php if (isset($errors['content'])): ?>
                                        <div class="invalid-feedback"><?= $errors['content'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="low" <?= ($status ?? 'low') == 'low' ? 'selected' : '' ?>>low</option>
                                            <option value="medium" <?= ($status ?? '') == 'medium' ? 'selected' : '' ?>>medium</option>
                                            <option value="high" <?= ($status ?? '') == 'high' ? 'selected' : '' ?>>high</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="publish_date" class="form-label">Publish Date</label>
                                        <input type="datetime-local" class="form-control" id="publish_date" name="publish_date"
                                            value="<?= htmlspecialchars($publish_date) ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="expiry_date" class="form-label">Expiry Date *</label>
                                    <input type="datetime-local" class="form-control <?= isset($errors['expiry_date']) ? 'is-invalid' : '' ?>"
                                        id="expiry_date" name="expiry_date"
                                        value="<?= htmlspecialchars($expiry_date) ?>" required>
                                    <?php if (isset($errors['expiry_date'])): ?>
                                        <div class="invalid-feedback"><?= $errors['expiry_date'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="announcements.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i>Back to Announcements
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i>Update Announcement
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set minimum expiry date to today
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const timezoneOffset = now.getTimezoneOffset() * 60000;
            const localISOTime = new Date(now - timezoneOffset).toISOString().slice(0, 16);
            document.getElementById('expiry_date').min = localISOTime;
        });
    </script>
</body>

</html>