<?php
session_start();
require_once 'includes/db_connect.php'; // Database connection
require_once 'includes/auth.php'; // Authentication functions

// Redirect logged in users
if (isset($_SESSION['user'])) {
    header("Location: " . ($_SESSION['user']['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php'));
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    echo $email;
    $password = $_POST['password'] ?? '';
    echo $password;

    if (authenticate($email, $password)) {
        // Successful login - redirect based on role
        $redirect = $_SESSION['user']['role'] === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php';
        header("Location: $redirect");
        exit();
        
    } else {
        $login_error = "Invalid email or password";
        echo $login_error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/main.css">
</head>
<body>
    <!-- Minimal Navbar -->
    <nav class="login-navbar">
        <a href="#" class="navbar-brand">
            <i class="fas fa-graduation-cap"></i>
            EduPortal
        </a>
        <div class="nav-help">
            <a href="#" style="color: rgba(255,255,255,0.9); text-decoration: none;">
                <i class="fas fa-question-circle"></i> Help Center
            </a>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="login-container">
        <div class="login-card animate-fadein">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h1 class="login-title">Student Portal Login</h1>
                <p class="login-subtitle">Access your academic dashboard</p>
            </div>
            
            <form action="" method="POST" class="login-form">
                <div class="form-group">
                    <label for="studentId" class="form-label">Student ID</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="email" name="email" class="form-control" placeholder="Enter your student ID" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                </div>            
                <button type="submit" class="login-button">Sign In</button>
            </form>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-links">
            <a href="#" class="footer-link">Privacy Policy</a>
            <a href="#" class="footer-link">Terms of Service</a>
            <a href="#" class="footer-link">Contact Support</a>
        </div>
        <p>&copy; 2023 EduPortal Student Management System. All rights reserved.</p>
    </footer>


</body>
</html>