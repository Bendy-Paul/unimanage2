<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

$event_id = $_GET['id'] ?? null;
if (!$event_id) {
    header('Location: index.php');
    exit();
}

// Fetch event
$stmt = db_query("SELECT * FROM events WHERE event_id = ?", [$event_id]);
$event = $stmt->fetch();
if (!$event) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start = $_POST['start_datetime'] ?? '';
    $end = $_POST['end_datetime'] ?? '';
    $venue = trim($_POST['venue'] ?? '');
    $dept_id = $_POST['dept_id'] ?? null;

    if (empty($title)) $errors['title'] = 'Title is required';
    if (empty($start)) $errors['start'] = 'Start datetime is required';
    if (empty($end)) $errors['end'] = 'End datetime is required';

    if (empty($errors)) {
        db_query(
            "UPDATE events SET title = ?, description = ?, start_datetime = ?, end_datetime = ?, venue = ?, dept_id = ? WHERE event_id = ?",
            [$title, $description, $start, $end, $venue, $dept_id, $event_id]
        );
        $success = true;
        // refresh event
        $stmt = db_query("SELECT * FROM events WHERE event_id = ?", [$event_id]);
        $event = $stmt->fetch();
    }
}

$dept_stmt = db_query("SELECT * FROM departments");
$departments = $dept_stmt->fetchAll();
?>

<div class="d-flex">
    <div class="main-content w-100" id="mainContent">

        <?php include_once('../../includes/navbar.php') ?>

        <h3>Edit Event</h3>


        <div class="card col-8 m-auto mt-4">
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">Event updated successfully.</div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['title'] ?? $event['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($_POST['description'] ?? $event['description']) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start *</label>
                            <input type="datetime-local" name="start_datetime" class="form-control <?= isset($errors['start']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['start_datetime'] ?? $event['start_datetime']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End *</label>
                            <input type="datetime-local" name="end_datetime" class="form-control <?= isset($errors['end']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['end_datetime'] ?? $event['end_datetime']) ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Venue</label>
                        <input type="text" name="venue" class="form-control" value="<?= htmlspecialchars($_POST['venue'] ?? $event['venue']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="dept_id" class="form-select">
                            <option value="">-- Any --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['dept_id'] ?>" <?= ((isset($_POST['dept_id']) && $_POST['dept_id'] == $d['dept_id']) || (!isset($_POST['dept_id']) && $event['dept_id'] == $d['dept_id'])) ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary">Save Changes</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>