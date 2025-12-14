<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../user/login.php");
    exit;
}

require_once '../user/db_config.php';

// Get all events
$sql = "SELECT * FROM events ORDER BY event_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - IT Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../user/style.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        .event-table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .event-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }
        .event-image {
            width: 80px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
        .action-btns .btn {
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .action-btns .btn {
                display: block;
                width: 100%;
                margin-bottom: 5px;
            }
            .event-image {
                width: 60px;
                height: 40px;
            }
        }
        
        @media (max-width: 576px) {
            .card-header {
                flex-direction: column;
                align-items: stretch !important;
            }
            .card-header .input-group {
                width: 100% !important;
                margin-top: 10px;
            }
        }
    </style>
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
                    <h1 class="h2">Manage Events</h1>
                    <div>
                        <a href="add_event.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-plus me-1"></i> Add New Event
                        </a>
                    </div>
                </div>

                <div class="admin-content">
                    <?php if(isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                            if($_GET['success'] == 'delete') {
                                echo "Event deleted successfully!";
                            } elseif($_GET['success'] == 'update') {
                                echo "Event updated successfully!";
                            }
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">All Events</h5>
                            <div class="input-group" style="width: 300px;">
                                <input type="text" id="eventSearch" class="form-control" placeholder="Search events...">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover event-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Title</th>
                                                <th>Date</th>
                                                <th>Location</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <img src="../<?php echo htmlspecialchars($row['image']); ?>" class="event-image" alt="<?php echo htmlspecialchars($row['title']); ?>">
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($row['event_date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                                                    <td class="action-btns">
                                                        <a href="edit_event.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <a href="delete_event.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this event?')">
                                                            <i class="fas fa-trash-alt"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center p-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <p>No events found. <a href="add_event.php">Add your first event</a>.</p>
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
    <script>
        // Simple search functionality
        document.getElementById('eventSearch').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.event-table tbody tr');
            
            tableRows.forEach(row => {
                const title = row.cells[1].textContent.toLowerCase();
                const location = row.cells[3].textContent.toLowerCase();
                
                if (title.includes(searchValue) || location.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>