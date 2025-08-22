<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

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
            "INSERT INTO timetable_slots (course_id, day_of_week, start_time, end_time, room_number, building, valid_from, valid_to, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [$course_id, $day, $start, $end, $room, $building, $valid_from, $valid_to]
        );
        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <div class="d-flex">
        <div class="main-content w-100" id="mainContent">
            <?php include_once('../../includes/navbar.php') ?>

            <h3>New Timetable Slot</h3>

            <div class="card col-8  m-auto">
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">Slot created.</div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Course *</label>
                            <select name="course_id" class="form-select <?= isset($errors['course_id']) ? 'is-invalid' : '' ?>" required>
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['course_id'] ?>" <?= (isset($_POST['course_id']) && $_POST['course_id'] == $c['course_id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['course_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Day of Week (0=Sun)</label>
                                <input type="number" name="day_of_week" class="form-control" value="<?= htmlspecialchars($_POST['day_of_week'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="start_time" class="form-control" value="<?= htmlspecialchars($_POST['start_time'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" name="end_time" class="form-control" value="<?= htmlspecialchars($_POST['end_time'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room Number</label>
                            <input type="text" name="room_number" class="form-control" value="<?= htmlspecialchars($_POST['room_number'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Building</label>
                            <input type="text" name="building" class="form-control" value="<?= htmlspecialchars($_POST['building'] ?? '') ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valid From</label>
                                <input type="date" name="valid_from" class="form-control" value="<?= htmlspecialchars($_POST['valid_from'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valid To</label>
                                <input type="date" name="valid_to" class="form-control" value="<?= htmlspecialchars($_POST['valid_to'] ?? '') ?>">
                            </div>
                        </div>
                        <button class="btn btn-primary">Create Slot</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

</html>