<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../user/login.php");
    exit;
}

require_once '../config/database.php';
require_once '../includes/security.php';
require_once '../includes/gmail_smtp.php';

$conn = getDBConnection();

// Get event ID if provided
$event_id = null;
if (isset($_GET['event_id'])) {
    $event_id = filter_var($_GET['event_id'], FILTER_VALIDATE_INT);
    if ($event_id === false) {
        $event_id = null;
    }
}

// Get all events for dropdown
$events_sql = "SELECT id, title FROM events ORDER BY event_date DESC";
$events_result = $conn->query($events_sql);

// Count pending queries
$pending_queries_sql = "SELECT COUNT(*) as count FROM event_queries WHERE status = 'pending'";
$pending_queries_result = $conn->query($pending_queries_sql);
$pending_queries_row = $pending_queries_result->fetch_assoc();
$pending_queries_count = $pending_queries_row['count'];

// Get queries with prepared statements
if ($event_id) {
    $sql = "SELECT q.*, e.title as event_title 
            FROM event_queries q 
            JOIN events e ON q.event_id = e.id 
            WHERE q.event_id = ? 
            ORDER BY q.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT q.*, e.title as event_title 
            FROM event_queries q 
            JOIN events e ON q.event_id = e.id 
            ORDER BY q.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Process response submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['query_id']) && isset($_POST['response'])) {
    $query_id = validateInteger($_POST['query_id']);
    $response = sanitizeInput($_POST['response']);
    
    if ($query_id === false || empty($response)) {
        $error_message = "Invalid input data.";
        goto skip_response;
    }
    
    // Get query details for email
    $query_details_sql = "SELECT q.*, e.title as event_title FROM event_queries q JOIN events e ON q.event_id = e.id WHERE q.id = ?";
    $query_details_stmt = $conn->prepare($query_details_sql);
    $query_details_stmt->bind_param("i", $query_id);
    $query_details_stmt->execute();
    $query_details_result = $query_details_stmt->get_result();
    $query_details = $query_details_result->fetch_assoc();
    
    $update_sql = "UPDATE event_queries SET response = ?, status = 'answered' WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $response, $query_id);
    
    if ($update_stmt->execute()) {
        // Send email to student
        if ($query_details) {
            $emailSent = sendQueryResponse(
                $query_details['email'],
                $query_details['name'],
                $query_details['event_title'],
                $query_details['query'],
                $response
            );
            
            if (!$emailSent) {
                error_log("Failed to send email response to student: " . $query_details['email']);
            }
        }
        
        // Redirect to prevent form resubmission
        $redirect_url = "view_queries.php";
        if ($event_id) {
            $redirect_url .= "?event_id=$event_id&success=response";
        } else {
            $redirect_url .= "?success=response";
        }
        header("Location: $redirect_url");
        exit;
    } else {
        $error_message = "Error submitting response.";
    }
    
    $query_details_stmt->close();
    
    $update_stmt->close();
}

skip_response:

// Process deletion if requested
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id === false) {
        $error_message = "Invalid query ID.";
        goto skip_deletion;
    }
    $delete_sql = "DELETE FROM event_queries WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);
    
    if ($delete_stmt->execute()) {
        $success_message = "Query deleted successfully!";
        // Redirect to remove the action from URL
        header("Location: view_queries.php" . ($event_id ? "?event_id=$event_id" : "") . "&success=delete");
        exit;
    } else {
        $error_message = "Error deleting query.";
    }
    
    $delete_stmt->close();
}

skip_deletion:

// Check for success message in URL
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'delete') {
        $success_message = "Query deleted successfully!";
    } elseif ($_GET['success'] == 'response') {
        $success_message = "Response submitted successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Queries - IT Club</title>
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
                    <h1 class="h2">Event Queries</h1>
                    <div>
                        <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>

                <div class="admin-content">
                    <?php if(isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Filter Queries</h5>
                            <form class="d-flex" method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <select name="event_id" class="form-select me-2" onchange="this.form.submit()">
                                    <option value="">All Events</option>
                                    <?php while($event = $events_result->fetch_assoc()): ?>
                                        <option value="<?php echo $event['id']; ?>" <?php echo ($event_id == $event['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($event['title']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </form>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($result->num_rows > 0): ?>
                                <div class="accordion" id="queriesAccordion">
                                    <?php $counter = 0; while($row = $result->fetch_assoc()): $counter++; ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading<?php echo $counter; ?>">
                                                <button class="accordion-button <?php echo ($counter > 1) ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $counter; ?>" aria-expanded="<?php echo ($counter == 1) ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $counter; ?>">
                                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                                        <div>
                                                            <span class="badge bg-<?php echo ($row['status'] == 'pending') ? 'warning' : 'success'; ?> me-2">
                                                                <?php echo ucfirst($row['status']); ?>
                                                            </span>
                                                            <strong><?php echo htmlspecialchars($row['name']); ?></strong> - 
                                                            <span class="text-primary"><?php echo htmlspecialchars($row['event_title']); ?></span>
                                                        </div>
                                                        <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></small>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="collapse<?php echo $counter; ?>" class="accordion-collapse collapse <?php echo ($counter == 1) ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $counter; ?>" data-bs-parent="#queriesAccordion">
                                                <div class="accordion-body">
                                                    <div class="mb-3">
                                                        <h6>Query:</h6>
                                                        <p><?php echo nl2br(htmlspecialchars($row['query'])); ?></p>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <h6>Contact Information:</h6>
                                                        <p>
                                                            <strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?><br>
                                                        </p>
                                                    </div>
                                                    
                                                    <?php if($row['status'] == 'answered'): ?>
                                                        <div class="mb-3">
                                                            <h6>Response:</h6>
                                                            <p><?php echo nl2br(htmlspecialchars($row['response'])); ?></p>
                                                        </div>
                                                    <?php else: ?>
                                                        <form method="POST">
                                                            <input type="hidden" name="query_id" value="<?php echo $row['id']; ?>">
                                                            <div class="mb-3">
                                                                <label for="response<?php echo $counter; ?>" class="form-label">Your Response:</label>
                                                                <textarea class="form-control" id="response<?php echo $counter; ?>" name="response" rows="3" required></textarea>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary">
                                                                Submit Response
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <div class="mt-3 text-end">
                                                        <button class="btn btn-sm btn-danger" onclick="deleteQuery(<?php echo $row['id']; ?>, this)">
                                                            <i class="fas fa-trash-alt"></i> Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center p-4">
                                    <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                    <p>No queries found.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_script.js"></script>
    
    <!-- Success Modal for Response Submission -->
    <div class="modal fade" id="responseSuccessModal" tabindex="-1" aria-labelledby="responseSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="responseSuccessModalLabel"><i class="fas fa-check-circle me-2"></i>Success</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                    <h4>Response Submitted Successfully!</h4>
                    <p>Your response has been saved.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Show success modal if response was submitted
        document.addEventListener('DOMContentLoaded', function() {
            <?php if(isset($_GET['success']) && $_GET['success'] == 'response'): ?>
                var successModal = new bootstrap.Modal(document.getElementById('responseSuccessModal'));
                successModal.show();
                
                // Auto close after 3 seconds and clean URL
                setTimeout(function() {
                    successModal.hide();
                    window.history.replaceState({}, document.title, window.location.pathname + (<?php echo $event_id ? '"?event_id=' . $event_id . '"' : '""'; ?>));
                }, 3000);
            <?php endif; ?>
            
            // Auto hide alerts after 3 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 3000);
            });
        });
        
        // Delete query function
        function deleteQuery(queryId, button) {
            if (confirm('Are you sure you want to delete this query?')) {
                fetch('delete_query.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'id=' + queryId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the accordion item
                        button.closest('.accordion-item').remove();
                        
                        // Show success message
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show';
                        alert.innerHTML = 'Query deleted successfully! <button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                        document.querySelector('.admin-content').insertBefore(alert, document.querySelector('.card'));
                        
                        // Auto hide after 3 seconds
                        setTimeout(function() {
                            alert.style.display = 'none';
                        }, 3000);
                    } else {
                        alert('Error deleting query: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error deleting query');
                });
            }
        }
        

    </script>
</body>
</html>
<?php $conn->close(); ?>