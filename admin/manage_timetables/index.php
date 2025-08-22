<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

// Search
$search = trim($_GET['search'] ?? '');
$params = [];

$sql = "SELECT ts.*, c.course_name FROM timetable_slots ts LEFT JOIN courses c ON ts.course_id = c.course_id";
if ($search !== '') {
    $sql .= " WHERE c.course_name LIKE ? OR ts.room_number LIKE ?";
    $term = "%$search%";
    $params = [$term, $term];
}

$sql .= " ORDER BY ts.day_of_week, ts.start_time";
$slots_stmt = db_query($sql, $params);
$slots = $slots_stmt->fetchAll();
?>


<div class="d-flex">
    <div class="main-content w-100" id="mainContent">
        <?php include_once('../../includes/navbar.php') ?>

        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Manage Timetable Slots</h3>
                <a href="new_slot.php" class="btn btn-primary">New Slot</a>
            </div>

            <!-- Search -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" name="search" class="form-control" placeholder="Search by course or room..." value="<?= htmlspecialchars($search) ?>">
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

            <div class="card col-12 m-4">
                <div class="card-body">
                    <?php if (empty($slots)): ?>
                        <p>No timetable slots found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Day</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Room</th>
                                        <th>Building</th>
                                        <th>Valid From</th>
                                        <th>Valid To</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($slots as $s): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($s['course_name'] ?? $s['course_id']) ?></td>
                                            <td><?= htmlspecialchars($s['day_of_week']) ?></td>
                                            <td><?= htmlspecialchars($s['start_time']) ?></td>
                                            <td><?= htmlspecialchars($s['end_time']) ?></td>
                                            <td><?= htmlspecialchars($s['room_number']) ?></td>
                                            <td><?= htmlspecialchars($s['building']) ?></td>
                                            <td><?= htmlspecialchars($s['valid_from']) ?></td>
                                            <td><?= htmlspecialchars($s['valid_to']) ?></td>
                                            <td>
                                                <a href="edit_slot.php?id=<?= $s['slot_id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                                <a href="delete_slot.php?id=<?= $s['slot_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this slot?')">Delete</a>
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