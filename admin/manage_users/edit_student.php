<?php
require_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

$student_id = $_GET['id'] ?? null;
$errors = [];
$success = false;

// Fetch student data
$student = null;
if ($student_id) {
    $stmt = db_query(
        "SELECT s.*, u.name, u.email, u.department_id, u.contact, u.date_of_birth, u.address
         FROM students s 
         JOIN users u ON s.user_id = u.user_id 
         WHERE s.user_id = ?",
        [$student_id]
    );
    $student = $stmt->fetch();

    if (!$student) {
        header('Location: students.php');
        exit();
    }
} else {
    header('Location: students.php');
    exit();
}

// Fetch departments for dropdown
$dept_stmt = db_query("SELECT * FROM departments");
$departments = $dept_stmt->fetchAll();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $year = $_POST['year'] ?? '';
    $department_id = $_POST['department_id'] ?? '';

    // Validation
    if (empty($name)) $errors['name'] = 'Name is required';
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    if (empty($contact)) $errors['contact'] = 'Contact number is required';
    if (empty($dob)) $errors['dob'] = 'Date of birth is required';
    if (empty($address)) $errors['address'] = 'Address is required';
    if (empty($year)) $errors['year'] = 'Year is required';

    // Check if email already exists for another user
    if (empty($errors['email'])) {
        $stmt = db_query("SELECT user_id FROM users WHERE email = ? AND user_id != ?", [$email, $student_id]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email already exists for another user';
        }
    }

    // Update database if no errors
    if (empty($errors)) {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Update users table
            db_query(
                "UPDATE users SET name = ?, email = ?, department_id = ?, contact = ?, date_of_birth = ?, address = ? WHERE user_id = ?",
                [$name, $email, $department_id, $contact, $dob, $address, $student_id]
            );

            // Update students table
            db_query(
                "UPDATE students SET year = ? WHERE user_id = ?",
                [$year, $student_id]
            );

            $pdo->commit();
            $success = true;

            // Refresh student data
            $stmt = db_query(
                "SELECT s.*, u.name, u.email, u.department_id, u.contact, u.date_of_birth, u.address
         FROM students s 
         JOIN users u ON s.user_id = u.user_id 
         WHERE s.user_id = ?",
                [$student_id]
            );
            $student = $stmt->fetch();
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors['general'] = 'Error updating student: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student | UniPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css//admin.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <div class="d-flex">
        <div class="main-content w-100" id="mainContent">
            <?php require_once '../../includes/navbar.php'; ?>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="form-container">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="mb-0">
                                    <i class="bi bi-pencil-square me-2"></i>Edit Student
                                </h2>
                                <a href="students.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Back to Students
                                </a>
                            </div>

                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    Student updated successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($errors['general'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <?= htmlspecialchars($errors['general']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="row">
                                    <!-- Personal Information -->
                                    <div class="col-md-6">
                                        <h5 class="mb-3 text-primary"><i class="bi bi-person me-2"></i>Personal Information</h5>

                                        <div class="mb-3">
                                            <label class="form-label">Student ID</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($student['user_id']) ?>" disabled>
                                            <div class="form-text">Student ID cannot be changed</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Full Name *</label>
                                            <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                                name="name" value="<?= htmlspecialchars($student['name']) ?>" required>
                                            <?php if (isset($errors['name'])): ?>
                                                <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Email Address *</label>
                                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                                name="email" value="<?= htmlspecialchars($student['email']) ?>" required>
                                            <?php if (isset($errors['email'])): ?>
                                                <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Contact Number *</label>
                                            <input type="tel" class="form-control <?= isset($errors['contact']) ? 'is-invalid' : '' ?>"
                                                name="contact" value="<?= htmlspecialchars($student['contact']) ?>" required>
                                            <?php if (isset($errors['contact'])): ?>
                                                <div class="invalid-feedback"><?= $errors['contact'] ?></div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Date of Birth *</label>
                                            <input type="date" class="form-control <?= isset($errors['dob']) ? 'is-invalid' : '' ?>"
                                                name="dob" value="<?= htmlspecialchars($student['date_of_birth']) ?>" required>
                                            <?php if (isset($errors['dob'])): ?>
                                                <div class="invalid-feedback"><?= $errors['dob'] ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Additional Information -->
                                    <div class="col-md-6">
                                        <h5 class="mb-3 text-primary"><i class="bi bi-info-circle me-2"></i>Additional Information</h5>

                                        <div class="mb-3">
                                            <label class="form-label">Address *</label>
                                            <textarea class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>"
                                                name="address" rows="3" required><?= htmlspecialchars($student['address']) ?></textarea>
                                            <?php if (isset($errors['address'])): ?>
                                                <div class="invalid-feedback"><?= $errors['address'] ?></div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Year *</label>
                                            <select class="form-select <?= isset($errors['year']) ? 'is-invalid' : '' ?>" name="year" required>
                                                <option value="">Select Year</option>
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <option value="<?= $i ?>" <?= $student['year'] == $i ? 'selected' : '' ?>>
                                                        Year <?= $i ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                            <?php if (isset($errors['year'])): ?>
                                                <div class="invalid-feedback"><?= $errors['year'] ?></div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Department</label>
                                            <select class="form-select" name="department_id">
                                                <option value="">Select Department</option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?= $dept['dept_id'] ?>"
                                                        <?= $student['department_id'] == $dept['dept_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($dept['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Enrollment Date</label>
                                            <input type="text" class="form-control"
                                                value="<?= date('M j, Y', strtotime($student['enrollment_date'])) ?>" disabled>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary btn-lg px-5">
                                            <i class="bi bi-check-circle me-2"></i>Update Student
                                        </button>
                                        <a href="students.php" class="btn btn-secondary btn-lg ms-2">
                                            Cancel
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>