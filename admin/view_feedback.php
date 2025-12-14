<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../user/login.php");
    exit;
}

require_once '../user/db_config.php';

$event_id = $_GET['id'] ?? 0;

// Get event details
$event_sql = "SELECT * FROM events WHERE id = ?";
$event_stmt = $conn->prepare($event_sql);
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();

if ($event_result->num_rows === 0) {
    header("location: dashboard.php");
    exit;
}

$event = $event_result->fetch_assoc();

// Get feedback statistics
$stats_sql = "SELECT AVG(rating) as avg_rating, COUNT(id) as total_feedback FROM event_feedback WHERE event_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
if ($stats_stmt) {
    $stats_stmt->bind_param("i", $event_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats = $stats_result->fetch_assoc();
} else {
    $stats = ['avg_rating' => 0, 'total_feedback' => 0];
}

// Get all individual feedback
$all_feedback_sql = "SELECT * FROM event_feedback WHERE event_id = ? ORDER BY created_at DESC";
$all_feedback_stmt = $conn->prepare($all_feedback_sql);
$all_feedback_stmt->bind_param("i", $event_id);
$all_feedback_stmt->execute();
$all_feedback_result = $all_feedback_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Feedback - <?php echo htmlspecialchars($event['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../user/style.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        .bg-gradient-primary { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); }
        .card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .progress { border-radius: 10px; }
        .progress-bar { border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <h1 class="h2">Event Feedback</h1>
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>

                <div class="admin-content">
                    <!-- Event Header -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-gradient-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i><?php echo htmlspecialchars($event['title']); ?></h4>
                            <small class="opacity-75"><i class="fas fa-clock me-1"></i><?php echo date('M d, Y', strtotime($event['event_date'])); ?></small>
                        </div>
                    </div>

                    <?php if ($stats && $stats['total_feedback'] > 0): ?>
                        <!-- Statistics Dashboard -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="display-4 text-warning mb-2">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <h2 class="text-warning mb-1"><?php echo number_format($stats['avg_rating'], 1); ?></h2>
                                        <p class="text-muted mb-0">Average Rating</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="display-4 text-primary mb-2">
                                            <i class="fas fa-comments"></i>
                                        </div>
                                        <h2 class="text-primary mb-1"><?php echo (int)$stats['total_feedback']; ?></h2>
                                        <p class="text-muted mb-0">Total Reviews</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="display-4 text-success mb-2">
                                            <i class="fas fa-thumbs-up"></i>
                                        </div>
                                        <h2 class="text-success mb-1"><?php echo round(($stats['avg_rating']/5)*100); ?>%</h2>
                                        <p class="text-muted mb-0">Satisfaction</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="display-4 text-info mb-2">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <h2 class="text-info mb-1"><?php echo $stats['avg_rating'] >= 4 ? 'Excellent' : ($stats['avg_rating'] >= 3 ? 'Good' : 'Fair'); ?></h2>
                                        <p class="text-muted mb-0">Performance</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rating Distribution -->
                        <?php 
                        $rating_dist_sql = "SELECT rating, COUNT(*) as count FROM event_feedback WHERE event_id = ? GROUP BY rating ORDER BY rating DESC";
                        $rating_dist_stmt = $conn->prepare($rating_dist_sql);
                        if ($rating_dist_stmt) {
                            $rating_dist_stmt->bind_param("i", $event_id);
                            $rating_dist_stmt->execute();
                            $rating_dist_result = $rating_dist_stmt->get_result();
                            $rating_distribution = [];
                            while($row = $rating_dist_result->fetch_assoc()) {
                                $rating_distribution[$row['rating']] = $row['count'];
                            }
                        }
                        ?>
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Rating Distribution</h5>
                            </div>
                            <div class="card-body">
                                <?php for($i = 5; $i >= 1; $i--): ?>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-3" style="width: 60px;">
                                            <span class="fw-bold"><?php echo $i; ?></span>
                                            <i class="fas fa-star text-warning ms-1"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="progress" style="height: 20px;">
                                                <?php 
                                                $count = isset($rating_distribution[$i]) ? $rating_distribution[$i] : 0;
                                                $percentage = $stats['total_feedback'] > 0 ? ($count / $stats['total_feedback']) * 100 : 0;
                                                ?>
                                                <div class="progress-bar bg-warning" style="width: <?php echo $percentage; ?>%"></div>
                                            </div>
                                        </div>
                                        <div class="ms-3" style="width: 40px;">
                                            <span class="text-muted"><?php echo $count; ?></span>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-inbox fa-4x text-muted mb-4"></i>
                                <h4 class="text-muted">No Feedback Yet</h4>
                                <p class="text-muted">No participants have submitted feedback for this event.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($all_feedback_result->num_rows > 0): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5>Individual Feedback</h5>
                            </div>
                            <div class="card-body">
                                <?php while($feedback = $all_feedback_result->fetch_assoc()): ?>
                                    <div class="border-bottom pb-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6><?php echo htmlspecialchars($feedback['participant_name']); ?></h6>
                                                <div class="text-warning mb-2">
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star<?php echo $i <= $feedback['rating'] ? '' : '-o'; ?>"></i>
                                                    <?php endfor; ?>
                                                    <span class="ms-2"><?php echo $feedback['rating']; ?>/5</span>
                                                </div>
                                            </div>
                                            <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($feedback['created_at'])); ?></small>
                                        </div>
                                        <?php if (!empty($feedback['suggestions'])): ?>
                                            <div class="mt-2">
                                                <strong>Suggestions:</strong>
                                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($feedback['suggestions'])); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_script.js"></script>
</body>
</html>