<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

// Search
$search = trim($_GET['search'] ?? '');
$params = [];

$sql = "SELECT c.*, d.name as department_name, u.name as lecturer_name 
     FROM courses c 
     LEFT JOIN departments d ON c.department_id = d.dept_id 
     LEFT JOIN users u ON c.lecturer_id = u.user_id";

if ($search !== '') {
    $sql .= " WHERE c.course_code LIKE ? OR c.course_name LIKE ? OR d.name LIKE ?";
    $term = "%$search%";
    $params = [$term, $term, $term];
}

$sql .= " ORDER BY c.course_name";
$courses = db_query($sql, $params)->fetchAll();
?>

<div class="d-flex">
    <div class="main-content w-100" id="mainContent">

        <?php include_once('../../includes/navbar.php') ?>

        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Manage Courses</h3>
                <a href="new_course.php" class="btn btn-primary">New Course</a>
            </div>

            <!-- Search -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" name="search" class="form-control" placeholder="Search by code, name or department..." value="<?= htmlspecialchars($search) ?>">
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
                    <?php if (empty($courses)): ?>
                        <p>No courses found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Lecturer</th>
                                        <th>Credits</th>
                                        <th>Year</th>
                                        <th>Semester</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $c): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($c['course_code']) ?></td>
                                            <td><?= htmlspecialchars($c['course_name']) ?></td>
                                            <td><?= htmlspecialchars($c['department_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($c['lecturer_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($c['credit_hours']) ?></td>
                                            <td><?= htmlspecialchars($c['target_year']) ?></td>
                                            <td><?= htmlspecialchars($c['semester']) ?></td>
                                            <td>
                                                <a href="edit_course.php?id=<?= $c['course_id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                                <a href="delete_course.php?id=<?= $c['course_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this course?')">Delete</a>
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