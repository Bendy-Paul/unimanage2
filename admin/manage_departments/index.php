<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

// Search
$search = trim($_GET['search'] ?? '');
$params = [];

$sql = "SELECT * FROM departments";

if ($search !== '') {
    $sql .= " WHERE name LIKE ? OR code LIKE ?";
    $term = "%$search%";
    $params = [$term, $term];
}

$sql .= " ORDER BY name";
$departments = db_query($sql, $params)->fetchAll();
?>

<div class="d-flex">
    <div class="main-content w-100" id="mainContent">

        <?php include_once('../../includes/navbar.php') ?>

        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Manage Departments</h3>
                <a href="new_department.php" class="btn btn-primary">New Department</a>
            </div>

            <!-- Search -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" name="search" class="form-control" placeholder="Search by name or code..." value="<?= htmlspecialchars($search) ?>">
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
                    <?php if (empty($departments)): ?>
                        <p>No departments found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($departments as $d): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($d['code'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($d['name']) ?></td>
                                            <td><?= htmlspecialchars($d['description'] ?? '') ?></td>
                                            <td>
                                                <a href="edit_department.php?id=<?= $d['dept_id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                                <a href="delete_department.php?id=<?= $d['dept_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this department?')">Delete</a>
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
