<?php
function authenticate($email, $password) {
    // Get user by email
    $stmt = db_query("SELECT * FROM users WHERE email = ?", [$email]);
    $user = $stmt->fetch();
    // print_r($user);

    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Get additional student info if needed
            print_r($user);

        $extra_info = [];
        if ($user['role'] === 'student') {
            $stmt = db_query("SELECT * FROM students WHERE user_id = ?", [$user['user_id']]);
            $extra_info = $stmt->fetch();
        }
        
        // Set session
        $_SESSION['user'] = [
            'id' => $user['user_id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'dept_id' => $user['department_id'],
            'year' => $extra_info['year'] ?? null,
            'enrollment_date' => $extra_info['enrollment_date'] ?? null,
            'student_info' => $extra_info
        ];
        
            session_start();

        return true;
    }
    
    return false;
}

function require_student() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
        header('Location: ../student/dashboard.php');
        exit();
    }
}


function require_admin() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header('Location: ../admin/dashboard.php');
        exit();
    }
}
?>