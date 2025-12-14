<?php
session_start();
require_once '../includes/env_loader.php';
require_once '../config/database.php';
require_once '../includes/security.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../user/login.php');
    exit();
}

$conn = getDBConnection();
$winners_file = '../data/winners.json';
$winners = json_decode(file_get_contents($winners_file), true) ?: [];

// Get events for dropdown
$events_query = "SELECT id, title FROM events ORDER BY event_date DESC";
$events_result = $conn->query($events_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_winner'])) {
        $new_winner = [
            'name' => $_POST['name'],
            'event' => $_POST['event'],
            'position' => $_POST['position'],
            'prize' => $_POST['prize'] ?: ''
        ];
        $winners[] = $new_winner;
        file_put_contents($winners_file, json_encode($winners, JSON_PRETTY_PRINT));
        $success = "Winner added successfully!";
    } elseif (isset($_POST['delete_winner'])) {
        $index = $_POST['winner_index'];
        unset($winners[$index]);
        $winners = array_values($winners);
        file_put_contents($winners_file, json_encode($winners, JSON_PRETTY_PRINT));
        $success = "Winner deleted successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Winners - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../user/style.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <button class="sidebar-toggle" id="sidebarToggle">
        <div class="hamburger-lines">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </button>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <h1 class="h2"><i class="fas fa-trophy me-2"></i>Manage Winners</h1>
                    <div>
                        <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> (<?= ucfirst($_SESSION['role']) ?>)</span>
                    </div>
                </div>

                <div class="admin-content">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-plus me-2"></i>Add New Winner</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Winner Name:</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Event:</label>
                                        <select name="event" class="form-select" required>
                                            <option value="">Select Event</option>
                                            <?php while ($event = $events_result->fetch_assoc()): ?>
                                                <option value="<?= htmlspecialchars($event['title']) ?>"><?= htmlspecialchars($event['title']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Position:</label>
                                        <select name="position" class="form-select" required>
                                            <option value="1st">🥇 1st Place</option>
                                            <option value="2nd">🥈 2nd Place</option>
                                            <option value="3rd">🥉 3rd Place</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Prize Amount (Optional):</label>
                                        <input type="number" name="prize" class="form-control" placeholder="Enter amount">
                                    </div>
                                </div>
                                <button type="submit" name="add_winner" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add Winner
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-list me-2"></i>Current Winners</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($winners)): ?>
                                <p class="text-muted">No winners added yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Winner</th>
                                                <th>Event</th>
                                                <th>Position</th>
                                                <th>Prize</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($winners as $index => $winner): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($winner['name']) ?></strong></td>
                                                    <td><?= htmlspecialchars($winner['event']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $winner['position'] === '1st' ? 'warning' : ($winner['position'] === '2nd' ? 'secondary' : 'dark') ?>">
                                                            <?= $winner['position'] === '1st' ? '🥇' : ($winner['position'] === '2nd' ? '🥈' : '🥉') ?>
                                                            <?= ucfirst($winner['position']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($winner['prize'])): ?>
                                                            ₹<?= $winner['prize'] ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="winner_index" value="<?= $index ?>">
                                                            <button type="submit" name="delete_winner" class="btn btn-sm btn-danger" 
                                                                    onclick="return confirm('Delete this winner?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="../user/winners.php" target="_blank" class="btn btn-success">
                            <i class="fas fa-external-link-alt me-2"></i>View Winners Page
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_script.js"></script>
</body>
</html>