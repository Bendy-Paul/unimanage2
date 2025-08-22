<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

$errors = [];
$success = false;

$departments = db_query("SELECT * FROM departments ORDER BY name")->fetchAll();
$lecturers = db_query("SELECT user_id, name FROM users WHERE role = 'faculty' ORDER BY name")->fetchAll();


    // validate against current academic year
    $ayRow = db_query("SELECT academic_year FROM academic_year WHERE id = 1 LIMIT 1")->fetch();
    $currentAcademicYear = $ayRow['academic_year'] ?? (int)date('Y');
    
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_code = trim($_POST['course_code'] ?? '');
    $course_name = trim($_POST['course_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $target_year = $_POST['target_year'] ?? null;
    $credit_hours = $_POST['credit_hours'] ?? null;
    $academic_year = $_POST['academic_year'] ?? null;
    if ($academic_year !== null && $academic_year !== '') {
        $selectedAy = intval($academic_year);
        if ($selectedAy > $currentAcademicYear) {
            $errors['academic_year'] = 'Academic year cannot be greater than current academic year.';
        }
    }
    $semester = trim($_POST['semester'] ?? '');
    $department_id = $_POST['department_id'] ?? null;
    $lecturer_id = $_POST['lecturer_id'] ?? null;

    if (empty($course_code)) $errors['course_code'] = 'Course code is required';
    if (empty($course_name)) $errors['course_name'] = 'Course name is required';

    if (empty($errors)) {
        db_query(
            "INSERT INTO courses (course_code, course_name, description, target_year, credit_hours, academic_year, semester, department_id, lecturer_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [$course_code, $course_name, $description, $target_year ?: null, $credit_hours ?: null, $academic_year ?: null, $semester, $department_id ?: null, $lecturer_id ?: null]
        );
        $success = true;
        // clear form
        $course_code = $course_name = $description = $target_year = $credit_hours = $academic_year = $semester = $department_id = $lecturer_id = null;
    }
}
?>

<div class="d-flex">
    <div class="main-content w-100" id="mainContent">
        <?php include_once('../../includes/navbar.php') ?>

        <h3>New Course</h3>

        <div class="card col-8 m-auto mt-4">
            <?php if ($success): ?>
                <div class="alert alert-success">Course created successfully.</div>
            <?php endif; ?>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Course Code *</label>
                        <input type="text" name="course_code" class="form-control <?= isset($errors['course_code']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($course_code ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course Name *</label>
                        <input type="text" name="course_name" class="form-control <?= isset($errors['course_name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($course_name ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($description ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Target Year</label>
                            <select name="target_year" class="form-select">
                                <option value="1">-- Select Year --</option>
                                <?php for ($y = 1; $y <= 5; $y++): ?>
                                    <option value="<?= $y ?>" <?= (isset($target_year) && $target_year == $y) ? 'selected' : '' ?>>Year <?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Credit Hours</label>
                            <input type="number" name="credit_hours" class="form-control" value="<?= htmlspecialchars($credit_hours ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year" class="form-select <?= isset($errors['academic_year']) ? 'is-invalid' : '' ?>">
                                <option value="<?php echo $currentAcademicYear?>">-- Select Year --</option>
                                <?php
                                $start = (int)($currentAcademicYear);
                                for ($i = 0; $i <= 10; $i++) {
                                    $y = $start - $i;
                                    $label = $y . '/' . ($y + 1);
                                    $sel = (isset($academic_year) && $academic_year == $y) ? ' selected' : '';
                                    echo '<option value="' . $y . '"' . $sel . '>' . htmlspecialchars($label) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Semester</label>
                            <select name="semester" class="form-select">
                                <option value="">-- Select Semester --</option>
                                <option value="Rain" <?= (isset($semester) && $semester == 'Rain') ? 'selected' : '' ?>>Rain</option>
                                <option value="Hammartan" <?= (isset($semester) && $semester == 'Hammartan') ? 'selected' : '' ?>>Hammartan</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="hidden" name="department_id" value="sOFTWARE ENGINEERING">
                        <!-- <label class="form-label">Department</label> -->
                        <!-- <select name="department_id" class="form-select">
                            <option value="">-- Any --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['dept_id'] ?>" <?= (isset($department_id) && $department_id == $d['dept_id']) ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select> -->
                    </div>
                    <div class="mb-3">
                        <input type="hidden" name="lecturer_id" value="1">
                        <!-- <label class="form-label">Lecturer</label>
                        <select name="lecturer_id" class="form-select">
                            <option value="">-- None --</option>
                            <?php foreach ($lecturers as $l): ?>
                                <option value="<?= $l['user_id'] ?>" <?= (isset($lecturer_id) && $lecturer_id == $l['user_id']) ? 'selected' : '' ?>><?= htmlspecialchars($l['name']) ?></option>
                            <?php endforeach; ?>
                        </select> -->
                    </div>
                    <button class="btn btn-primary">Create Course</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>