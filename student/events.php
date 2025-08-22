<?php
include_once '../includes/header.php';
include_once '../includes/db_connect.php';

$user = $_SESSION['user'] ?? null;
$deptId = $user['dept_id'] ?? $user['department_id'] ?? null;

// Fetch upcoming events
if ($user) {
    $stmt = db_query("SELECT * FROM events ORDER BY start_datetime ASC");
} else {
    $stmt = db_query("SELECT * FROM events ORDER BY start_datetime ASC");
}
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events | UniPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/student.css">
    <style>
        .event-card {
            transition: transform 0.2s;
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .no-results {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="dashboard-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="welcome-text">Department Events</h1>
                            <p class="welcome-subtext">Latest events in your department</p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="input-group">
                                <input type="text" id="eventSearch" class="form-control" placeholder="Search events...">
                                <button class="btn btn-primary" type="button" id="searchButton"><i class="bi bi-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4" id="eventsContainer">
                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $ev): ?>
                            <div class="col-md-6 col-lg-4 event-item" 
                                 data-title="<?php echo htmlspecialchars(strtolower($ev['title'])); ?>"
                                 data-description="<?php echo htmlspecialchars(strtolower($ev['description'])); ?>"
                                 data-venue="<?php echo htmlspecialchars(strtolower($ev['venue'])); ?>"
                                 data-date="<?php echo htmlspecialchars(strtolower($ev['start_datetime'])); ?>">
                                <div class="card event-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($ev['title']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars(substr($ev['description'], 0, 150)); ?>...</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i><?php echo date('M j, Y g:i a', strtotime($ev['start_datetime'])); ?></small>
                                            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($ev['venue']); ?></small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0">
                                        <a href="event_details.php?event_id=<?php echo (int)$ev['event_id']; ?>" class="btn btn-primary btn-sm">Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="card p-3">No upcoming events found.</div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- No results message (hidden by default) -->
                <div id="noResults" class="col-12 no-results">
                    <div class="card p-3">No events match your search.</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Search functionality
            $('#eventSearch, #searchButton').on('input keyup click', function() {
                const searchTerm = $('#eventSearch').val().toLowerCase();
                let hasResults = false;
                
                $('.event-item').each(function() {
                    const title = $(this).data('title');
                    const description = $(this).data('description');
                    const venue = $(this).data('venue');
                    const date = $(this).data('date');
                    
                    if (title.includes(searchTerm) || 
                        description.includes(searchTerm) || 
                        venue.includes(searchTerm) || 
                        date.includes(searchTerm)) {
                        $(this).show();
                        hasResults = true;
                    } else {
                        $(this).hide();
                    }
                });
                
                // Show/hide no results message
                if (hasResults || searchTerm === '') {
                    $('#noResults').hide();
                } else {
                    $('#noResults').show();
                }
            });
            
            // Trigger search on Enter key
            $('#eventSearch').keypress(function(e) {
                if (e.which === 13) {
                    $('#searchButton').click();
                }
            });
        });
    </script>
</body>
</html>