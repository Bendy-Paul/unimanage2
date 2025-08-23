    <?php
    include_once '../includes/sidebar.php';


    require_once '../includes/db_connect.php';



    $stats = [
        'total_students' => db_query("SELECT COUNT(*) as count FROM students")->fetch()['count'],
        // Active events: events that have not yet ended (ongoing or upcoming)
        'active_events' => db_query("SELECT COUNT(*) as count FROM events WHERE end_datetime >= NOW()")->fetch()['count'],
        'total_courses' => db_query("SELECT COUNT(*) as count FROM courses")->fetch()['count'],
        'pending_results' => db_query("SELECT COUNT(*) as count FROM results WHERE status != 'PUBLISHED'")->fetch()['count']
    ];
    // Fetch recent announcements
    $announcements = db_query(
        "SELECT a.*, u.name as author_name 
     FROM announcements a 
     JOIN users u ON a.author_id = u.user_id 
     ORDER BY a.publish_date DESC 
     LIMIT 5"
    );

    // Fetch recent activity logs
    $activities = db_query(
        "SELECT * FROM audit_logs 
     ORDER BY timestamp DESC 
     LIMIT 5"
    );

    // Fetch departments for filter
    $departments = db_query("SELECT * FROM departments");
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard | UniPortal</title>
    </head>

    <body>
        <div class="d-flex">

            <!-- Main Content -->
            <div class="main-content w-100" id="mainContent">
                <!-- Header -->
                <?php include_once('../includes/navbar.php') ?>

                <!-- Content -->
                <div class="container-fluid p-4">
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Total Students</h6>
                                            <h3 class="mb-0"><?= $stats['total_students'] ?></h3>
                                        </div>
                                        <div class="icon revenue">
                                            <i class="bi bi-people"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="card stat-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="text-muted mb-2">Active Events</h6>
                                                <h3 class="mb-0"><?= $stats['active_events'] ?></h3>
                                            </div>
                                            <div class="icon users">
                                                <i class="bi bi-calendar-event"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>

                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Courses Offered</h6>
                                            <h3 class="mb-0"><?= $stats['total_courses'] ?></h3>
                                        </div>
                                        <div class="icon orders">
                                            <i class="bi bi-journal-bookmark"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card stat-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Pending Results</h6>
                                            <h3 class="mb-0"><?= $stats['pending_results'] ?></h3>
                                        </div>
                                        <div class="icon conversion">
                                            <i class="bi bi-journal-check"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Announcements and Activity -->
                    <div class="row mb-4">
                        <div class="col-lg-12 mb-4 mb-lg-0">
                            <div class="chart-container">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Recent Announcements</h5>
                                    <a href="announcements.php" class="btn btn-sm btn-primary">View All</a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Content</th>
                                                <th>Author</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($announcements)): ?>
                                                <?php foreach ($announcements as $annc): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($annc['title']) ?></td>
                                                        <td><?= htmlspecialchars(substr($annc['content'], 0, 50)) ?>...</td>
                                                        <td><?= htmlspecialchars($annc['author_name']) ?></td>
                                                        <td><?= date('M j, Y', strtotime($annc['publish_date'])) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No announcements found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Events and Quick Actions -->
                    <div class="row">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="card-title mb-0">Upcoming Events</h5>
                                        <a href="manage_events/index.php" class="btn btn-sm btn-outline-primary">Manage Events</a>
                                    </div>
                                    <div class="list-group">
                                        <?php
                                        $events_stmt = db_query("SELECT title, start_datetime, event_id FROM events WHERE end_datetime >= NOW() ORDER BY start_datetime ASC LIMIT 5");
                                        $upcoming = $events_stmt->fetchAll();
                                        ?>
                                        <?php if (!empty($upcoming)): ?>
                                            <?php foreach ($upcoming as $ev): ?>
                                                <a href="../student/event_details.php?event_id=<?= $ev['event_id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-calendar-event me-2"></i>
                                                        <?= htmlspecialchars($ev['title']) ?>
                                                    </div>
                                                    <small class="text-muted"><?= date('M j, Y H:i', strtotime($ev['start_datetime'])) ?></small>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="list-group-item text-muted">No upcoming events</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Quick Actions</h5>
                                    <div class="row g-2">
                                        <div class="col-md-12">
                                            <a href="manage_users/new_user.php" class="btn btn-primary w-100 mb-2">
                                                <i class="bi bi-person-plus me-2"></i> Add Student/User
                                            </a>
                                        </div>
                                        <!-- <div class="col-md-6"> -->
                                            <!-- <a href="manage_users/new_user.php" class="btn btn-success w-100 mb-2"> -->
                                                <!-- <i class="bi bi-person-plus me-2"></i> Add Faculty -->
                                            <!-- </a> -->
                                        <!-- </div> -->
                                        <div class="col-md-6">
                                            <a href="manage_courses/new_course.php" class="btn btn-info w-100 mb-2">
                                                <i class="bi bi-journal-plus me-2"></i> Add Course
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="manage_announcements/new_announcement.php" class="btn btn-warning w-100 mb-2">
                                                <i class="bi bi-megaphone me-2"></i> Post Announcement
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </body>

    </html>