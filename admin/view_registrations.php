<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../user/login.php");
    exit;
}

require_once '../user/db_config.php';

// Get event ID if provided
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : null;

// Get all events for dropdown
$events_sql = "SELECT id, title FROM events ORDER BY event_date DESC";
$events_result = $conn->query($events_sql);

// Get registrations
if ($event_id) {
    // Get registrations for specific event
    $sql = "SELECT r.*, e.title as event_title 
            FROM event_registrations r 
            LEFT JOIN events e ON r.event_id = e.id 
            WHERE r.event_id = ? 
            ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // Get all registrations
    $sql = "SELECT r.*, e.title as event_title 
            FROM event_registrations r 
            LEFT JOIN events e ON r.event_id = e.id 
            ORDER BY r.created_at DESC";
    $result = $conn->query($sql);
}

// Success message will be handled by AJAX
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Registrations - IT Club</title>
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
                    <h1 class="h2">Event Registrations</h1>
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
                            <h5 class="mb-0">Filter Registrations</h5>
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
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Event</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Department</th>
                                                <th>Student</th>
                                                <th>Payment</th>
                                                <th>Registered On</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $row['id']; ?></td>
                                                    <td><?php echo isset($row['event_title']) ? htmlspecialchars($row['event_title']) : '<span class="text-muted">Event deleted</span>'; ?></td>
                                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                                    <td><?php echo $row['is_student'] ? 'Yes' : 'No'; ?></td>
                                                    <td>
                                                        <?php if (!empty($row['payment_screenshot'])): ?>
                                                            <button type="button" class="btn btn-sm btn-success" onclick="showPaymentImage('../<?php echo htmlspecialchars($row['payment_screenshot']); ?>')" title="View Payment Screenshot">
                                                                <i class="fas fa-image"></i> View
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                                    <td>
                                                        <button type="button" 
                                                           class="btn btn-sm btn-danger delete-registration" 
                                                           data-id="<?php echo $row['id']; ?>">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center p-4">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <p>No registrations found.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Export Button -->
                    <?php if ($result->num_rows > 0): ?>
                    <div class="text-end mb-4">
                        <button class="btn btn-success" onclick="exportToCSV()">
                            <i class="fas fa-file-csv me-2"></i> Export to CSV
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Image Modal -->
    <div class="modal fade" id="paymentImageModal" tabindex="-1" aria-labelledby="paymentImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentImageModalLabel">Payment Screenshot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="paymentImage" src="" class="img-fluid" alt="Payment Screenshot" style="max-height: 70vh;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_script.js"></script>
    <script>
        function showPaymentImage(imageSrc) {
            document.getElementById('paymentImage').src = imageSrc;
            var modal = new bootstrap.Modal(document.getElementById('paymentImageModal'));
            modal.show();
        }
    </script>
    <script>
        // Function to export table data to CSV
        function exportToCSV() {
            const table = document.querySelector('table');
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = [], cols = rows[i].querySelectorAll('td, th');
                
                for (let j = 0; j < cols.length; j++) {
                    // Remove the last column (Actions)
                    if (j < cols.length - 1 || i === 0) {
                        let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
                        data = data.replace(/"/g, '""');
                        row.push('"' + data + '"');
                    }
                }
                csv.push(row.join(','));
            }
            
            const csvString = csv.join('\n');
            const filename = 'event_registrations_<?php echo date("Y-m-d"); ?>.csv';
            const link = document.createElement('a');
            link.style.display = 'none';
            link.setAttribute('target', '_blank');
            link.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvString));
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // AJAX deletion for registrations
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners to all delete buttons
            document.querySelectorAll('.delete-registration').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this registration?')) {
                        const id = this.getAttribute('data-id');
                        const row = this.closest('tr');
                        
                        // Create form data
                        const formData = new FormData();
                        formData.append('id', id);
                        
                        // Send AJAX request
                        fetch('delete_registration.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove the row from the table
                                row.remove();
                                
                                // Show success message
                                const alertDiv = document.createElement('div');
                                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                                alertDiv.innerHTML = `
                                    ${data.message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                `;
                                
                                // Insert the alert at the top of the admin-content div
                                const adminContent = document.querySelector('.admin-content');
                                adminContent.insertBefore(alertDiv, adminContent.firstChild);
                                
                                // Check if table is now empty
                                const tbody = document.querySelector('tbody');
                                if (tbody.children.length === 0) {
                                    const tableResponsive = document.querySelector('.table-responsive');
                                    tableResponsive.innerHTML = `
                                        <div class="text-center p-4">
                                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                            <p>No registrations found.</p>
                                        </div>
                                    `;
                                    
                                    // Hide export button
                                    const exportButton = document.querySelector('.btn-success');
                                    if (exportButton) exportButton.style.display = 'none';
                                }
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the registration.');
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>