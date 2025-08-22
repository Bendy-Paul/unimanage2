<?php
include_once '../includes/header.php';
include_once '../includes/db_connect.php';

$user = $_SESSION['user'] ?? null;
$slots = [];
$courses = [];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

if ($user) {
    // Get student's department ID
    $deptId = $user['department_id'] ?? null;
    $studentId =$user['id'] ?? $user['user_id'] ?? null;     
    

    if ($studentId) {  

        // Determine student year (default to 3)
        if (isset($user['year'])) {
            $stmt = db_query("SELECT * FROM academic_year WHERE id = 1 LIMIT 1")->fetchAll();
            $academicYear = $stmt[0]['academic_year'] + 1;
            $studentYear = 2025 - $user['year'];
        }
        // $studentYear = isset($user['year']) ? (int)$user['year'] : 3;

        // Get timetable slots for the department
        $stmt = db_query(
            'SELECT ts.*, c.course_name, c.course_code 
             FROM timetable_slots ts 
             LEFT JOIN courses c ON ts.course_id = c.course_id 
             WHERE c.target_year = ? 
             ORDER BY ts.day_of_week, ts.start_time',
            [$studentYear]
        );
        
        $slots = $stmt->fetchAll();
        
        
        // Get student's enrolled courses for the current semester
        $courses = db_query(
            'SELECT * FROM courses WHERE target_year = ? ORDER BY course_name',
            [$studentYear]
        )->fetchAll();
    }
}


// Organize timetable slots by time and day
$grid = [];
foreach ($slots as $slot) {
    $time = date('g:i a', strtotime($slot['start_time'])) . ' - ' . date('g:i a', strtotime($slot['end_time']));
    $dayIndex = $slot['day_of_week'] - 1; // Convert to 0-based index
    $dayName = $days[$dayIndex] ?? 'Unknown';
    
    $grid[$time][$dayName] = [
        'course_code' => $slot['course_code'] ?? '',
        'course_name' => $slot['course_name'] ?? '',
        'venue' => ($slot['building'] ?? '') . ' ' . ($slot['room_number'] ?? ''),
        'lecturer' => $slot['lecturer_id'] ?? '' // You might want to join with users table to get lecturer name
    ];
}

// Also build a day-centric structure: list of slots per day (ordered by start_time)
$byDay = [];
foreach ($days as $d) $byDay[$d] = [];
foreach ($slots as $slot) {
    $dayIndex = ($slot['day_of_week'] ?? 1) - 1;
    $dayName = $days[$dayIndex] ?? 'Unknown';
    $start = $slot['start_time'] ?? $slot['start'] ?? null;
    $end = $slot['end_time'] ?? $slot['end'] ?? null;
    $byDay[$dayName][] = [
        'start' => $start,
        'end' => $end,
        'time' => $start ? date('g:i a', strtotime($start)) . ($end ? ' - ' . date('g:i a', strtotime($end)) : '') : '',
        'course_code' => $slot['course_code'] ?? '',
        'course_name' => $slot['course_name'] ?? '',
        'venue' => trim(($slot['building'] ?? '') . ' ' . ($slot['room_number'] ?? '')),
        'lecturer' => $slot['lecturer_id'] ?? '',
    ];
}

// Sort each day's slots by start time
foreach ($byDay as $d => &$list) {
    usort($list, function($a, $b){
        $ta = $a['start'] ? strtotime($a['start']) : PHP_INT_MAX;
        $tb = $b['start'] ? strtotime($b['start']) : PHP_INT_MAX;
        return $ta <=> $tb;
    });
}
unset($list);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Timetable | UniPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/student.css">
    <style>
        .timetable-card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .timetable-header {
            background-color: #f8f9fa;
        }
        .timetable-slot {
            min-height: 80px;
            vertical-align: middle;
        }
        .course-badge {
            font-size: 0.8rem;
        }
        .legend-color {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 3px;
        }
        .bg-primary-light { background-color: #e7f1ff; }
        .bg-success-light { background-color: #e6f7ee; }
        .bg-info-light { background-color: #e6f9ff; }
        .bg-warning-light { background-color: #fff8e6; }
        .bg-danger-light { background-color: #ffebee; }
    </style>
</head>
<body>

    <!-- Main Content -->
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="page-title">
                        <i class="bi bi-calendar-week me-2"></i>
                        My Timetable
                    </h1>
                    <div>
                        <select class="form-select form-select-sm w-auto d-inline-block" id="semester-select">
                            <option value="current" selected>Current Semester</option>
                            <?php
                            // Generate semester options dynamically from results
                            $semesters = [];
                            foreach ($courses as $course) {
                                $semesterKey = $course['semester'] . $course['academic_year'];
                                if (!isset($semesters[$semesterKey])) {
                                    $semesters[$semesterKey] = $course['semester'] . ' ' . $course['academic_year'];
                                    echo '<option value="' . htmlspecialchars($semesterKey) . '">' . 
                                         htmlspecialchars($semesters[$semesterKey]) . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    </div>
                </div>

                <!-- Timetable View (per-day schedules) -->
                <div class="mb-4">
                    <div class="row g-3">
                        <?php foreach ($days as $day): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card timetable-card h-100">
                                    <div class="card-header bg-white">
                                        <h5 class="mb-0"><?php echo $day; ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($byDay[$day])): ?>
                                            <div class="text-muted">No scheduled classes.</div>
                                        <?php else: ?>
                                            <ul class="list-group">
                                                <?php foreach ($byDay[$day] as $slot): ?>
                                                    <li class="list-group-item">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($slot['course_code']); ?></strong>
                                                                <div class="small text-muted"><?php echo htmlspecialchars($slot['course_name']); ?></div>
                                                            </div>
                                                            <div class="text-end">
                                                                <div class="fw-bold"><?php echo htmlspecialchars($slot['time']); ?></div>
                                                                <div class="small text-muted"><?php echo htmlspecialchars($slot['venue']); ?></div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Course List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">My Courses This Semester</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if (empty($courses)): ?>
                                <div class="col-12 text-center text-muted py-3">
                                    No courses registered for this semester.
                                </div>
                            <?php else: ?>
                                <?php foreach ($courses as $course): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="course-card p-3 border rounded h-100">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-1">
                                                    <?php echo htmlspecialchars($course['course_code']); ?> - 
                                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                                </h6>
                                                <span class="badge bg-primary course-badge">
                                                    <?php echo (int)$course['credit_hours']; ?> Credits
                                                </span>
                                            </div>
                                            <p class="small mb-1">Lecturer: To be assigned</p>
                                            <div class="d-flex justify-content-between">
                                                <span class="small text-muted">
                                                    <i class="bi bi-calendar-week me-1"></i> 
                                                    <?php echo htmlspecialchars($course['semester']); ?> <?php echo htmlspecialchars($course['academic_year']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer bg-light py-3 mt-4 border-top">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <span class="text-muted">&copy; <?php echo date('Y'); ?> UniPortal. All rights reserved.</span>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-decoration-none me-3">Terms</a>
                    <a href="#" class="text-decoration-none me-3">Privacy</a>
                    <a href="#" class="text-decoration-none">Help</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/student.js"></script>
    <script>
        // Semester filter functionality
        document.getElementById('semester-select').addEventListener('change', function() {
            // In a real implementation, this would fetch timetable data for the selected semester
            console.log('Selected semester:', this.value);
            // You would typically make an AJAX call here to load the appropriate timetable
        });
    </script>
</body>
</html>