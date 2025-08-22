<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

// Search/filter
$search = trim($_GET['search'] ?? '');
$params = [];

$sql = "SELECT e.*, u.name as organizer_name, d.name as dept_name 
	 FROM events e 
	 LEFT JOIN users u ON e.organizer_id = u.user_id 
	 LEFT JOIN departments d ON e.dept_id = d.dept_id ";

if ($search !== '') {
    $sql .= " WHERE e.title LIKE ? OR e.description LIKE ? OR e.venue LIKE ?";
    $term = "%$search%";
    $params = [$term, $term, $term];
}

$sql .= " ORDER BY e.start_datetime DESC";

$events_stmt = db_query($sql, $params);
$events = $events_stmt->fetchAll();
?>

<div class="d-flex">
    <div class="main-content w-100" id="mainContent">

        <?php include_once('../../includes/navbar.php') ?>

        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Manage Events</h3>
                <a href="new_event.php" class="btn btn-primary">New Event</a>
            </div>

            <!-- Search -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" name="search" class="form-control" placeholder="Search events by title, description or venue..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100">Search</button>
                        </div>
                        <div class="col-md-2">
                            <a href="index.php" class="btn btn-outline-secondary w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($events)): ?>
                        <p>No events found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Venue</th>
                                        <th>Organizer</th>
                                        <th>Department</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $e): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($e['title']) ?></td>
                                            <td><?= htmlspecialchars($e['start_datetime']) ?></td>
                                            <td><?= htmlspecialchars($e['end_datetime']) ?></td>
                                            <td><?= htmlspecialchars($e['venue']) ?></td>
                                            <td><?= htmlspecialchars($e['organizer_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($e['dept_name'] ?? '') ?></td>
                                            <td>
                                                <a href="edit_event.php?id=<?= $e['event_id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                                <a href="delete_event.php?id=<?= $e['event_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this event?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>