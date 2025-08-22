<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db_connect.php';

$eventId = $_GET['event_id'] ?? $_GET['id'] ?? null;

// Helper for safe multi-key access
function row_get($row, $keys) {
    foreach ($keys as $k) {
        if (isset($row[$k]) && $row[$k] !== null) return $row[$k];
    }
    return null;
}

if (!$eventId) {
    http_response_code(404);
    echo "<div class='container py-5'><h3>Event not found</h3><p>No event specified.</p></div>";
    exit;
}

// Detect primary key column on events table
$has_event_id = (bool) db_query("SHOW COLUMNS FROM events LIKE 'event_id'")->fetch();
$has_id = (bool) db_query("SHOW COLUMNS FROM events LIKE 'id'")->fetch();

if ($has_event_id) {
    $stmt = db_query("SELECT * FROM events WHERE event_id = ? LIMIT 1", [$eventId]);
} elseif ($has_id) {
    $stmt = db_query("SELECT * FROM events WHERE id = ? LIMIT 1", [$eventId]);
} else {
    // Unusual schema: try a generic lookup (may fail)
    $stmt = db_query("SELECT * FROM events LIMIT 1");
}

$event = $stmt->fetch();
if (!$event) {
    http_response_code(404);
    echo "<div class='container py-5'><h3>Event not found</h3><p>The requested event does not exist.</p></div>";
    exit;
}

// Resolve fields with fallbacks
$title = row_get($event, ['title', 'name']) ?? 'Untitled Event';
$description = row_get($event, ['description', 'body']) ?? '';
$venue = row_get($event, ['venue', 'location', 'place']) ?? 'TBD';
$startRaw = row_get($event, ['start_datetime', 'start_time', 'start', 'date']);
$endRaw = row_get($event, ['end_datetime', 'end_time', 'end']);
$organizerId = row_get($event, ['organizer_id', 'created_by', 'organizer']);
$organizerName = '';
if ($organizerId) {
    // try fetching organizer name (handle id vs user_id)
    $existsUserId = (bool) db_query("SHOW COLUMNS FROM users LIKE 'user_id'")->fetch();
    if ($existsUserId) {
        $u = db_query("SELECT name FROM users WHERE user_id = ? LIMIT 1", [$organizerId])->fetch();
    } else {
        $u = db_query("SELECT name FROM users WHERE id = ? LIMIT 1", [$organizerId])->fetch();
    }
    $organizerName = $u['name'] ?? '';
}

$displayDate = $startRaw ? date('F j, Y', strtotime($startRaw)) : 'TBD';
$displayTime = $startRaw ? date('g:i A', strtotime($startRaw)) . ($endRaw ? ' - ' . date('g:i A', strtotime($endRaw)) : '') : '';
$eventTags = row_get($event, ['tags', 'type']) ?? '';
$image = row_get($event, ['picture', 'image', 'hero']) ?? null;

// Page meta title
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> | UniPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/student.css">
    <style>
        .event-hero { background-size: cover; background-position: center; color: white; padding: 4rem 0; border-radius: 0 0 20px 20px; margin-bottom: 2rem; }
        .event-tag { display:inline-block; background-color: rgba(255,255,255,0.15); padding: .25rem .75rem; border-radius: 20px; }
        .event-details-card { border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <!-- Navigation -->
    <!-- <?php include __DIR__ . '/header.php'; ?> -->

    <div class="event-hero" style="background-color: rgba(67,97,238,0.85); <?= $image ? 'background-image: url("' . htmlspecialchars($image) . '");' : '' ?>">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <?php if ($eventTags): ?><span class="event-tag"><?= htmlspecialchars($eventTags) ?></span><?php endif; ?>
                    <h1 class="display-4 fw-bold mb-3"><?= htmlspecialchars($title) ?></h1>
                    <p class="lead mb-4"><?= htmlspecialchars(mb_strimwidth($description, 0, 160, '...')) ?></p>
                    <div class="d-flex justify-content-center gap-3">
                        <span class="badge bg-light text-dark fs-6 px-3 py-2"><i class="bi bi-calendar me-1"></i> <?= htmlspecialchars($displayDate) ?></span>
                        <span class="badge bg-light text-dark fs-6 px-3 py-2"><i class="bi bi-clock me-1"></i> <?= htmlspecialchars($displayTime) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="card event-details-card mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-4">About This Event</h3>
                        <p class="card-text"><?= nl2br(htmlspecialchars($description)) ?></p>

                        <?php if ($organizerName): ?>
                            <h5 class="mt-4 mb-2">Organizer</h5>
                            <p><?= htmlspecialchars($organizerName) ?></p>
                        <?php endif; ?>

                        <?php if ($event['speakers'] ?? false): ?>
                            <h5 class="mt-4 mb-3">Speakers</h5>
                            <div class="row">
                                <?php foreach (json_decode($event['speakers'], true) ?? [] as $s): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card speaker-card p-2 h-100">
                                            <?php if (!empty($s['photo'])): ?><img src="<?= htmlspecialchars($s['photo']) ?>" class="speaker-img w-100" alt="<?= htmlspecialchars($s['name'] ?? '') ?>"><?php endif; ?>
                                            <div class="card-body">
                                                <h6 class="mb-1"><?= htmlspecialchars($s['name'] ?? '') ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($s['title'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card event-details-card mb-4">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Event Details</h3>
                        <div class="event-info-item mb-3 d-flex">
                            <div class="me-3"><i class="bi bi-calendar text-primary"></i></div>
                            <div>
                                <h6 class="mb-0">Date & Time</h6>
                                <p class="mb-0"><?= htmlspecialchars($displayDate) ?><br><?= htmlspecialchars($displayTime) ?></p>
                            </div>
                        </div>

                        <div class="event-info-item mb-3 d-flex">
                            <div class="me-3"><i class="bi bi-geo-alt text-primary"></i></div>
                            <div>
                                <h6 class="mb-0">Location</h6>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($venue)) ?></p>
                            </div>
                        </div>

                        <?php if ($organizerName): ?>
                        <div class="event-info-item mb-3 d-flex">
                            <div class="me-3"><i class="bi bi-people text-primary"></i></div>
                            <div>
                                <h6 class="mb-0">Organizer</h6>
                                <p class="mb-0"><?= htmlspecialchars($organizerName) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($eventTags): ?>
                        <div class="event-info-item mb-3 d-flex">
                            <div class="me-3"><i class="bi bi-tag text-primary"></i></div>
                            <div>
                                <h6 class="mb-0">Event Type</h6>
                                <p class="mb-0"><?= htmlspecialchars($eventTags) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2 mt-3">
                            <a href="events.php" class="btn btn-outline-primary">Back to events</a>
                            <a href="#" class="btn btn-primary btn-rsvp">RSVP / Register</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                <li class="mb-2"><a href="dashboard.html" class="text-decoration-none text-light">Dashboard</a></li>
                                <li class="mb-2"><a href="announcements.html" class="text-decoration-none text-light">Announcements</a></li>
                                <li class="mb-2"><a href="events.html" class="text-decoration-none text-light">Events</a></li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="#" class="text-decoration-none text-light">Timetable</a></li>
                                <li class="mb-2"><a href="#" class="text-decoration-none text-light">Results</a></li>
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
                    <p class="mb-md-0">&copy; <?= date('Y') ?> UniPortal. All rights reserved.</p>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/student.js"></script>
</body>
</html>