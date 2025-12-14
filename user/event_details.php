<?php
require_once 'db_config.php';
require_once '../includes/settings_helper.php';

// Get settings
$site_description = getSetting('site_description', 'Fostering tech innovation and collaboration among students.');
$footer_text = getSetting('footer_text', '© 2025 IT Club, Computer Science Dept, Sangameshwar College. All rights reserved.');
$instagram_url = getSetting('instagram_url', '#');

// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: events.php");
    exit;
}

$id = $_GET['id'];

// Get event details
$sql = "SELECT * FROM events WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// If event doesn't exist, redirect to events page
if ($result->num_rows == 0) {
    header("location: events.php");
    exit;
}

$event = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['title']); ?> - IT Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Animated Background -->
    <ul class="bg-animation">
        <li></li><li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li><li></li>
    </ul>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="it-club-logo.png" alt="IT Club Logo" height="50">
                
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <div class="hamburger-lines">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link active" href="events.php">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="winners.php">Winners</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Login
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="login.php?role=faculty">Faculty Login</a></li>
                            <li><a class="dropdown-item" href="login.php?role=member">Member Login</a></li>
                        </ul>
                    </li>
                </ul>
                
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header text-center">
        <div class="container">
            <h1 style="color:white;"><?php echo htmlspecialchars($event['title']); ?></h1>
            <p class="lead" style="color:white;">
                <i class="far fa-calendar-alt me-2"></i><?php echo date('F d, Y', strtotime($event['event_date'])); ?> at <?php echo date('g:i A', strtotime($event['event_date'])); ?>
            </p>
        </div>
    </section>

    <!-- Event Details Section -->
    <section class="event-details py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8" data-aos="fade-right">
                    <div class="text-center mb-4">
                        <img src="../<?php echo htmlspecialchars($event['image']); ?>" class="img-fluid rounded shadow event-detail-image" alt="<?php echo htmlspecialchars($event['title']); ?>">
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="text-primary mb-3">About This Event</h3>
                            <p class="text-dark"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        </div>
                    </div>
                    
                    <?php
                    // Load gallery images
                    $gallery_file = '../data/event_gallery.json';
                    $gallery_data = json_decode(file_get_contents($gallery_file), true) ?: [];
                    $event_images = $gallery_data[$event['id']] ?? [];
                    
                    if (!empty($event_images)):
                    ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="text-primary mb-3"><i class="fas fa-images me-2"></i>Event Gallery</h3>
                            <div class="row">
                                <?php foreach ($event_images as $index => $image): ?>
                                    <?php 
                                    $file_ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
                                    $is_video = in_array($file_ext, ['mp4', 'mov', 'avi', 'webm']);
                                    ?>
                                    <div class="col-md-4 col-sm-6 mb-3">
                                        <?php if ($is_video): ?>
                                            <div style="position: relative; height: 200px; background: #000; border-radius: 8px; overflow: hidden; cursor: pointer; box-shadow: 0 4px 8px rgba(0,0,0,0.1);" onclick="showImageModal(<?= $index ?>)">
                                                <video style="width: 100%; height: 100%; object-fit: cover;">
                                                    <source src="../<?= $image ?>" type="video/<?= $file_ext ?>">
                                                </video>
                                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-size: 3em; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">
                                                    <i class="fas fa-play-circle"></i>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <img src="../<?= $image ?>" class="img-fluid rounded shadow" 
                                                 style="height: 200px; width: 100%; object-fit: cover; cursor: pointer;" 
                                                 onclick="showImageModal(<?= $index ?>)" 
                                                 alt="Event Image <?= $index + 1 ?>">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <script>
                                const eventImages = <?= json_encode($event_images) ?>;
                            </script>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-lg-4" data-aos="fade-left">
                    <div class="card sticky-top" style="top: 100px;">
                        <div class="card-body">
                            <h4>Event Details</h4>
                            <ul class="list-unstyled text-dark">
                                <li class="mb-3">
                                    <i class="far fa-calendar-alt me-2 text-primary"></i>
                                    <strong>Date:</strong> <?php echo date('F d, Y', strtotime($event['event_date'])); ?>
                                </li>
                                <li class="mb-3">
                                    <i class="far fa-clock me-2 text-primary"></i>
                                    <strong>Time:</strong> <?php echo date('g:i A', strtotime($event['event_date'])); ?>
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                    <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?>
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-money-bill-wave me-2 text-primary"></i>
                                    <strong>Entry:</strong> 
                                    <?php if (isset($event['is_paid']) && $event['is_paid']): ?>
                                        <span class="text-danger fw-bold">₹<?php echo number_format($event['price'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="text-success fw-bold">Free</span>
                                    <?php endif; ?>
                                </li>
                            </ul>
                            
                            <?php if (isset($event['is_paid']) && $event['is_paid'] && !empty($event['payment_qr'])): ?>
                                <div class="mt-4">
                                    <h6><i class="fas fa-qrcode me-2"></i>Payment QR Code</h6>
                                    <div class="text-center">
                                        <img src="../<?php echo htmlspecialchars($event['payment_qr']); ?>" class="img-fluid qr-code" style="max-width: 200px; border: 2px solid #ddd; border-radius: 10px; cursor: pointer;" alt="Payment QR Code" onclick="showQRModal(this.src)">
                                        <p class="small text-muted mt-2">Scan to pay ₹<?php echo number_format($event['price'], 2); ?></p>
                                        <p class="small text-info">Click to enlarge</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2 mt-4">
                                <?php
                                // Check if event date is in the future
                                $event_date = new DateTime($event['event_date']);
                                $current_date = new DateTime();
                                
                                if ($event_date > $current_date) {
                                    // Show registration button only for future events
                                    echo '<a href="register_for_event.php?event_id=' . $event['id'] . '" class="btn btn-primary">Register Now</a>';
                                } else {
                                    // For past events, show a message
                                    echo '<button class="btn btn-secondary" disabled>Event Completed</button>';
                                }
                                ?>
                                <a href="events.php" class="btn btn-outline-secondary">Back to Events</a>
                                <?php if ($event_date > $current_date): ?>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#queryModal">
                                    <i class="fas fa-question-circle me-2"></i>Raise Query
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Registration info -->
    <div class="d-none">Registration is now handled on a dedicated page.</div>
    
    <!-- Query Modal -->
    <div class="modal fade" id="queryModal" tabindex="-1" aria-labelledby="queryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: white;">
                    <h5 class="modal-title" id="queryModalLabel">Raise Query about <?php echo htmlspecialchars($event['title']); ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="queryForm" action="submit_query.php" method="post">
                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                        <input type="hidden" name="event_title" value="<?php echo htmlspecialchars($event['title']); ?>">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label text-dark">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label text-dark">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="query" class="form-label text-dark">Your Query</label>
                            <textarea class="form-control" id="query" name="query" rows="4" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="queryForm" class="btn btn-primary">Submit Query</button>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">Payment QR Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="qrModalImage" src="" class="img-fluid" alt="Payment QR Code" style="max-height: 70vh;">
                    <p class="mt-3 text-muted">Scan this QR code to make payment</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Image Gallery Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Event Images</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner" id="carouselImages">
                            <!-- Images will be loaded here -->
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                        <div class="carousel-indicators" id="carouselIndicators">
                            <!-- Indicators will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php 
$site_title = getSetting('site_title');
$about_text = getSetting('about_text');
$facebook_url = getSetting('facebook_url');
$twitter_url = getSetting('twitter_url');
$linkedin_url = getSetting('linkedin_url');
include 'footer.php'; 
?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AOS animation library
            AOS.init({
                duration: 600,
                easing: 'ease-out',
                once: true,
                offset: 50,
                disable: false
            });

            // Navbar scroll effect
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Animated background bubbles
            const bgAnimation = document.querySelector('.bg-animation');
            if (bgAnimation) {
                for (let i = 0; i < 15; i++) {
                    const li = document.createElement('li');
                    const size = Math.random() * 80 + 20;
                    const position = Math.random() * 100;
                    const delay = Math.random() * 2;
                    const duration = Math.random() * 15 + 8;

                    li.style.width = `${size}px`;
                    li.style.height = `${size}px`;
                    li.style.left = `${position}%`;
                    li.style.animationDelay = `${delay}s`;
                    li.style.animationDuration = `${duration}s`;
                    li.style.animationTimingFunction = `cubic-bezier(${Math.random()}, ${Math.random()}, ${Math.random()}, ${Math.random()})`;

                    bgAnimation.appendChild(li);
                }
            }
        });
        
        // QR Code modal functionality
        window.showQRModal = function(imageSrc) {
            document.getElementById('qrModalImage').src = imageSrc;
            var modal = new bootstrap.Modal(document.getElementById('qrModal'));
            modal.show();
        };
        
        // Image gallery modal functionality
        window.showImageModal = function(startIndex) {
            if (typeof eventImages === 'undefined' || eventImages.length === 0) return;
            
            const carouselImages = document.getElementById('carouselImages');
            const carouselIndicators = document.getElementById('carouselIndicators');
            
            // Clear existing content
            carouselImages.innerHTML = '';
            carouselIndicators.innerHTML = '';
            
            // Create carousel items
            eventImages.forEach((image, index) => {
                const fileExt = image.split('.').pop().toLowerCase();
                const isVideo = ['mp4', 'mov', 'avi', 'webm'].includes(fileExt);
                
                // Create carousel item
                const carouselItem = document.createElement('div');
                carouselItem.className = `carousel-item ${index === startIndex ? 'active' : ''}`;
                
                if (isVideo) {
                    carouselItem.innerHTML = `<video controls autoplay class="d-block w-100" style="max-height: 80vh; object-fit: contain;"><source src="../${image}" type="video/${fileExt}"></video>`;
                } else {
                    carouselItem.innerHTML = `<img src="../${image}" class="d-block w-100" style="max-height: 80vh; object-fit: contain;">`;
                }
                carouselImages.appendChild(carouselItem);
                
                // Create indicator
                const indicator = document.createElement('button');
                indicator.type = 'button';
                indicator.setAttribute('data-bs-target', '#imageCarousel');
                indicator.setAttribute('data-bs-slide-to', index);
                indicator.className = index === startIndex ? 'active' : '';
                if (index === startIndex) indicator.setAttribute('aria-current', 'true');
                carouselIndicators.appendChild(indicator);
            });
            
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();
            
            // Stop videos when modal is closed
            document.getElementById('imageModal').addEventListener('hidden.bs.modal', function () {
                const videos = this.querySelectorAll('video');
                videos.forEach(video => {
                    video.pause();
                    video.currentTime = 0;
                });
            });
        };
    </script>
</body>
</html>
<?php 
$stmt->close();
$conn->close(); 
?>