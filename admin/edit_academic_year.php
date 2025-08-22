<?php
require_once '../includes/sidebar.php';
require_once '../includes/db_connect.php';

$errors = [];
$success = false;

// Fetch current academic year record (assumes single row with id=1)
$rec = db_query("SELECT * FROM academic_year WHERE id = 1 LIMIT 1")->fetch();
$current = $rec['academic_year'] ?? (int)date('Y');
$currentSemester = isset($rec['semester']) ? intval($rec['semester']) : 1; // 1=Harmattan, 2=Rain

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = intval($_POST['academic_year'] ?? 0);
    $selectedSemester = intval($_POST['semester'] ?? $currentSemester);
    // Only allow editing, not creating or deleting
    if ($selected <= 0) {
        $errors[] = 'Invalid academic year selected.';
    } elseif ($selected < ($current - 50) || $selected > ($current + 10)) {
        // basic sanity limits: allow admin to increase the academic year up to +10
        $errors[] = 'Selected year is out of allowed range.';
    } else {
        // Ensure semester column exists; add it if missing so we can persist current semester
        $col = db_query("SHOW COLUMNS FROM academic_year LIKE 'semester'")->fetch();
        if (!$col) {
            // add tinyint column to store 1 or 2
            db_query("ALTER TABLE academic_year ADD COLUMN semester TINYINT(1) DEFAULT 1");
        }
        db_query("UPDATE academic_year SET academic_year = ?, semester = ? WHERE id = 1", [$selected, $selectedSemester]);
        $success = true;
        $current = $selected;
        $currentSemester = $selectedSemester;
    }
}

?>
<div class="d-flex">
    <div class="main-content w-100" id="mainContent">
        <?php include_once('../includes/navbar.php'); ?>
        <h3>Edit Academic Year</h3>

        <div class="card col-6 m-auto mt-4">
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">Academic year updated.</div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars(implode("<br>", $errors)) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year" class="form-select">
                                <?php
                                // show a range that allows increasing the academic year:
                                // from current +5 down to current -10 (admin can pick a future year)
                                $startFuture = (int)$current + 5;
                                $endPast = (int)$current - 10;
                                for ($y = $startFuture; $y >= $endPast; $y--) {
                                    $label = $y . '/' . ($y + 1);
                                    echo '<option value="' . $y . '"' . ($y == $current ? ' selected' : '') . '>' . htmlspecialchars($label) . '</option>';
                                }
                                ?>
                        </select>
                    </div>
                        <div class="mb-3">
                            <label class="form-label">Current Semester</label>
                            <select name="semester" class="form-select">
                                <option value="1" <?= ($currentSemester === 1) ? 'selected' : '' ?>>1 - Harmattan</option>
                                <option value="2" <?= ($currentSemester === 2) ? 'selected' : '' ?>>2 - Rain</option>
                            </select>
                            <div class="form-text">Change the current academic semester (1 = Harmattan, 2 = Rain).</div>
                        </div>
                    <button class="btn btn-primary">Save</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
