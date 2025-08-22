<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniPortal - Student Dashboard</title>
    <!-- custom CSS -->
    <link rel="stylesheet" href="../assets/css/student.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->

</head>

<body>
    <!-- Navigation Bar with Custom Dropdowns -->
    <?php
    include_once '../includes/header.php';
    ?>

    <!-- Main Content -->
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-10 mx-auto">

                <!-- Dashboard Header -->
                <?php
                // Ensure DB connection is available
                include_once '../includes/db_connect.php';

                // Get current user from session
                $user = $_SESSION['user'] ?? null;
                $userName = $user['name'] ?? 'Student';
                $userRole = $user['role'] ?? '';
                $deptId = $user['dept_id'] ?? $user['department_id'] ?? null;
                if ($user) {
                    $userId = $user['id'] ?? $user['user_id'] ?? null;
                    if ($userId) {
                        $stmt = db_query('SELECT u.*, s.* FROM users u LEFT JOIN students s ON u.user_id = s.user_id WHERE u.user_id = ?', [$userId]);
                        $student = $stmt->fetch();
                        // fetch department name
                        if (!empty($student['department_id'])) {
                            $d = db_query('SELECT name FROM departments WHERE dept_id = ?', [$student['department_id']])->fetch();
                            $student['department_name'] = $d['name'] ?? null;
                        }
                    }
                }

                // Prepare counts and lists
                // Announcements for user's department or global (dept_id IS NULL)
                $announcements = [];
                if ($deptId) {
                    $stmt = db_query("SELECT * FROM announcements WHERE dept_id = ? OR dept_id IS NULL ORDER BY publish_date DESC LIMIT 3", [$deptId]);
                } else {
                    $stmt = db_query("SELECT * FROM announcements ORDER BY publish_date DESC LIMIT 3");
                }
                $announcements = $stmt->fetchAll();
                // print_r($announcements);

                // Upcoming events for dept or global
                $stmt = db_query("SELECT * FROM events WHERE end_datetime >= NOW() ORDER BY start_datetime ASC LIMIT 5");
                $events = $stmt->fetchAll();

                // Results count for student (if student)
                $newResultsCount = 0;
                if ($user && ($user['role'] ?? '') === 'student') {
                    $studentId = $user['id'] ?? $user['user_id'] ?? null;
                    if ($studentId) {
                        $stmt = db_query("SELECT COUNT(*) AS cnt FROM results WHERE student_id = ?", [$studentId]);
                        $row = $stmt->fetch();
                        $newResultsCount = $row['cnt'] ?? 0;
                    }
                }

                // Today's timetable for student's year/department (basic)
                $todaySlots = [];
                if ($user && ($user['role'] ?? '') === 'student') {
                    // Try to get student year from students table
                    $studentYear = null;
                    $studentId = $user['id'] ?? $user['user_id'] ?? null;
                    if ($studentId) {
                        $stmt = db_query('SELECT year FROM students WHERE user_id = ?', [$studentId]);
                        $s = $stmt->fetch();
                        $studentYear = $s['year'] ?? null;
                    }

                    // Fetch timetable slots valid today for user's department and year
                    $params = [$deptId];
                    $sql = "SELECT ts.*, c.course_name FROM timetable_slots ts LEFT JOIN courses c ON ts.course_id = c.course_id WHERE ts.department_id = ?";
                    // Note: if timetable_slots doesn't have department_id in schema, fallback to course.department_id via join
                    // Try query assuming timetable_slots has department_id
                    try {
                        $stmt = db_query($sql, $params);
                        $todaySlots = $stmt->fetchAll();
                    } catch (Exception $e) {
                        // fallback: join courses.department_id
                        $sql2 = "SELECT ts.*, c.course_name FROM timetable_slots ts LEFT JOIN courses c ON ts.course_id = c.course_id WHERE c.department_id = ?";
                        $stmt = db_query($sql2, $params);
                        $todaySlots = $stmt->fetchAll();
                    }
                }
                ?>
                <div class="dashboard-header animate-fadein">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="welcome-text">Welcome back, <?php echo htmlspecialchars($userName); ?>!</h1>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <span class="badge bg-light text-dark px-3 py-2 mb-2">
                                <i class="bi bi-bookmark-star-fill me-2"></i>Set <?php echo htmlspecialchars($student['year'] ?? ''); ?>
                            </span>
                            <span class="badge bg-light text-dark px-3 py-2 mb-2 ms-2">
                                <i class="bi bi-calendar-check me-2"></i><?php echo htmlspecialchars($student['department_name'] ?? '')?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="row mb-4 animate-fadein" style="animation-delay: 0.2s">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-journal-check"></i>
                            </div>
                            <h3 class="stats-number"><?php echo (int)$newResultsCount; ?></h3>
                            <p class="stats-label">New Results</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <?php
                            // Count upcoming events
                            $eventsCount = count($events);
                            ?>
                            <h3 class="stats-number"><?php echo $eventsCount; ?></h3>
                            <p class="stats-label">Upcoming Events</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-megaphone"></i>
                            </div>
                            <?php
                            $annCount = count($announcements);
                            ?>
                            <h3 class="stats-number"><?php echo $annCount; ?></h3>
                            <p class="stats-label">Announcements</p>
                        </div>
                    </div>
                </div>

                <!-- Main Content Row -->
                <div class="row animate-fadein" style="animation-delay: 0.4s">
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        <!-- Announcements Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0 d-flex align-items-center">
                                    <i class="bi bi-megaphone-fill text-primary me-2"></i>
                                    Latest Announcements
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($announcements as $ann): ?>
                                        <a href="#" class="list-group-item list-group-item-action announcement-card py-3 px-4">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1 fw-bold">
                                                    <?php echo htmlspecialchars($ann['title']); ?>
                                                <?php if (strtotime($ann['publish_date']) > strtotime('-1 day')): ?>
                                                    <span class="badge bg-primary rounded-pill">New</span>
                                                <?php endif; ?>
                                                </h6>
                                                <span class="badge status-badge bg-<?=$ann['priority'] == 'low' ? 'success' : ($ann['priority'] == 'medium' ? 'warning' : 'danger')?>">
                                                    <?= ucfirst($ann['priority']) ?> Priority
                                                </span>
                                            </div>
                                            <p class="mb-1"><?php echo htmlspecialchars(substr($ann['content'], 0, 200)); ?></p>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i> Posted <?php echo htmlspecialchars($ann['publish_date']); ?></small>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 py-3">
                                <a href="#" class="btn btn-outline-primary btn-sm">View All Announcements</a>
                            </div>
                        </div>

                        <!-- Timetable removed per request -->
                    </div>

                    <!-- Right Column -->
                    <div class="col-lg-4">
                        <!-- Upcoming Events Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0 d-flex align-items-center">
                                    <i class="bi bi-calendar2-event-fill text-success me-2"></i>
                                    Upcoming Events
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($events as $event): ?>
                                        <a href="#" class="list-group-item list-group-item-action py-3 px-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded me-3">
                                                    <i class="bi bi-briefcase-fill fs-4"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($event['title']) ?></h6>
                                                    <p class="mb-1 small text-muted"><?php
                                                                                        // Assuming $event['start_datetime'] contains the datetime from your database
                                                                                        $startDate = new DateTime($event['start_datetime']);
                                                                                        $formattedDate = $startDate->format('M j');  // "Oct 25"
                                                                                        $formattedTime = $startDate->format('g:i A'); // "10:00 AM"

                                                                                        // Combine them with the bullet separator
                                                                                        $displayDateTime = $formattedDate . ' • ' . $formattedTime; // "Oct 25 • 10:00 AM"
                                                                                        ?> <?php echo htmlspecialchars($displayDateTime)  ?> • <?php echo htmlspecialchars($event['venue']) ?></p>
                                                    <!-- <span class="badge bg-primary bg-opacity-10 text-primary small">RSVP Open</span> -->
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>

                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 py-3">
                                <a href="events.php" class="btn btn-outline-success btn-sm">View All Events</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="text-white mb-4"><i class="bi bi-mortarboard me-2"></i>UniPortal</h5>
                    <p class="mb-0">A comprehensive university management system designed for students, faculty, and administrators.</p>
                </div>
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="text-white mb-4">Quick Links</h5>
                    <div class="row">
                        <div class="col-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="#" class="text-decoration-none text-light">Dashboard</a></li>
                                <li class="mb-2"><a href="#" class="text-decoration-none text-light">Courses</a></li>
                                <li class="mb-2"><a href="#" class="text-decoration-none text-light">Results</a></li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="#" class="text-decoration-none text-light">Timetable</a></li>
                                <li class="mb-2"><a href="#" class="text-decoration-none text-light">Events</a></li>
                                <li class="mb-2"><a href="#" class="text-decoration-none text-light">Profile</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h5 class="text-white mb-4">Contact Us</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i> support@uniportal.edu</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i> (123) 456-7890</li>
                        <li class="mb-2"><i class="bi bi-building me-2"></i> University Campus, Department of Software Engineering</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 bg-light">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-md-0">&copy; 2023 UniPortal. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item"><a href="#" class="text-light"><i class="bi bi-facebook"></i></a></li>
                        <li class="list-inline-item ms-3"><a href="#" class="text-light"><i class="bi bi-twitter"></i></a></li>
                        <li class="list-inline-item ms-3"><a href="#" class="text-light"><i class="bi bi-instagram"></i></a></li>
                        <li class="list-inline-item ms-3"><a href="#" class="text-light"><i class="bi bi-linkedin"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Simple animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.animate-fadein');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });

            animatedElements.forEach(el => {
                el.style.opacity = 0;
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>

</html>