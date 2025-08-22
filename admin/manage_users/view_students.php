<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search and filter
$search = $_GET['search'] ?? '';
$year_filter = $_GET['year'] ?? '';

// Build base query
$query = "SELECT s.*, u.name, u.email, d.name as department_name 
          FROM students s 
          JOIN users u ON s.user_id = u.user_id 
          LEFT JOIN departments d ON u.department_id = d.dept_id 
          WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR s.user_id LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

if (!empty($year_filter) && is_numeric($year_filter)) {
    $query .= " AND s.year = ?";
    $params[] = $year_filter;
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as count 
                FROM students s 
                JOIN users u ON s.user_id = u.user_id 
                LEFT JOIN departments d ON u.department_id = d.dept_id 
                WHERE 1=1";

$count_params = [];
if (!empty($search)) {
    $count_query .= " AND (u.name LIKE ? OR u.email LIKE ? OR s.user_id LIKE ?)";
    $count_params = array_merge($count_params, [$search_term, $search_term, $search_term]);
}

if (!empty($year_filter) && is_numeric($year_filter)) {
    $count_query .= " AND s.year = ?";
    $count_params[] = $year_filter;
}

$count_stmt = db_query($count_query, $count_params);
$total_records = $count_stmt->fetch()['count'];
$total_pages = ceil($total_records / $limit);

// Add sorting and pagination
$query .= " ORDER BY u.name LIMIT $limit OFFSET $offset";
// Remove the limit and offset from the parameters array

// Fetch students
$stmt = db_query($query, $params);
$students = $stmt->fetchAll();

// Get unique years for filter dropdown
$years_stmt = db_query("SELECT DISTINCT year FROM students ORDER BY year");
$years = $years_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students | UniPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        .table-responsive {
            min-height: 400px;
        }

        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .status-badge {
            font-size: 0.75rem;
        }

        .search-form {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1rem;
        }
    </style>
</head>

<body>

    <div class="d-flex">
        <div class="main-content w-100" id="mainContent">
            <?php require_once '../../includes/navbar.php'; ?>
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-people me-2"></i>Student Management</h2>
                    <a href="create_user.php" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i> Add New Student
                    </a>
                </div>

                <!-- Search and Filter Form -->
                <div class="card search-form mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-5">
                                <label for="search" class="form-label">Search Students</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    value="<?= htmlspecialchars($search) ?>"
                                    placeholder="Search by name, email, or ID...">
                            </div>
                            <div class="col-md-3">
                                <label for="year" class="form-label">Filter by Year</label>
                                <select class="form-select" id="year" name="year">
                                    <option value="">All Years</option>
                                    <?php foreach ($years as $y): ?>
                                        <option value="<?= $y['year'] ?>" <?= $year_filter == $y['year'] ? 'selected' : '' ?>>
                                            Year <?= $y['year'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-1"></i> Search
                                </button>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label d-block">&nbsp;</label>
                                <a href="students.php" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Students Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Student Records (<?= $total_records ?> found)</h5>
                        <div>
                            <span class="text-muted small">Page <?= $page ?> of <?= $total_pages ?></span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Year</th>
                                        <th>Department</th>
                                        <th>Enrollment Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($students)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="bi bi-people display-4 d-block mb-2"></i>
                                                    No students found.
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($student['user_id']) ?></td>
                                                <td><?= htmlspecialchars($student['name']) ?></td>
                                                <td><?= htmlspecialchars($student['email']) ?></td>
                                                <td>Year <?= htmlspecialchars($student['year']) ?></td>
                                                <td><?= htmlspecialchars($student['department_name'] ?? 'N/A') ?></td>
                                                <td><?= date('M j, Y', strtotime($student['enrollment_date'])) ?></td>
                                                <td>
                                                    <span class="badge bg-success status-badge">Active</span>
                                                </td>
                                                <td class="action-buttons">
                                                    <a href="edit_student.php?id=<?= $student['user_id'] ?>"
                                                        class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        title="Delete" data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal"
                                                        data-id="<?= $student['user_id'] ?>"
                                                        data-name="<?= htmlspecialchars($student['name']) ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="card-footer">
                            <nav aria-label="Student pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                                    </li>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete student: <strong id="deleteStudentName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone and will permanently delete all student records.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a id="deleteConfirmBtn" href="#" class="btn btn-danger">Delete Student</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Delete modal handler
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const studentId = button.getAttribute('data-id');
            const studentName = button.getAttribute('data-name');

            document.getElementById('deleteStudentName').textContent = studentName;
            document.getElementById('deleteConfirmBtn').href = `delete_student.php?id=${studentId}`;
        });
    </script>
</body>

</html>