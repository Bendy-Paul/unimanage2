            <header class="header sticky-top px-4 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <button id="toggleSidebar" class="me-3">
                        <i class="bi bi-list text-white"></i>
                    </button>
                    <span class="fw-bold text-white">Admin Dashboard</span>
                </div>
                
                <div class="d-flex align-items-center">
                    <div class="input-group me-3" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" placeholder="Search...">
                    </div>
                    
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name=admin&background=random" alt="User" width="32" height="32" class="rounded-circle me-2">
                            <span class="d-none d-sm-inline text-white"><?php echo htmlspecialchars( $user['name'] ?? 'admin') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                            <li><a class="dropdown-item" href="<?php echo file_exists('profile.php') ? 'profile.php' : '../profile.php'; ?>"><i class="bi bi-person me-2"></i> Profile</a></li>
                            <!-- <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li> -->
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo file_exists('../includes/logout.php') ? '../includes/logout.php' : '../../includes/logout.php'; ?>"><i class="bi bi-box-arrow-right me-2"></i> Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </header>