<?php
include_once '../includes/header.php';
include_once '../includes/db_connect.php';

$user = $_SESSION['user'] ?? null;
$deptId = $user['dept_id'] ?? $user['department_id'] ?? null;

// fetch announcements for department or global
if ($deptId) {
    $stmt = db_query("SELECT * FROM announcements WHERE dept_id = ? OR dept_id IS NULL ORDER BY publish_date DESC", [$deptId]);
} else {
    $stmt = db_query("SELECT * FROM announcements ORDER BY publish_date DESC");
}
$announcements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | UniPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/student.css">
    </head>
<body>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="dashboard-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="welcome-text">Department Announcements</h1>
                            <p class="welcome-subtext">Latest updates from your department</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search announcements...">
                                <button class="btn btn-primary" type="button">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-megaphone-fill text-primary me-2"></i>All Announcements</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (!empty($announcements)): ?>
                                <?php foreach ($announcements as $ann): ?>
                                    <a class="list-group-item list-group-item-action announcement-card py-3 px-4">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($ann['title']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($ann['publish_date']); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars(substr($ann['content'],0,250)); ?></p>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-3">No announcements found.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add this right before the closing </body> tag -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Search functionality
    $('.input-group input').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.announcement-card').each(function() {
            const title = $(this).find('h6').text().toLowerCase();
            const content = $(this).find('p').text().toLowerCase();
            const date = $(this).find('small').text().toLowerCase();
            
            if (title.includes(searchTerm) || content.includes(searchTerm) || date.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        
            $('.no-results').remove();

        // Show "No results" message if all are hidden
        if ($('.announcement-card:visible').length === 0) {
            $('.card-body').append('<div class="p-3 no-results">No matching announcements found.</div>');
        } else {
            $('.no-results').remove();
        }
    });
    
    // Also trigger search when clicking the search button
    $('.input-group button').on('click', function() {
        $('.input-group input').trigger('keyup');
    });
});
</script>
</body>
</html>