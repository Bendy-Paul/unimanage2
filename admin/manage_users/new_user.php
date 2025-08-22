<?php
include_once '../../includes/sidebar.php';
require_once '../../includes/db_connect.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = trim($_POST['name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $session = trim($_POST['session'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) $errors['name'] = 'Name is required';
    if (empty($student_id)) $errors['student_id'] = 'Student ID is required';
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    if (empty($contact)) $errors['contact'] = 'Contact number is required';
    if (empty($dob)) $errors['dob'] = 'Date of birth is required';
    if (empty($address)) $errors['address'] = 'Address is required';
    if (empty($session)) $errors['session'] = 'Session is required';
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    // Check if student ID or email already exists
    if (empty($errors)) {
        $stmt = db_query("SELECT user_id FROM users WHERE email = ? OR user_id = ?", [$email, $student_id]);
        if ($stmt->fetch()) {
            $errors['general'] = 'Student ID or Email already exists';
        }
    }

    // Insert into database if no errors
    if (empty($errors)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Start transaction
            $pdo->beginTransaction();

            // Insert into users table
            db_query(
                "INSERT INTO users (user_id, name, email, password_hash, role, department_id, contact, date_of_birth, address) 
                 VALUES (?, ?, ?, ?, 'student', ?, ?, ?, ?)",
                [$student_id, $name, $email, $password_hash, $_SESSION['user']['dept_id'] ?? null, $contact, $dob, $address]
            );

            // Insert into students table
            db_query(
                "INSERT INTO students (user_id, year, enrollment_date) 
                 VALUES (?, ?, CURDATE())",
                [$student_id, $session]
            );

            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors['general'] = 'Error creating user: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

</head>

<body>
    <style>
        .form-container {
            max-width: 800px;
            margin: auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: 500;
            color: #333;
        }

        .is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.875rem;
        }

        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
        }
    </style>
    </head>

    <body>
        <div class="d-flex">
            <div class="main-content w-100" id="mainContent">
                <?php include_once('../../includes/navbar.php') ?>

                <div class="container-fluid pt-4">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-container">
                                <h2 class="mb-4 text-center">
                                    <i class="bi bi-person-plus me-2"></i>Create New Student
                                </h2>

                                <?php if ($success): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        Student created successfully!
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

                                <form id="studentForm" method="POST" novalidate>
                                    <div class="row">
                                        <!-- Personal Information -->
                                        <div class="col-md-6">
                                            <h5 class="mb-3 text-primary"><i class="bi bi-person me-2"></i>Personal Information</h5>

                                            <div class="mb-3">
                                                <label class="form-label">Full Name *</label>
                                                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                                    name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                                                <?php if (isset($errors['name'])): ?>
                                                    <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Student ID *</label>
                                                <input type="text" class="form-control <?= isset($errors['student_id']) ? 'is-invalid' : '' ?>"
                                                    name="student_id" value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>" required>
                                                <?php if (isset($errors['student_id'])): ?>
                                                    <div class="invalid-feedback"><?= $errors['student_id'] ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Email Address *</label>
                                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                                    name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                                <?php if (isset($errors['email'])): ?>
                                                    <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Contact Number *</label>
                                                <input type="tel" class="form-control <?= isset($errors['contact']) ? 'is-invalid' : '' ?>"
                                                    name="contact" value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>" required>
                                                <?php if (isset($errors['contact'])): ?>
                                                    <div class="invalid-feedback"><?= $errors['contact'] ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Additional Information -->
                                        <div class="col-md-6">
                                            <h5 class="mb-3 text-primary"><i class="bi bi-info-circle me-2"></i>Additional Information</h5>

                                            <div class="mb-3">
                                                <label class="form-label">Date of Birth *</label>
                                                <input type="date" class="form-control <?= isset($errors['dob']) ? 'is-invalid' : '' ?>"
                                                    name="dob" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>" required>
                                                <?php if (isset($errors['dob'])): ?>
                                                    <div class="invalid-feedback"><?= $errors['dob'] ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Address *</label>
                                                <textarea class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>"
                                                    name="address" rows="3" required><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                                                <?php if (isset($errors['address'])): ?>
                                                    <div class="invalid-feedback"><?= $errors['address'] ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Session *</label>
                                                <input type="text" class="form-control <?= isset($errors['session']) ? 'is-invalid' : '' ?>"
                                                    name="session" value="<?= htmlspecialchars($_POST['session'] ?? '') ?>"
                                                    placeholder="e.g., 2023-2024" required>
                                                <?php if (isset($errors['session'])): ?>
                                                    <div class="invalid-feedback"><?= $errors['session'] ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Password *</label>
                                                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                                    name="password" id="password" required>
                                                <div class="password-strength" id="passwordStrength"></div>
                                                <?php if (isset($errors['password'])): ?>
                                                    <div class="invalid-feedback"><?= $errors['password'] ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Confirm Password *</label>
                                                <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                                                    name="confirm_password" required>
                                                <?php if (isset($errors['confirm_password'])): ?>
                                                    <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-12 text-center">
                                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                                <i class="bi bi-person-plus me-2"></i>Create Student
                                            </button>
                                            <a href="students.php" class="btn btn-secondary btn-lg ms-2">
                                                <i class="bi bi-arrow-left me-2"></i>Back to Students
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('studentForm');
                const password = document.getElementById('password');
                const passwordStrength = document.getElementById('passwordStrength');

                // Password strength indicator
                password.addEventListener('input', function() {
                    const strength = calculatePasswordStrength(this.value);
                    passwordStrength.style.width = strength + '%';
                    passwordStrength.style.backgroundColor =
                        strength < 40 ? '#dc3545' : strength < 70 ? '#ffc107' : '#198754';
                });

                // Form validation
                form.addEventListener('submit', function(e) {
                    let isValid = true;
                    const inputs = form.querySelectorAll('input[required], textarea[required]');

                    inputs.forEach(input => {
                        if (!input.value.trim()) {
                            showError(input, 'This field is required');
                            isValid = false;
                        } else if (input.type === 'email' && !isValidEmail(input.value)) {
                            showError(input, 'Please enter a valid email address');
                            isValid = false;
                        } else if (input.name === 'password' && input.value.length < 8) {
                            showError(input, 'Password must be at least 8 characters');
                            isValid = false;
                        } else if (input.name === 'confirm_password' && input.value !== password.value) {
                            showError(input, 'Passwords do not match');
                            isValid = false;
                        } else {
                            clearError(input);
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        // Scroll to first error
                        const firstError = form.querySelector('.is-invalid');
                        if (firstError) {
                            firstError.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    }
                });

                function showError(input, message) {
                    input.classList.add('is-invalid');
                    let feedback = input.nextElementSibling;
                    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                        feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        input.parentNode.insertBefore(feedback, input.nextSibling);
                    }
                    feedback.textContent = message;
                }

                function clearError(input) {
                    input.classList.remove('is-invalid');
                    const feedback = input.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.remove();
                    }
                }

                function isValidEmail(email) {
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
                }

                function calculatePasswordStrength(password) {
                    let strength = 0;
                    if (password.length >= 8) strength += 25;
                    if (/[A-Z]/.test(password)) strength += 25;
                    if (/[0-9]/.test(password)) strength += 25;
                    if (/[^A-Za-z0-9]/.test(password)) strength += 25;
                    return Math.min(strength, 100);
                }
            });
        </script>
    </body>

</html>