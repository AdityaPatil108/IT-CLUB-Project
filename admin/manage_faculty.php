<?php
session_start();

// Check if user is logged in and is faculty
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'faculty') {
    header("location: ../user/login.php");
    exit;
}

require_once '../user/db_config.php';

// Process faculty deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Don't allow deletion of the current user
    if ($id != $_SESSION['id']) {
        $sql = "DELETE FROM users WHERE id = ? AND role = 'faculty'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $success_message = "Faculty deleted successfully!";
        } else {
            $error_message = "Error deleting faculty.";
        }
        
        $stmt->close();
    } else {
        $error_message = "You cannot delete your own account!";
    }
}

// Get all faculty
$sql = "SELECT * FROM users WHERE role = 'faculty' ORDER BY username";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty - IT Club</title>
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
                    <h1 class="h2">Manage Faculty</h1>
                    <div>
                        <a href="add_faculty.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-user-plus me-1"></i> Add New Faculty
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
                            <h5 class="mb-0">All Faculty</h5>
                            <div class="input-group" style="width: 300px;">
                                <input type="text" id="facultySearch" class="form-control" placeholder="Search faculty...">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $row['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                                    <td>
                                                        <a href="edit_faculty.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <?php if($row['id'] != $_SESSION['id']): ?>
                                                        <a href="manage_faculty.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this faculty member?')">
                                                            <i class="fas fa-trash-alt"></i> Delete
                                                        </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center p-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p>No faculty found.</p>
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
        document.getElementById('facultySearch').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const username = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();
                
                if (username.includes(searchValue) || email.includes(searchValue)) {
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