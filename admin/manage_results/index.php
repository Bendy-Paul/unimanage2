<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

// Search
$search = trim($_GET['search'] ?? '');
$params = [];

$sql = "SELECT r.*, u.name as student_name 
     FROM results r 
     LEFT JOIN users u ON r.student_id = u.user_id ";

if ($search !== '') {
    $sql .= " WHERE r.course_code LIKE ? OR u.name LIKE ?";
    $term = "%$search%";
    $params = [$term, $term];
}

$sql .= " ORDER BY r.created_at DESC";

$results_stmt = db_query($sql, $params);
$results = $results_stmt->fetchAll();
?>

<div class="d-flex">
    <div class="main-content w-100" id="mainContent">

        <?php include_once('../../includes/navbar.php') ?>

        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Manage Results</h3>
                <a href="new_result.php" class="btn btn-primary">Add Result</a>
            </div>

            <!-- Search -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" name="search" class="form-control" placeholder="Search by course code or student..." value="<?= htmlspecialchars($search) ?>">
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
                    <?php if (empty($results)): ?>
                        <p>No results found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Course Code</th>
                                        <th>Year</th>
                                        <th>Semester</th>
                                        <th>Marks</th>
                                        <th>Grade</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['student_name'] ?? $r['student_id']) ?></td>
                                            <td><?= htmlspecialchars($r['course_code']) ?></td>
                                            <td><?= htmlspecialchars($r['academic_year']) ?></td>
                                            <td><?= htmlspecialchars($r['semester']) ?></td>
                                            <td><?= htmlspecialchars($r['marks']) ?></td>
                                            <td><?= htmlspecialchars($r['grade']) ?></td>
                                            <td><?= htmlspecialchars($r['status']) ?></td>
                                            <td>
                                                <a href="edit_result.php?id=<?= $r['result_id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                                <a href="delete_result.php?id=<?= $r['result_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this result?')">Delete</a>
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