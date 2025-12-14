    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h4><?php echo htmlspecialchars($site_title); ?></h4>
                    <p><?php echo htmlspecialchars($about_text); ?></p>
                </div>
                <div class="col-md-4 mb-4">
                    <h4>Quick Links</h4>
                    <ul class="list-unstyled">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#about">About</a></li>
                        <li><a href="events.php">Events</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h4>Connect With Us</h4>
                    <div class="social-icons">
                        <?php if($instagram_url && $instagram_url != '#'): ?>
                            <a href="<?php echo htmlspecialchars($instagram_url); ?>"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php if($facebook_url && $facebook_url != '#'): ?>
                            <a href="<?php echo htmlspecialchars($facebook_url); ?>"><i class="fab fa-facebook"></i></a>
                        <?php endif; ?>
                        <?php if($twitter_url && $twitter_url != '#'): ?>
                            <a href="<?php echo htmlspecialchars($twitter_url); ?>"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if($linkedin_url && $linkedin_url != '#'): ?>
                            <a href="<?php echo htmlspecialchars($linkedin_url); ?>"><i class="fab fa-linkedin"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <hr class="mt-4 mb-4" style="background-color: rgba(255,255,255,0.1);">
            <p class="text-center mb-0"><?php echo htmlspecialchars($footer_text); ?></p>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>
