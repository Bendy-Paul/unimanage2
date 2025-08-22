<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;
if(!$user || $user['role'] !== 'student') {
    header('Location: ../index.php');
    exit();
}
?>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-mortarboard me-2"></i>UniPortal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="../student/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    
                    <!-- Academic Resources Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="academicDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-journal-text"></i> Academic
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="academicDropdown">
                            <li><span class="dropdown-header">Results & Progress</span></li>
                            <li><a class="dropdown-item" href="../student/results.php"><i class="bi bi-graph-up me-2"></i> View Results</a></li>
                        </ul>
                    </li>
                    
                    <!-- Schedule Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="scheduleDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-calendar"></i> Schedule
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="scheduleDropdown">
                            <li><span class="dropdown-header">Timetable</span></li>
                            <li><a class="dropdown-item" href="../student/timetable.php"><i class="bi bi-calendar-week me-2"></i>Weekly View</a></li>
                            <li><a class="dropdown-item" href="../student/timetable.php?print=true"><i class="bi bi-download me-2"></i> Download PDF</a></li>
                        </ul>
                    </li>
                    
                    <!-- Campus Life Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="campusDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-people"></i> Campus Life
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="campusDropdown">
                            <li><span class="dropdown-header">Events</span></li>
                            <li><a class="dropdown-item" href="../student/events.php"><i class="bi bi-calendar-event me-2"></i> Upcoming Events</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><span class="dropdown-header">Information</span></li>
                            <li><a class="dropdown-item" href="../student/annoncement.php"><i class="bi bi-megaphone me-2"></i> Announcements</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="../student/profile.php">
                            <i class="bi bi-person"></i> Profile
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown">
                            <span class="d-none d-lg-inline"><?php echo htmlspecialchars($user['name'] ?? 'Guest'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../student/profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../includes/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>