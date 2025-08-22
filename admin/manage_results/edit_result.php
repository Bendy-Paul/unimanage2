<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

$result_id = $_GET['id'] ?? null;
if (!$result_id) {
    header('Location: index.php');
    exit();
}

$stmt = db_query("SELECT * FROM results WHERE result_id = ?", [$result_id]);
$result = $stmt->fetch();
if (!$result) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = false;
$departments = db_query("SELECT * FROM departments")->fetchAll();
$courses = db_query("SELECT course_id, course_code, course_name, credit_hours, semester FROM courses ORDER BY course_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_code = trim($_POST['course_code'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    if (!empty($_POST['course_select'])) {
        $cstmt = db_query("SELECT course_code, semester, credit_hours FROM courses WHERE course_id = ?", [$_POST['course_select']]);
        if ($crow = $cstmt->fetch()) {
            $course_code = $crow['course_code'];
            $semester = $crow['semester'];
            $credits = $crow['credit_hours'];
        }
    }
    $academic_year = $_POST['academic_year'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $credits = $_POST['credits'] ?? null;
    $department_id = $_POST['department_id'] ?? null;
    $marks = $_POST['marks'] ?? null;
    $grade = trim($_POST['grade'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $remarks = trim($_POST['remarks'] ?? '');

    if (empty($course_code)) $errors['course_code'] = 'Course code is required';
    if (empty($student_id)) $errors['student_id'] = 'Student is required';

    // Allow entering "Name - ID" in the student input; extract trailing ID if necessary
    if (!empty($student_id) && !ctype_digit($student_id)) {
        if (preg_match('/(\d+)\s*$/', $student_id, $m)) {
            $student_id = $m[1];
        }
    }
    if ($marks === '' || $marks === null) $errors['marks'] = 'Marks are required';

    if (empty($errors)) {
        // Validate selected student exists
        $sstmt = db_query("SELECT user_id FROM users WHERE user_id = ? AND role = 'student'", [$student_id]);
        if (!$sstmt->fetch()) {
            $errors['student_id'] = 'Invalid student selected';
        } else {
            db_query(
                "UPDATE results SET course_code = ?, student_id = ?, academic_year = ?, semester = ?, credits = ?, department_id = ?, marks = ?, grade = ?, status = ?, published_by = ?, published_at = ?, remarks = ?, updated_at = NOW() WHERE result_id = ?",
                [$course_code, $student_id, $academic_year, $semester, $credits, $department_id, $marks, $grade, $status, $_SESSION['user']['id'] ?? null, ($status === 'published' ? date('Y-m-d H:i:s') : null), $remarks, $result_id]
            );
            $success = true;
            $stmt = db_query("SELECT * FROM results WHERE result_id = ?", [$result_id]);
            $result = $stmt->fetch();
        }
    }
}
?>

<div class="d-flex">
    <div class="main-content w-100" id="mainContent">

        <?php include_once('../../includes/navbar.php') ?>

        <h3>Edit Result</h3>

        <div class="card m-auto col-8 mt-4">
            <div class="card-body">

                <?php if ($success): ?>
                    <div class="alert alert-success">Result updated.</div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Course Code *</label>
                        <input type="text" id="course_code" name="course_code" readonly class="form-control <?= isset($errors['course_code']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['course_code'] ?? $result['course_code']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Student *</label>
                        <?php
                        // Prefill with stored student id resolved to name if available
                        $prefill_student = '';
                        if (isset($_POST['student_id'])) {
                            $prefill_student = $_POST['student_id'];
                        } else {
                            // try to get student name for $result['student_id']
                            $sstmt = db_query("SELECT name FROM users WHERE user_id = ?", [$result['student_id']]);
                            $sdata = $sstmt->fetch();
                            if ($sdata) $prefill_student = $sdata['name'] . ' - ' . $result['student_id'];
                        }
                        ?>
                        <input type="text" id="student_search" class="form-control <?= isset($errors['student_id']) ? 'is-invalid' : '' ?>" placeholder="Type student name or ID" autocomplete="off" value="<?= htmlspecialchars($prefill_student) ?>" required>
                        <input type="hidden" name="student_id" id="student_id" value="<?= htmlspecialchars($_POST['student_id'] ?? $result['student_id']) ?>">
                        <div id="student_suggestions" class="list-group"></div>
                        <div class="form-text">Start typing to search students and select one from the list.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course (choose to auto-fill)</label>
                        <select id="course_select" name="course_select" class="form-select">
                            <option value="">-- Select Course --</option>
                            <?php foreach ($courses as $c): ?>
                                <option data-code="<?= htmlspecialchars($c['course_code']) ?>" data-semester="<?= htmlspecialchars($c['semester']) ?>" data-credits="<?= htmlspecialchars($c['credit_hours']) ?>" value="<?= $c['course_id'] ?>" <?= ((isset($_POST['course_select']) && $_POST['course_select'] == $c['course_id']) || (!isset($_POST['course_select']) && $result['course_code'] == $c['course_code'])) ? 'selected' : '' ?>><?= htmlspecialchars($c['course_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Academic Year</label>
                            <input type="number" name="academic_year" class="form-control" value="<?= htmlspecialchars($_POST['academic_year'] ?? $result['academic_year']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Semester</label>
                            <input type="text" id="course_semester" name="semester" readonly class="form-control" value="<?= htmlspecialchars($_POST['semester'] ?? $result['semester']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Credits</label>
                            <input type="number" id="course_credits" name="credits" readonly class="form-control" value="<?= htmlspecialchars($_POST['credits'] ?? $result['credits']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Academic Year</label>
                            <input type="number" name="academic_year" class="form-control" value="<?= htmlspecialchars($_POST['academic_year'] ?? $result['academic_year']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Semester</label>
                            <input type="text" name="semester" class="form-control" value="<?= htmlspecialchars($_POST['semester'] ?? $result['semester']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Credits</label>
                            <input type="number" name="credits" class="form-control" value="<?= htmlspecialchars($_POST['credits'] ?? $result['credits']) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Marks *</label>
                        <input type="number" step="0.01" name="marks" class="form-control <?= isset($errors['marks']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['marks'] ?? $result['marks']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Grade</label>
                        <input type="text" name="grade" class="form-control" value="<?= htmlspecialchars($_POST['grade'] ?? $result['grade']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= (($_POST['status'] ?? $result['status']) == 'draft') ? 'selected' : '' ?>>draft</option>
                            <option value="published" <?= (($_POST['status'] ?? $result['status']) == 'published') ? 'selected' : '' ?>>published</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">-- Any --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['dept_id'] ?>" <?= ((isset($_POST['department_id']) && $_POST['department_id'] == $d['dept_id']) || (!isset($_POST['department_id']) && $result['department_id'] == $d['dept_id'])) ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control"><?= htmlspecialchars($_POST['remarks'] ?? $result['remarks']) ?></textarea>
                    </div>
                    <button class="btn btn-primary">Save Changes</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const studentSearch = document.getElementById('student_search');
        const suggestions = document.getElementById('student_suggestions');
        const studentIdInput = document.getElementById('student_id');
        let timer;

        studentSearch.addEventListener('input', function() {
            const q = this.value.trim();
            studentIdInput.value = ''; // reset until selection
            if (timer) clearTimeout(timer);
            if (q.length < 2) {
                suggestions.innerHTML = '';
                return;
            }
            timer = setTimeout(function() {
                fetch('../api/search_students.php?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        suggestions.innerHTML = '';
                        data.forEach(s => {
                            const item = document.createElement('button');
                            item.type = 'button';
                            item.className = 'list-group-item list-group-item-action';
                            item.textContent = s.name + ' - ' + s.user_id;
                            item.dataset.id = s.user_id;
                            item.addEventListener('click', function() {
                                studentSearch.value = this.textContent;
                                studentIdInput.value = this.dataset.id;
                                suggestions.innerHTML = '';
                            });
                            suggestions.appendChild(item);
                        });
                    }).catch(() => {
                        suggestions.innerHTML = '';
                    });
            }, 250);
        });

        // Course select autofill
        const courseSelect = document.getElementById('course_select');
        const courseCode = document.getElementById('course_code');
        const courseSemester = document.getElementById('course_semester');
        const courseCredits = document.getElementById('course_credits');
        if (courseSelect) {
            courseSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if (!opt || !opt.dataset) return;
                courseCode.value = opt.dataset.code || '';
                courseSemester.value = opt.dataset.semester || '';
                courseCredits.value = opt.dataset.credits || '';
            });
        }
    });
</script>