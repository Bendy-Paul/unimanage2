<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

$slot_id = $_GET['id'] ?? null;
if (!$slot_id) {
    header('Location: index.php');
    exit();
}

$stmt = db_query("SELECT * FROM timetable_slots WHERE slot_id = ?", [$slot_id]);
$slot = $stmt->fetch();
if (!$slot) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = false;
$courses = db_query("SELECT course_id, course_name FROM courses")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'] ?? null;
    $day = $_POST['day_of_week'] ?? null;
    $start = $_POST['start_time'] ?? null;
    $end = $_POST['end_time'] ?? null;
    $room = trim($_POST['room_number'] ?? '');
    $building = trim($_POST['building'] ?? '');
    $valid_from = $_POST['valid_from'] ?? null;
    $valid_to = $_POST['valid_to'] ?? null;

    if (empty($course_id)) $errors['course_id'] = 'Course is required';
    if ($day === null) $errors['day_of_week'] = 'Day is required';
    if (empty($start)) $errors['start_time'] = 'Start time is required';
    if (empty($end)) $errors['end_time'] = 'End time is required';

    if (empty($errors)) {
        db_query(
            "UPDATE timetable_slots SET course_id = ?, day_of_week = ?, start_time = ?, end_time = ?, room_number = ?, building = ?, valid_from = ?, valid_to = ?, updated_at = NOW() WHERE slot_id = ?",
            [$course_id, $day, $start, $end, $room, $building, $valid_from, $valid_to, $slot_id]
        );
        $success = true;
        $stmt = db_query("SELECT * FROM timetable_slots WHERE slot_id = ?", [$slot_id]);
        $slot = $stmt->fetch();
    }
}
?>
<div class="d-flex">
    <div class="main-content w-100" id="mainContent">
        <?php include_once('../../includes/navbar.php') ?>

        <h3>Edit Slot</h3>
        <?php if ($success): ?>
            <div class="alert alert-success">Slot updated.</div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Course *</label>
                        <select name="course_id" class="form-select <?= isset($errors['course_id']) ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Select Course --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['course_id'] ?>" <?= ((isset($_POST['course_id']) && $_POST['course_id'] == $c['course_id']) || (!isset($_POST['course_id']) && $slot['course_id'] == $c['course_id'])) ? 'selected' : '' ?>><?= htmlspecialchars($c['course_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Day of Week (0=Sun)</label>
                            <input type="number" name="day_of_week" class="form-control" value="<?= htmlspecialchars($_POST['day_of_week'] ?? $slot['day_of_week']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" class="form-control" value="<?= htmlspecialchars($_POST['start_time'] ?? $slot['start_time']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control" value="<?= htmlspecialchars($_POST['end_time'] ?? $slot['end_time']) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Room Number</label>
                        <input type="text" name="room_number" class="form-control" value="<?= htmlspecialchars($_POST['room_number'] ?? $slot['room_number']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Building</label>
                        <input type="text" name="building" class="form-control" value="<?= htmlspecialchars($_POST['building'] ?? $slot['building']) ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valid From</label>
                            <input type="date" name="valid_from" class="form-control" value="<?= htmlspecialchars($_POST['valid_from'] ?? $slot['valid_from']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valid To</label>
                            <input type="date" name="valid_to" class="form-control" value="<?= htmlspecialchars($_POST['valid_to'] ?? $slot['valid_to']) ?>">
                        </div>
                    </div>
                    <button class="btn btn-primary">Save Changes</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>