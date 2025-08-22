<?php
// Results page - clean and organized
include_once '../includes/header.php';
include_once '../includes/db_connect.php';

$user = $_SESSION['user'] ?? null;
$results = [];
$summary = [
    'gpa' => null,
    'credits' => 0,
    'current_semester' => null,
];

// Grade point mapping based on common grading systems
$gradePoints = [
    // 5.0 scale mapping (adjust to your institution policy if needed)
    'A' => 5.0,
    'B+' => 4.0,
    'B' => 3.5,
    'C+' => 3.0,
    'C' => 2.5,
    'D' => 1.0,
    'F' => 0.0,
];

if ($user && isset($user['id'])) {
    $studentId = $user['id'];
    echo $user['id'] . "\n";

    // Get all results for the student with course details
    $stmt = db_query(
        'SELECT 
        r.course_code AS original_course_code,
        r.*,
        c.course_name,
        c.course_code AS official_course_code
    FROM results r 
    LEFT JOIN courses c ON r.course_code = c.course_code 
    WHERE r.student_id = ? 
    ORDER BY r.academic_year DESC, r.semester DESC',
        [$studentId]
    );

    $results = $stmt->fetchAll();

    // Calculate summary information
    $totalCredits = 0;
    $gpSum = 0;
    $gpCount = 0;

    foreach ($results as $r) {
        $credits = (int)($r['credits'] ?? 0);
        $totalCredits += $credits;

        // Calculate CGPA on a 5.0 scale.
        // Priority: use explicit grade_point (if your results table has it),
        // then letter grade mapping, then numeric marks -> grade-point mapping.
        if (isset($r['grade_point']) && $r['grade_point'] !== null && $r['grade_point'] !== '') {
            $gp = (float)$r['grade_point'];
            $gpSum += $gp * $credits;
            $gpCount += $credits;
        } elseif (!empty($r['grade']) && isset($gradePoints[$r['grade']])) {
            $gp = (float)$gradePoints[$r['grade']];
            $gpSum += $gp * $credits;
            $gpCount += $credits;
        } elseif (isset($r['marks']) && is_numeric($r['marks'])) {
            // Map numeric marks to 5.0 grade points (customize thresholds as needed)
            $marks = (float)$r['marks'];
            if ($marks >= 70) $gp = 5.0;
            elseif ($marks >= 60) $gp = 4.0;
            elseif ($marks >= 50) $gp = 3.0;
            elseif ($marks >= 45) $gp = 2.0;
            elseif ($marks >= 40) $gp = 1.0;
            else $gp = 0.0;

            $gpSum += $gp * $credits;
            $gpCount += $credits;
        }
    }

    $summary['credits'] = $totalCredits;
    $summary['gpa'] = $gpCount ? round($gpSum / $gpCount, 2) : null;

    if (!empty($results)) {
        $stmt = db_query("SELECT * FROM academic_year WHERE id = 1")->fetchAll();
        $getsemester = $stmt[0]['semester'];
        if ($getsemester == 1) {
            $summary['current_semester'] = "Hammatarn";
        } else {
            $summary['current_semester'] = "Rain";
        }
        // echo $academicYear;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Results | UniPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/student.css">
    <style>
        .gpa-excellent {
            color: #28a745;
        }

        .gpa-good {
            color: #17a2b8;
        }

        .gpa-fair {
            color: #ffc107;
        }

        .gpa-poor {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0"><i class="bi bi-journal-check me-2"></i>My Academic Results</h1>
                    <div>
                        <select id="semester-filter" class="form-select form-select-sm w-auto">
                            <option value="all">All semesters</option>
                            <?php
                            // Generate semester filter options from available results
                            $semesters = [];
                            foreach ($results as $r) {
                                if (!empty($r['semester']) && !in_array($r['semester'], $semesters)) {
                                    $semesters[] = $r['semester'];
                                    echo '<option value="' . htmlspecialchars($r['semester']) . '">' . htmlspecialchars($r['semester']) . '</option>';
                                }
                            }
                            ?>
                        </select>

                        <select id="year-filter" class="form-select form-select-sm w-auto mt-4">
                            <option value="all">All Years</option>
                            <?php
                            // Generate academic year filter options from available results
                            $academic_years = [];
                            foreach ($results as $r) {
                                if (!empty($r['academic_year']) && !in_array($r['academic_year'], $academic_years)) {
                                    $academic_years[] = $r['academic_year'];
                                    // Display academic year in format YYYY/YYYY+1
                                    echo '<option value="' . htmlspecialchars($r['academic_year']) . '">' .
                                        htmlspecialchars($r['academic_year']) . '/' .
                                        htmlspecialchars($r['academic_year'] + 1) .
                                        '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="me-3 text-primary"><i class="bi bi-award fs-2"></i></div>
                                <div>
                                    <div class="small text-muted">Current GPA</div>
                                    <div class="h5 mb-0 <?php
                                                        if ($summary['gpa'] >= 3.5) echo 'gpa-excellent';
                                                        elseif ($summary['gpa'] >= 2.5) echo 'gpa-good';
                                                        elseif ($summary['gpa'] >= 1.5) echo 'gpa-fair';
                                                        else echo 'gpa-poor';
                                                        ?>">
                                        <?php echo $summary['gpa'] !== null ? number_format($summary['gpa'], 2) : '&mdash;'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="me-3 text-success"><i class="bi bi-check-circle fs-2"></i></div>
                                <div>
                                    <div class="small text-muted">Completed Credits</div>
                                    <div class="h5 mb-0"><?php echo number_format($summary['credits']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body d-flex align-items-center">
                                <div class="me-3 text-info"><i class="bi bi-book fs-2"></i></div>
                                <div>
                                    <div class="small text-muted">Current Semester</div>
                                    <div class="h5 mb-0"><?php echo htmlspecialchars($summary['current_semester'] ?? '&mdash;'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Course Results</strong>
                        <small class="text-muted"><?php echo count($results); ?> records found</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Course</th>
                                        <th>Session</th>
                                        <th>Semester</th>
                                        <th>Credits</th>
                                        <th>Marks</th>
                                        <th>Grade</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($results)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">No results published yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($results as $r): ?>
                                            <tr data-year="<?php echo htmlspecialchars($r['academic_year'] ?? ''); ?>"
                                                data-semester="<?php echo htmlspecialchars($r['semester'] ?? ''); ?>">
                                                <td><?php echo htmlspecialchars($r['course_code'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($r['course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($r['academic_year'] ?? ''); ?>/<?php echo htmlspecialchars($r['academic_year'] + 1 ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($r['semester'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($r['credits'] ?? ''); ?></td>
                                                <td><?php echo is_numeric($r['marks']) ? number_format($r['marks'], 1) : htmlspecialchars($r['marks'] ?? ''); ?></td>
                                                <td>
                                                    <?php if (!empty($r['grade'])): ?>
                                                        <span class="badge <?php
                                                                            echo $r['grade'] === 'A' ? 'bg-success' : ($r['grade'] === 'F' ? 'bg-danger' : 'bg-primary');
                                                                            ?>">
                                                            <?php echo htmlspecialchars($r['grade']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">Published</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer"></footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Semester filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const semesterFilter = document.getElementById('semester-filter');
            const resultRows = document.querySelectorAll('tbody tr[data-semester]');

            // Add year filter select element
            yearFilter = document.getElementById('year-filter');




            // Combined filter function
            function filterResults() {
                const selectedSemester = semesterFilter.value;
                const selectedYear = yearFilter.value;

                resultRows.forEach(row => {
                    const showBySemester = selectedSemester === 'all' || row.getAttribute('data-semester') === selectedSemester;
                    const showByYear = selectedYear === 'all' || row.getAttribute('data-year') === selectedYear;
                    row.style.display = (showBySemester && showByYear) ? '' : 'none';
                });
            }

            // Add event listeners for both filters
            semesterFilter.addEventListener('change', filterResults);
            yearFilter.addEventListener('change', filterResults);

            // Initialize to show all results
            filterResults();
        });
    </script>
</body>

</html>