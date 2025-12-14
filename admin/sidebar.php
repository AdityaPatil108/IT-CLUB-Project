<?php
// Count pending queries for badge
$pending_queries_sql = "SELECT COUNT(*) as count FROM event_queries WHERE status = 'pending'";
$pending_queries_result = $conn->query($pending_queries_sql);
$pending_queries_row = $pending_queries_result->fetch_assoc();
$pending_queries_count = $pending_queries_row['count'];

// Get current page for active class
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<div class="col-md-3 col-lg-2 admin-sidebar p-0">
    <div class="d-flex flex-column p-3">
        <a href="dashboard.php" class="d-flex align-items-center mb-3 text-white text-decoration-none">
            <span class="fs-4"><br><br>IT CLUB Admin</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="add_event.php" class="nav-link <?php echo ($current_page == 'add_event.php') ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-plus me-2"></i>
                    Add Event
                </a>
            </li>
            <li>
                <a href="manage_events.php" class="nav-link <?php echo ($current_page == 'manage_events.php' || $current_page == 'edit_event.php') ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Manage Events
                </a>
            </li>
            <li>
                <a href="view_registrations.php" class="nav-link <?php echo ($current_page == 'view_registrations.php') ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-list me-2"></i>
                    View Registrations
                </a>
            </li>
            <li>
                <a href="view_queries.php" class="nav-link <?php echo ($current_page == 'view_queries.php') ? 'active' : ''; ?>">
                    <i class="fas fa-question-circle me-2"></i>
                    View Queries
                    <?php if($pending_queries_count > 0): ?>
                    <span class="badge bg-danger ms-2"><?php echo $pending_queries_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="manage_winners.php" class="nav-link <?php echo ($current_page == 'manage_winners.php') ? 'active' : ''; ?>">
                    <i class="fas fa-trophy me-2"></i>
                    Manage Winners
                </a>
            </li>
            <li>
                <a href="manage_gallery.php" class="nav-link <?php echo ($current_page == 'manage_gallery.php') ? 'active' : ''; ?>">
                    <i class="fas fa-images me-2"></i>
                    Event Gallery
                </a>
            </li>
            <li>
                <a href="settings.php" class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                    <i class="fas fa-cog me-2"></i>
                    Site Settings
                </a>
            </li>

            <li>
                <a href="manage_members.php" class="nav-link <?php echo ($current_page == 'manage_members.php' || $current_page == 'add_member.php' || $current_page == 'edit_member.php') ? 'active' : ''; ?>">
                    <i class="fas fa-users me-2"></i>
                    Manage Members
                </a>
            </li>
            <?php if($_SESSION['role'] === 'member'): ?>
            <li>
                <a href="../user/login.php?role=faculty" class="nav-link">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    Manage Faculty
                </a>
            </li>
            <li>
                <a href="../user/login.php?role=faculty" class="nav-link">
                    <i class="fas fa-user-plus me-2"></i>
                    Add Faculty
                </a>
            </li>
            <?php endif; ?>
            <?php if($_SESSION['role'] === 'faculty'): ?>
            <li>
                <a href="manage_faculty.php" class="nav-link <?php echo ($current_page == 'manage_faculty.php' || $current_page == 'edit_faculty.php' || $current_page == 'reset_faculty_password.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    Manage Faculty
                </a>
            </li>
            <li>
                <a href="add_faculty.php" class="nav-link <?php echo ($current_page == 'add_faculty.php') ? 'active' : ''; ?>">
                    <i class="fas fa-user-plus me-2"></i>
                    Add Faculty
                </a>
            </li>
            <?php endif; ?>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle me-2 fs-5"></i>
                <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
            </ul>
        </div>
    </div>
</div>