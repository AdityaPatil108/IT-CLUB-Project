<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../user/login.php");
    exit;
}

require_once '../user/db_config.php';

// Get current date
$current_date = date('Y-m-d H:i:s');

// Check for pending queries
$pending_queries_sql = "SELECT COUNT(*) as count FROM event_queries WHERE status = 'pending'";
$pending_queries_result = $conn->query($pending_queries_sql);
$pending_queries_row = $pending_queries_result->fetch_assoc();
$pending_queries_count = $pending_queries_row['count'];

// Get upcoming events
$upcoming_sql = "SELECT * FROM events WHERE event_date >= ? ORDER BY event_date ASC";
$upcoming_stmt = $conn->prepare($upcoming_sql);
$upcoming_stmt->bind_param("s", $current_date);
$upcoming_stmt->execute();
$upcoming_result = $upcoming_stmt->get_result();

// Get past events with average ratings
$past_sql = "SELECT e.*, 
    COALESCE(AVG(f.rating), 0) as avg_rating,
    COUNT(f.id) as feedback_count
    FROM events e 
    LEFT JOIN event_feedback f ON e.id = f.event_id 
    WHERE e.event_date < ? 
    GROUP BY e.id 
    ORDER BY e.event_date DESC";
$past_stmt = $conn->prepare($past_sql);
if ($past_stmt) {
    $past_stmt->bind_param("s", $current_date);
    $past_stmt->execute();
    $past_result = $past_stmt->get_result();
} else {
    // Fallback if LEFT JOIN fails
    $past_sql = "SELECT * FROM events WHERE event_date < ? ORDER BY event_date DESC";
    $past_stmt = $conn->prepare($past_sql);
    $past_stmt->bind_param("s", $current_date);
    $past_stmt->execute();
    $past_result = $past_stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - IT Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../user/style.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <!-- Mobile Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <div class="hamburger-lines">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </button>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Include Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <h1 class="h2">Dashboard</h1>
                    <div>
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo ucfirst($_SESSION['role']); ?>)</span>
                    </div>
                </div>

                <div class="admin-content">
                    <?php if(isset($_GET['feedback_sent'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            Successfully sent feedback emails to <strong><?php echo $_GET['feedback_sent']; ?></strong> participants!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if(isset($_GET['error']) && $_GET['error'] == 'feedback_send_failed'): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Failed to send feedback emails. Please try again.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if($pending_queries_count > 0): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-bell me-2"></i>
                            You have <strong><?php echo $pending_queries_count; ?></strong> pending <?php echo $pending_queries_count == 1 ? 'query' : 'queries'; ?> that need your response.
                            <a href="view_queries.php" class="alert-link ms-2">View now</a>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Upcoming Events</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($upcoming_result->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>Date</th>
                                                        <th>Location</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while($row = $upcoming_result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                            <td><?php echo date('M d, Y', strtotime($row['event_date'])); ?></td>
                                                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                                                            <td>
                                                                <a href="edit_event.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                                <a href="delete_event.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p>No upcoming events found. <a href="add_event.php">Add a new event</a>.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h5>Past Events</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($past_result->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>Date</th>
                                                        <th>Location</th>
                                                        <th>Rating</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while($row = $past_result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                            <td><?php echo date('M d, Y', strtotime($row['event_date'])); ?></td>
                                                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                                                            <td>
                                                                <?php if (isset($row['feedback_count']) && $row['feedback_count'] > 0): ?>
                                                                    <span class="badge bg-warning text-dark">
                                                                        <i class="fas fa-star"></i> <?php echo number_format($row['avg_rating'], 1); ?>
                                                                        (<?php echo $row['feedback_count']; ?>)
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">No feedback</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <a href="view_feedback.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Feedback</a>
                                                                <a href="generate_whatsapp_links.php?event_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" target="_blank">
                                                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                                                </a>
                                                                <?php if (isset($row['feedback_sent']) && !$row['feedback_sent']): ?>
                                                                    <a href="send_feedback_emails.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Send Emails</a>
                                                                <?php endif; ?>
                                                                <a href="edit_event.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                                <a href="delete_event.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p>No past events found.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if($pending_queries_count > 0): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h5><i class="fas fa-bell me-2"></i>Pending Queries</h5>
                                </div>
                                <div class="card-body">
                                    <p>You have <strong><?php echo $pending_queries_count; ?></strong> pending <?php echo $pending_queries_count == 1 ? 'query' : 'queries'; ?> that require your attention.</p>
                                    <a href="view_queries.php" class="btn btn-warning">
                                        <i class="fas fa-reply me-2"></i>Respond to Queries
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_script.js"></script>
    
    <!-- Toast Notification for Query Response -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="responseToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <small>Just now</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <i class="fas fa-check-circle text-success me-2"></i>
                Response submitted successfully!
            </div>
        </div>
    </div>
    
    <script>
        // Auto refresh dashboard every 30 seconds to check for new queries
        setInterval(function() {
            // Only refresh if no modals are open and user is not interacting
            if (!document.querySelector('.modal.show') && document.visibilityState === 'visible') {
                location.reload();
            }
        }, 30000); // 30 seconds
        
        // Auto-hide success/error alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('alert-success') || alert.classList.contains('alert-danger')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
        
        // Show toast notification if response was submitted
        document.addEventListener('DOMContentLoaded', function() {
            <?php if(isset($_GET['success']) && $_GET['success'] == 'response'): ?>
                var responseToast = new bootstrap.Toast(document.getElementById('responseToast'));
                responseToast.show();
                
                // Redirect to remove the success parameter after 3 seconds
                setTimeout(function() {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }, 3000);
            <?php endif; ?>
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>