<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;
if(!$user || $user['role'] !== 'admin') {
    file_exists('../index.php') ?  header('Location: ../index.php') : header('Location: ../../index.php');
    // header('Location: ../index.php');
    exit();
}


?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="<?php echo file_exists('../assets/css/admin.css') ? '../assets/css/admin.css' : '../../assets/css/admin.css'; ?>">
<style>
                .submenu{
                margin-left: 20px !important;
            }

</style>
<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="p-4">
        <h4 class="text-center mb-4">
            <i class="bi bi-book me-2 text-white"></i>
            <span class="fw-bold">UniPortal</span>
        </h4>

        <ul class="nav flex-column mt-4">
            <li class="nav-item">
                <a class="nav-link active" href="<?php echo file_exists('dashboard.php') ? 'dashboard.php' : '../dashboard.php'; ?>">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>

            <!-- Students Dropdown -->
            <li class="nav-item">
                <span class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#studentsSubmenu" role="button">
                    <i class="bi bi-people"></i>
                    Students
                </span>
                <div class="submenu collapse" id="studentsSubmenu">
                    <a class="nav-link" href="<?php echo file_exists('./manage_users/new_user.php') ? './manage_users/new_user.php' : '../manage_users/new_user.php'; ?>">
                        <i class="bi bi-plus-circle"></i> Add Student
                    </a>

                    <a class="nav-link" href="<?php echo file_exists('./manage_users/view_students.php') ? './manage_users/view_students.php' : '../manage_users/view_students.php'; ?>">
                        <i class="bi bi-eye"></i> View Students
                    </a>
                </div>
            </li>

            <!-- Courses Dropdown -->
            <li class="nav-item">
                <span class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#coursesSubmenu" role="button">
                    <i class="bi bi-journal-bookmark"></i>
                    Courses
                </span>
                <div class="submenu collapse" id="coursesSubmenu">
                    <a class="nav-link" href="<?php echo file_exists('./manage_courses/index.php') ? './manage_courses/index.php' : '../manage_courses/index.php'; ?>">
                        <i class="bi bi-eye"></i> View Courses
                    </a>
                    <a class="nav-link" href="<?php echo file_exists('./manage_courses/new_course.php') ? './manage_courses/new_course.php' : '../manage_courses/new_course.php'; ?>">
                        <i class="bi bi-plus-circle"></i> Add Course
                    </a>
                </div>
            </li>

            <!-- Results Dropdown -->
            <li class="nav-item">
                <span class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#resultsSubmenu" role="button">
                    <i class="bi bi-journal-check"></i>
                    Results
                </span>
                <div class="submenu collapse" id="resultsSubmenu">
                    <a class="nav-link" href="<?php echo file_exists('./manage_results/index.php') ? './manage_results/index.php' : '../manage_results/index.php'; ?>">
                        <i class="bi bi-eye"></i> View Results
                    </a>
                    <a class="nav-link" href="<?php echo file_exists('./manage_results/new_result.php') ? './manage_results/new_result.php' : '../manage_results/new_result.php'; ?>">
                        <i class="bi bi-plus-circle"></i> Add Result
                    </a>
                </div>
            </li>

            <!-- Events Dropdown -->
            <li class="nav-item">
                <span class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#eventsSubmenu" role="button">
                    <i class="bi bi-calendar-event"></i>
                    Events
                </span>
                <div class="submenu collapse" id="eventsSubmenu">
                    <a class="nav-link" href="<?php echo file_exists('./manage_events/index.php') ? './manage_events/index.php' : '../manage_events/index.php'; ?>">
                        <i class="bi bi-eye"></i> View Events
                    </a>
                    <a class="nav-link" href="<?php echo file_exists('./manage_events/new_event.php') ? './manage_events/new_event.php' : '../manage_events/new_event.php'; ?>">
                        <i class="bi bi-plus-circle"></i> Create Event
                    </a>
                </div>
            </li>

            <!-- Timetables Dropdown -->
            <li class="nav-item">
                <span class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#timetableSubmenu" role="button">
                    <i class="bi bi-table"></i>
                    Timetables
                </span>
                <div class="submenu collapse" id="timetableSubmenu">
                    <a class="nav-link" href="<?php echo file_exists('./manage_timetables/index.php') ? './manage_timetables/index.php' : '../manage_timetables/index.php'; ?>">
                        <i class="bi bi-eye"></i> View Timetable
                    </a>
                    <a class="nav-link" href="<?php echo file_exists('./manage_timetables/new_slot.php') ? './manage_timetables/new_slot.php' : '../manage_timetables/new_slot.php'; ?>">
                        <i class="bi bi-plus-circle"></i> New Slot
                    </a>
                </div>
            </li>

            <!-- Announcements Dropdown -->
            <li class="nav-item">
                <span class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#announcementsSubmenu" role="button">
                    <i class="bi bi-megaphone"></i>
                    Announcements
                </span>
                <div class="submenu collapse" id="announcementsSubmenu">
                    <a class="nav-link" href="<?php echo file_exists('./manage_announcements/new_announcement.php') ? './manage_announcements/new_announcement.php' : '../manage_announcements/new_announcement.php'; ?>">
                        <i class="bi bi-plus-circle"></i> Create Announcement
                    </a>
                    <a class="nav-link" href="<?php echo file_exists('./manage_announcements/view_announcements.php') ? './manage_announcements/view_announcements.php' : '../manage_announcements/view_announcements.php'; ?>">
                        <i class="bi bi-eye"></i> View Announcements
                    </a>
                </div>
            </li>

            <!-- Departments Dropdown -->
            <!-- <li class="nav-item">
                <span class="nav-link dropdown-btn" data-bs-toggle="collapse" href="#departmentsSubmenu" role="button">
                    <i class="bi bi-building"></i>
                    Departments
                </span>
                <div class="submenu collapse" id="departmentsSubmenu">
                    <a class="nav-link" href="departments.php?action=view">
                        <i class="bi bi-eye"></i> View Departments
                    </a>
                    <a class="nav-link" href="departments.php?action=add">
                        <i class="bi bi-plus-circle"></i> Add Department
                    </a>
                </div>
            </li> -->
            
            <!-- Academic Year (admin-only edit) -->
            <li class="nav-item">
                <a class="nav-link" href="<?php echo file_exists('./edit_academic_year.php') ? './edit_academic_year.php' : '../edit_academic_year.php'; ?>">
                    <i class="bi bi-calendar3"></i> Edit Academic Year
                </a>
            </li>
        </ul>
    </div>
</div> 

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
            $(document).ready(function() {
                // Toggle sidebar
                $('#toggleSidebar').click(function() {
                    $('#sidebar').toggleClass('sidebar-collapsed');
                    $('#mainContent').toggleClass('main-content-expanded');
                });

                // Responsive sidebar for mobile
                function handleResponsiveSidebar() {
                    if ($(window).width() < 992) {
                        $('#sidebar').addClass('sidebar-collapsed');
                        $('#mainContent').addClass('main-content-expanded');
                    } else {
                        $('#sidebar').removeClass('sidebar-collapsed');
                        $('#mainContent').removeClass('main-content-expanded');
                    }
                }

                $(window).resize(handleResponsiveSidebar);
                handleResponsiveSidebar();
            });
        </script>