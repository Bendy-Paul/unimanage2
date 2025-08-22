<?php
include_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter setup
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT a.*, u.name as author_name 
          FROM announcements a 
          JOIN users u ON a.author_id = u.user_id 
          WHERE a.dept_id = ?";
$params = [$_SESSION['user']['dept_id']];

if (!empty($status_filter)) {
    $query .= " AND a.priority = ?";
    $params[] = $status_filter;
}

if (!empty($search_term)) {
    $query .= " AND (a.title LIKE ? OR a.content LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
}

// FIX: Convert limit and offset to integers and add them directly to the query
// instead of passing them as parameters to avoid the string conversion issue
$query .= " ORDER BY a.publish_date DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

// Get announcements
$announcements = db_query($query, $params);

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM announcements WHERE dept_id = ?";
$count_params = [$_SESSION['user']['dept_id']];

if (!empty($status_filter)) {
    $count_query .= " AND priority = ?";
    $count_params[] = $status_filter;
}

if (!empty($search_term)) {
    $count_query .= " AND (title LIKE ? OR content LIKE ?)";
    $count_params[] = "%$search_term%";
    $count_params[] = "%$search_term%";
}

$total_announcements = db_query("SELECT COUNT(*) as count FROM announcements")->fetch()['count'];
$total_pages = ceil($total_announcements / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - UniPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo file_exists('../assets/css/admin.css') ? '../assets/css/admin.css' : '../../assets/css/admin.css'; ?>">

</head>

<body>
    <div class="d-flex">
        <div class="main-content w-100" id="mainContent">

            <?php include_once('../../includes/navbar.php') ?>

            <div class="container-fluid mt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold"><i class="bi bi-megaphone me-2"></i>Announcements Management</h2>
                    <a href="create_announcement.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Create New
                    </a>
                </div>

                <!-- Filters -->
                <div class="card mb-4 filter-card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="low" <?= $status_filter == 'low' ? 'selected' : '' ?>>low</option>
                                    <option value="medium" <?= $status_filter == 'medium' ? 'selected' : '' ?>>medium</option>
                                    <option value="high" <?= $status_filter == 'high' ? 'selected' : '' ?>>high</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Search announcements..."
                                    value="<?= htmlspecialchars($search_term) ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-funnel me-1"></i>Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Announcements List -->
                <?php if (empty($announcements)): ?>
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="bi bi-info-circle me-2 fs-4"></i>
                        <div>No announcements found. <a href="create_announcement.php" class="alert-link">Create one now</a>.</div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($announcements as $announcement):
                            $is_expired = strtotime($announcement['expiry_date']) < time();
                            $status = $is_expired ? 'expired' : $announcement['priority'];
                        ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card announcement-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge status-badge bg-<?=
                                                                                $status == 'low' ? 'success' : ($status == 'medium' ? 'warning' : 'danger')
                                                                                ?>">
                                                <?= ucfirst($status) ?>
                                            </span>
                                            <small class="text-muted">
                                                <?= date('M j, Y', strtotime($announcement['publish_date'])) ?>
                                            </small>
                                        </div>
                                        <h5 class="card-title fw-bold"><?= htmlspecialchars($announcement['title']) ?></h5>
                                        <p class="card-text text-truncate"><?= htmlspecialchars($announcement['content']) ?></p>
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-person me-1"></i>By: <?= htmlspecialchars($announcement['author_name']) ?>
                                        </p>
                                        <?php if ($announcement['expiry_date']): ?>
                                            <p class="text-muted small">
                                                <i class="bi bi-clock me-1"></i>Expires: <?= date('M j, Y', strtotime($announcement['expiry_date'])) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-transparent d-flex justify-content-between">
                                        <a href="edit_announcement.php?id=<?= $announcement['annc_id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </a>
                                        <a href="delete_announcement.php?id=<?= $announcement['annc_id'] ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this announcement?')">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Announcements pagination" class="mt-5">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= $status_filter ?>&search=<?= urlencode($search_term) ?>">
                                        <i class="bi bi-chevron-left me-1"></i>Previous
                                    </a>
                                </li>

                                <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $total_pages); $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&status=<?= $status_filter ?>&search=<?= urlencode($search_term) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= $status_filter ?>&search=<?= urlencode($search_term) ?>">
                                        Next<i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>

</html>