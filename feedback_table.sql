-- Add feedback table to existing database
CREATE TABLE `event_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `participant_email` varchar(100) NOT NULL,
  `participant_name` varchar(100) NOT NULL,
  `rating` int(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
  `suggestions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_feedback` (`event_id`, `participant_email`),
  FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add feedback_sent column to events table to track email status
ALTER TABLE `events` ADD COLUMN `feedback_sent` tinyint(1) DEFAULT 0;