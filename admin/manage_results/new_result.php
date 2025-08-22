<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

$errors = [];
$success = false;

// We'll use AJAX for student lookup instead of loading all students
$departments = db_query("SELECT * FROM departments")->fetchAll();

// Load courses for selection/autofill
$courses = db_query("SELECT course_id, course_code, course_name, credit_hours, semester FROM courses ORDER BY course_name")->fetchAll();

// validate against current academic year
$ayRow = db_query("SELECT academic_year FROM academic_year WHERE id = 1 LIMIT 1")->fetch();
$currentAcademicYear = $ayRow['academic_year'] ?? (int)date('Y');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_code = trim($_POST['course_code'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    // If a course was selected, map its authoritative values
    if (!empty($_POST['course_select'])) {
        $cstmt = db_query("SELECT course_code, semester, credit_hours FROM courses WHERE course_id = ?", [$_POST['course_select']]);
        if ($crow = $cstmt->fetch()) {
            $course_code = $crow['course_code'];
            $semester = $crow['semester'];
            $credits = $crow['credit_hours'];
        }
    }
    $academic_year = $_POST['academic_year'] ?? '';

    if ($academic_year !== null && $academic_year !== '') {
        $selectedAy = intval($academic_year);
        if ($selectedAy > $currentAcademicYear) {
            $errors['academic_year'] = 'Academic year cannot be greater than current academic year.';
        }
    }
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
                "INSERT INTO results (course_code, student_id, academic_year, semester, credits, department_id, marks, grade, status, published_by, published_at, remarks, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [$course_code, $student_id, $academic_year, $semester, $credits, $department_id, $marks, $grade, $status, $_SESSION['user']['id'] ?? null, ($status === 'published' ? date('Y-m-d H:i:s') : null), $remarks]
            );
            $success = true;
        }
    }
}
?>

<div class="d-flex">
    <div class="main-content w-100" id="mainContent">
        <?php include_once('../../includes/navbar.php') ?>
        <h3>Add Result</h3>

        <div class="card m-auto col-8 mt-4">
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">Result saved.</div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Course Code *</label>
                        <input type="text" id="course_code" name="course_code" readonly class="form-control <?= isset($errors['course_code']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['course_code'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Student *</label>
                        <input type="text" id="student_search" class="form-control <?= isset($errors['student_id']) ? 'is-invalid' : '' ?>" placeholder="Type student name or ID" autocomplete="off" required>
                        <input type="hidden" name="student_id" id="student_id" value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
                        <div id="student_suggestions" class="list-group"></div>
                        <div class="form-text">Start typing to search students and select one from the list.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course (choose to auto-fill)</label>
                        <select id="course_select" name="course_select" class="form-select">
                            <option value="">-- Select Course --</option>
                            <?php foreach ($courses as $c): ?>
                                <option data-code="<?= htmlspecialchars($c['course_code']) ?>" data-semester="<?= htmlspecialchars($c['semester']) ?>" data-credits="<?= htmlspecialchars($c['credit_hours']) ?>" value="<?= $c['course_id'] ?>" <?= (isset($_POST['course_select']) && $_POST['course_select'] == $c['course_id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['course_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year" class="form-select <?= isset($errors['academic_year']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Select Year --</option>
                                <?php
                                $start = (int)($currentAcademicYear);
                                $selectedVal = $_POST['academic_year'] ?? '';
                                for ($i = 0; $i <= 10; $i++) {
                                    $y = $start - $i;
                                    $label = $y . '/' . ($y + 1);
                                    $sel = ($selectedVal == $y) ? ' selected' : '';
                                    echo '<option value="' . $y . '"' . $sel . '>' . htmlspecialchars($label) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Semester</label>
                            <input type="text" id="course_semester" name="semester" readonly class="form-control" value="<?= htmlspecialchars($_POST['semester'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Credits</label>
                            <input type="number" id="course_credits" name="credits" readonly class="form-control" value="<?= htmlspecialchars($_POST['credits'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Marks *</label>
                        <input type="number" step="0.01" name="marks" class="form-control <?= isset($errors['marks']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($_POST['marks'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Grade</label>
                        <select name="grade" class="form-control">
                            <option value="">Select Grade</option>
                            <option value="A" <?= (($_POST['grade'] ?? $result['grade'] ?? '') == 'A') ? 'selected' : '' ?>>A</option>
                            <option value="B" <?= (($_POST['grade'] ?? $result['grade'] ?? '') == 'B') ? 'selected' : '' ?>>B</option>
                            <option value="C" <?= (($_POST['grade'] ?? $result['grade'] ?? '') == 'C') ? 'selected' : '' ?>>C</option>
                            <option value="D" <?= (($_POST['grade'] ?? $result['grade'] ?? '') == 'D') ? 'selected' : '' ?>>D</option>
                            <option value="E" <?= (($_POST['grade'] ?? $result['grade'] ?? '') == 'E') ? 'selected' : '' ?>>E</option>
                            <option value="F" <?= (($_POST['grade'] ?? $result['grade'] ?? '') == 'F') ? 'selected' : '' ?>>F</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= (($_POST['status'] ?? '') == 'draft') ? 'selected' : '' ?>>draft</option>
                            <option value="published" <?= (($_POST['status'] ?? '') == 'published') ? 'selected' : '' ?>>published</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="hidden" name="department_id" value="SOFTWARE ENGINEERING">
                        <!-- <label class="form-label">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">-- Any --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['dept_id'] ?>" <?= (isset($_POST['department_id']) && $_POST['department_id'] == $d['dept_id']) ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select> -->
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control"><?= htmlspecialchars($_POST['remarks'] ?? '') ?></textarea>
                    </div>
                    <button class="btn btn-primary">Save Result</button>
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