<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    header('Location: index.php');
    exit();
}

$stmt = db_query("SELECT * FROM courses WHERE course_id = ?", [$course_id]);
$course = $stmt->fetch();
if (!$course) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = false;
$departments = db_query("SELECT * FROM departments ORDER BY name")->fetchAll();
$lecturers = db_query("SELECT user_id, name FROM users WHERE role = 'faculty' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_code = trim($_POST['course_code'] ?? '');
    $course_name = trim($_POST['course_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $target_year = $_POST['target_year'] ?? null;
    $credit_hours = $_POST['credit_hours'] ?? null;
    $academic_year = $_POST['academic_year'] ?? null;
    $semester = trim($_POST['semester'] ?? '');
    $department_id = $_POST['department_id'] ?? null;
    $lecturer_id = $_POST['lecturer_id'] ?? null;

    if (empty($course_code)) $errors['course_code'] = 'Course code is required';
    if (empty($course_name)) $errors['course_name'] = 'Course name is required';

    if (empty($errors)) {
        db_query(
            "UPDATE courses SET course_code = ?, course_name = ?, description = ?, target_year = ?, credit_hours = ?, academic_year = ?, semester = ?, department_id = ?, lecturer_id = ?, updated_at = NOW() WHERE course_id = ?",
            [$course_code, $course_name, $description, $target_year ?: null, $credit_hours ?: null, $academic_year ?: null, $semester, $department_id ?: null, $lecturer_id ?: null, $course_id]
        );
        $success = true;
        $stmt = db_query("SELECT * FROM courses WHERE course_id = ?", [$course_id]);
        $course = $stmt->fetch();
    }
}
?>

<div class="d-flex">
    <div class="main-content w-100" id="mainContent">
        <?php include_once('../../includes/navbar.php') ?>

        <h3>Edit Course</h3>

        <div class="card col-8 m-auto mt-4">
            <?php if ($success): ?>
                <div class="alert alert-success">Course updated successfully.</div>
            <?php endif; ?>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Course Code *</label>
                        <input type="text" name="course_code" class="form-control <?= isset($errors['course_code']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['course_code'] ?? $course['course_code']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course Name *</label>
                        <input type="text" name="course_name" class="form-control <?= isset($errors['course_name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['course_name'] ?? $course['course_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($_POST['description'] ?? $course['description']) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Target Year</label>
                            <select name="target_year" class="form-select">
                                <option value="">-- Select Year --</option>
                                <?php for ($y = 1; $y <= 5; $y++): ?>
                                    <option value="<?= $y ?>" <?= ((isset($_POST['target_year']) && $_POST['target_year'] == $y) || (!isset($_POST['target_year']) && $course['target_year'] == $y)) ? 'selected' : '' ?>>Year <?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Credit Hours</label>
                            <input type="number" name="credit_hours" class="form-control" value="<?= htmlspecialchars($_POST['credit_hours'] ?? $course['credit_hours']) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Academic Year</label>
                            <input type="number" name="academic_year" class="form-control" value="<?= htmlspecialchars($_POST['academic_year'] ?? $course['academic_year']) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Semester</label>
                            <select name="semester" class="form-select">
                                <option value="">-- Select Semester --</option>
                                <option value="Rain" <?= ((isset($_POST['semester']) && $_POST['semester'] == 'Rain') || (!isset($_POST['semester']) && $course['semester'] == 'Rain')) ? 'selected' : '' ?>>Rain</option>
                                <option value="Hammartan" <?= ((isset($_POST['semester']) && $_POST['semester'] == 'Hammartan') || (!isset($_POST['semester']) && $course['semester'] == 'Hammartan')) ? 'selected' : '' ?>>Hammartan</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">-- Any --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['dept_id'] ?>" <?= ((isset($_POST['department_id']) && $_POST['department_id'] == $d['dept_id']) || (!isset($_POST['department_id']) && $course['department_id'] == $d['dept_id'])) ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lecturer</label>
                        <select name="lecturer_id" class="form-select">
                            <option value="">-- None --</option>
                            <?php foreach ($lecturers as $l): ?>
                                <option value="<?= $l['user_id'] ?>" <?= ((isset($_POST['lecturer_id']) && $_POST['lecturer_id'] == $l['user_id']) || (!isset($_POST['lecturer_id']) && $course['lecturer_id'] == $l['user_id'])) ? 'selected' : '' ?>><?= htmlspecialchars($l['name']) ?></option>
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