<?php
include_once '../includes/header.php';
include_once '../includes/db_connect.php';

$user = $_SESSION['user'] ?? null;
$student = null;
if ($user) {
    $userId = $user['id'] ?? $user['user_id'] ?? null;
    if ($userId) {
        $stmt = db_query('SELECT u.*, s.* FROM users u LEFT JOIN students s ON u.user_id = s.user_id WHERE u.user_id = ?', [$userId]);
        $student = $stmt->fetch();
        // fetch department name
        if (!empty($student['department_id'])) {
            $d = db_query('SELECT name FROM departments WHERE dept_id = ?', [$student['department_id']])->fetch();
            $student['department_name'] = $d['name'] ?? null;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | UniPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/student.css">
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <div class="position-relative d-inline-block">
                                    <i class="bi bi-person-circle fs-1 text-primary rounded-circle border border-4 border-primary" style="font-size: 120px; width: 120px; height: 120px; line-height: 120px; display: inline-block; background: #f8f9fa;"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h2 class="mb-1"><?php echo htmlspecialchars($student['name'] ?? ($user['name'] ?? 'Student')); ?></h2>
                                <p class="text-muted mb-2"><i class="bi bi-mortarboard me-1"></i> Year <?php echo htmlspecialchars($student['year'] ?? ''); ?> - <?php echo htmlspecialchars($student['department_name'] ?? ''); ?></p>
                                <p class="mb-2"><i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($student['email'] ?? ($user['email'] ?? '')); ?></p>
                                <p class="mb-0"><i class="bi bi-calendar me-1"></i> Enrolled: <?php echo htmlspecialchars($student['enrollment_date'] ?? ''); ?></p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <span class="badge bg-primary-light text-primary fs-6 mb-2"><i class="bi bi-award me-1"></i> Profile</span>
                                <div class="mt-2">
                                    <a href="edit_profile.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i> Edit Profile</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header"><h5 class="mb-0"><i class="bi bi-person me-2"></i> Personal Information</h5></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr><th width="40%">Full Name</th><td><?php echo htmlspecialchars($student['name'] ?? ''); ?></td></tr>
                                            <tr><th>Student ID</th><td><?php echo htmlspecialchars($student['user_id'] ?? ''); ?></td></tr>
                                            <tr><th>Date of Birth</th><td><?php echo htmlspecialchars($student['date_of_birth'] ?? ''); ?></td></tr>
                                            <tr><th>Contact Number</th><td><?php echo htmlspecialchars($student['contact'] ?? ''); ?></td></tr>
                                            <tr><th>Address</th><td><?php echo nl2br(htmlspecialchars($student['address'] ?? '')); ?></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header"><h5 class="mb-0"><i class="bi bi-book me-2"></i> Academic Information</h5></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr><th width="40%">Department</th><td><?php echo htmlspecialchars($student['department_name'] ?? ''); ?></td></tr>
                                            <tr><th>Year</th><td><?php echo htmlspecialchars($student['year'] ?? ''); ?></td></tr>
                                            <tr><th>Enrollment Date</th><td><?php echo htmlspecialchars($student['enrollment_date'] ?? ''); ?></td></tr>
                                            <!-- <tr><th>Advisor</th><td><?php echo htmlspecialchars($student['advisor'] ?? ''); ?></td></tr> -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer"></footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/student.js"></script>
</body>

</html>