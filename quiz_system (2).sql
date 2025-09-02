-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 02, 2025 at 05:19 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quiz_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

DROP TABLE IF EXISTS `achievements`;
CREATE TABLE IF NOT EXISTS `achievements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criteria` json NOT NULL,
  `badge_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'primary',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `achievements`
--

INSERT INTO `achievements` (`id`, `name`, `description`, `icon`, `criteria`, `badge_color`, `created_at`) VALUES
(1, 'First Quiz', 'Complete your first quiz', 'fas fa-medal', '{\"type\": \"quiz_count\", \"value\": 1}', 'primary', '2025-08-21 08:56:56'),
(2, 'Quiz Master', 'Complete 10 quizzes', 'fas fa-crown', '{\"type\": \"quiz_count\", \"value\": 10}', 'primary', '2025-08-21 08:56:56'),
(3, 'Perfect Score', 'Get 100% on any quiz', 'fas fa-star', '{\"type\": \"perfect_score\", \"value\": 100}', 'primary', '2025-08-21 08:56:56'),
(4, 'Speed Demon', 'Complete a quiz in under 5 minutes', 'fas fa-bolt', '{\"type\": \"time_limit\", \"value\": 300}', 'primary', '2025-08-21 08:56:56');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quiz_id` int NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('multiple_choice','true_false') NOT NULL DEFAULT 'multiple_choice',
  `option_a` varchar(255) DEFAULT NULL,
  `option_b` varchar(255) DEFAULT NULL,
  `option_c` varchar(255) DEFAULT NULL,
  `option_d` varchar(255) DEFAULT NULL,
  `correct_answer` varchar(10) NOT NULL,
  `explanation` text,
  `points` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `quiz_id`, `question_text`, `question_type`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `explanation`, `points`, `created_at`) VALUES
(9, 6, 'What does \"SSD\" stand for in computing?', 'multiple_choice', 'Solid State Drive', 'Super Speed Data', 'System Storage Device', 'Serial Signal Distributor', 'A', '', 1, '2025-09-02 02:51:46'),
(11, 6, 'What does \'URL\' stand for?', 'multiple_choice', 'Uniform Resource Locator', 'Universal Reference Link', 'Unified Response Language', 'User Request Log', 'A', '', 1, '2025-09-02 03:47:29'),
(10, 7, 'what is 1000 - 258 ?', 'multiple_choice', '872', '642', '742', '752', 'C', '', 1, '2025-09-02 03:45:02'),
(12, 6, 'What is \'the cloud\' in cloud computing?', 'multiple_choice', 'A type of wireless Bluetooth speaker.', 'Remote servers accessed over the internet.', 'The physical hardware inside your personal computer.', 'A weather data processing system.', 'B', '', 1, '2025-09-02 03:48:47'),
(13, 6, 'What does \'OS\' typically stand for in the tech world?', 'multiple_choice', 'Online Security', 'Output Signal', 'Optical Sensor', 'Operating System', 'D', '', 1, '2025-09-02 03:50:11'),
(14, 6, 'What does RAM stand for?', 'multiple_choice', 'Readily Available Memory', 'Remote Access Module', 'Random Access Memory', 'Random Archive Management', 'C', '', 1, '2025-09-02 03:51:35'),
(15, 2, 'What is the hardest natural substance on Earth?', 'multiple_choice', 'Tungsten', 'Diamond', 'Quartz', 'Iron', 'B', '', 1, '2025-09-02 03:54:25'),
(16, 2, 'Which organ is part of the human respiratory system?', 'multiple_choice', 'Lungs', 'Liver', 'Kidneys', 'Stomach', 'A', '', 1, '2025-09-02 03:55:36'),
(17, 2, 'What is the unit of measurement for electrical resistance?', 'multiple_choice', 'Ohm (Ω)', 'Volt (V)', 'Watt (W)', 'Ampere (A)', 'A', '', 1, '2025-09-02 03:56:38'),
(18, 2, 'What is the chemical symbol for the element gold?', 'multiple_choice', 'Gd', 'Go', 'Au', 'Ag', 'C', '', 1, '2025-09-02 03:57:24'),
(19, 2, 'What is the closest star to Earth?', 'multiple_choice', 'Proxima Centauri', 'Sirius', 'Alpha Centauri', 'The Sun', 'D', '', 1, '2025-09-02 03:58:42'),
(20, 7, 'What is 15% of 200?', 'multiple_choice', '15', '30', '300', '55', 'B', '', 1, '2025-09-02 03:59:51'),
(21, 7, 'What is 5 squared (5²)?', 'multiple_choice', '35', '7', '25', '100', 'C', '', 1, '2025-09-02 04:00:54'),
(22, 7, 'Solve for x: 2x + 5 = 13', 'multiple_choice', 'x = 3.5', 'x = 6', 'x = 9', 'x = 4', 'D', '', 1, '2025-09-02 04:02:00'),
(23, 7, 'What is the area of a rectangle with a length of 8 units and a width of 5 units?', 'multiple_choice', '35 square units', '40 square units', '26 square units', '13 square units', 'B', '', 1, '2025-09-02 04:03:09'),
(24, 7, 'What is 5 squared (5²)?', 'multiple_choice', '35', '7', '25', '100', 'C', '', 1, '2025-09-02 04:03:25');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

DROP TABLE IF EXISTS `quizzes`;
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `subject_id` int NOT NULL,
  `time_limit` int DEFAULT '30',
  `max_attempts` int DEFAULT '0',
  `passing_score` int DEFAULT '60',
  `shuffle_questions` tinyint(1) DEFAULT '1',
  `show_results_immediately` tinyint(1) DEFAULT '1',
  `status` enum('active','inactive','draft') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_quizzes_subject` (`subject_id`),
  KEY `idx_quizzes_status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `title`, `description`, `subject_id`, `time_limit`, `max_attempts`, `passing_score`, `shuffle_questions`, `show_results_immediately`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Science Fundamentals', 'General science knowledge test', 2, 20, 0, 60, 1, 1, 'active', '2025-08-21 08:56:56', '2025-08-21 08:56:56'),
(6, 'Basic Technology Quiz', 'test your knowledge', 6, 10, 0, 100, 1, 1, 'active', '2025-09-02 02:32:45', '2025-09-02 02:32:45'),
(7, 'Basic Mathematics Quiz', 'Test your knowledge of basic mathematical operations', 1, 10, 0, 100, 1, 1, 'active', '2025-09-02 02:33:20', '2025-09-02 02:33:20');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

DROP TABLE IF EXISTS `quiz_attempts`;
CREATE TABLE IF NOT EXISTS `quiz_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `total_questions` int NOT NULL,
  `correct_answers` int NOT NULL DEFAULT '0',
  `time_taken` int DEFAULT NULL,
  `answers` json NOT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_quiz_attempts_user` (`user_id`),
  KEY `idx_quiz_attempts_quiz` (`quiz_id`),
  KEY `idx_quiz_attempts_score` (`score`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `user_id`, `quiz_id`, `score`, `total_questions`, `correct_answers`, `time_taken`, `answers`, `start_time`, `end_time`, `created_at`) VALUES
(36, 1, 7, 100.00, 6, 0, NULL, '{\"10\": \"C\", \"20\": \"B\", \"21\": \"C\", \"22\": \"D\", \"23\": \"B\", \"24\": \"C\"}', NULL, NULL, '2025-09-02 04:57:14'),
(35, 1, 7, 50.00, 6, 0, NULL, '{\"10\": \"C\", \"20\": \"D\", \"21\": \"C\", \"22\": \"B\", \"23\": \"B\", \"24\": \"A\"}', NULL, NULL, '2025-09-02 04:55:41'),
(34, 1, 7, 50.00, 6, 0, NULL, '{\"10\": \"C\", \"20\": \"C\", \"21\": \"C\", \"22\": \"C\", \"23\": \"C\", \"24\": \"C\"}', NULL, NULL, '2025-09-02 04:27:35'),
(33, 1, 6, 20.00, 5, 0, NULL, '{\"9\": \"C\", \"11\": \"A\", \"12\": \"C\", \"13\": \"C\", \"14\": \"A\"}', NULL, NULL, '2025-09-02 04:21:04'),
(32, 1, 2, 60.00, 5, 0, NULL, '{\"15\": \"B\", \"16\": \"C\", \"17\": \"C\", \"18\": \"C\", \"19\": \"D\"}', NULL, NULL, '2025-09-02 04:11:56'),
(31, 1, 7, 100.00, 6, 0, NULL, '{\"10\": \"C\", \"20\": \"B\", \"21\": \"C\", \"22\": \"D\", \"23\": \"B\", \"24\": \"C\"}', NULL, NULL, '2025-09-02 04:04:30'),
(30, 1, 6, 100.00, 1, 0, NULL, '{\"9\": \"A\"}', NULL, NULL, '2025-09-02 03:33:09'),
(29, 1, 6, 100.00, 1, 0, NULL, '{\"9\": \"A\"}', NULL, NULL, '2025-09-02 03:27:08'),
(27, 1, 6, 100.00, 1, 0, NULL, '{\"9\": \"A\"}', NULL, NULL, '2025-09-02 03:07:20'),
(28, 1, 6, 100.00, 1, 0, NULL, '{\"9\": \"A\"}', NULL, NULL, '2025-09-02 03:13:42'),
(23, 1, 6, 100.00, 1, 0, NULL, '{\"9\": \"A\"}', NULL, NULL, '2025-09-02 02:52:24'),
(24, 1, 6, 0.00, 1, 0, NULL, '{\"9\": \"B\"}', NULL, NULL, '2025-09-02 03:02:47'),
(25, 1, 6, 100.00, 1, 0, NULL, '{\"9\": \"A\"}', NULL, NULL, '2025-09-02 03:03:08'),
(26, 1, 6, 100.00, 1, 0, NULL, '{\"9\": \"A\"}', NULL, NULL, '2025-09-02 03:04:55');

-- --------------------------------------------------------

--
-- Stand-in structure for view `quiz_statistics`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `quiz_statistics`;
CREATE TABLE IF NOT EXISTS `quiz_statistics` (
`id` int
,`title` varchar(200)
,`subject_id` int
,`subject_name` varchar(100)
,`total_attempts` bigint
,`average_score` decimal(9,6)
,`highest_score` decimal(5,2)
,`lowest_score` decimal(5,2)
,`unique_users` bigint
);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `setting_type` enum('string','integer','boolean','json') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`) VALUES
(1, 'site_name', 'Quiz System', 'string', 'Name of the quiz system', '2025-08-21 08:56:56'),
(2, 'max_quiz_attempts', '3', 'integer', 'Maximum attempts allowed per quiz', '2025-08-21 08:56:56'),
(3, 'default_quiz_time', '30', 'integer', 'Default time limit for quizzes in minutes', '2025-08-21 08:56:56'),
(4, 'passing_score', '60', 'integer', 'Default passing score percentage', '2025-08-21 08:56:56'),
(5, 'allow_registration', 'true', 'boolean', 'Allow new user registrations', '2025-08-21 08:56:56'),
(6, 'show_leaderboard', 'true', 'boolean', 'Show public leaderboard', '2025-08-21 08:56:56');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `description`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Mathematics', 'Basic to advanced mathematical concepts', NULL, 'active', '2025-08-21 08:56:56', '2025-08-21 08:56:56'),
(2, 'Science', 'General science topics including physics, chemistry, and biology', NULL, 'active', '2025-08-21 08:56:56', '2025-08-21 08:56:56'),
(3, 'History', 'World history and historical events', NULL, 'active', '2025-08-21 08:56:56', '2025-08-21 08:56:56'),
(4, 'Geography', 'World geography, countries, capitals, and landmarks', NULL, 'active', '2025-08-21 08:56:56', '2025-08-21 08:56:56'),
(5, 'Literature', 'Classic and modern literature analysis', NULL, 'active', '2025-08-21 08:56:56', '2025-08-21 08:56:56'),
(6, 'Technology', 'Computer science and technology topics', NULL, 'active', '2025-08-21 08:56:56', '2025-08-21 08:56:56'),
(7, 'English', 'Learn , Practice , Test and Improve', NULL, 'active', '2025-08-21 12:11:29', '2025-08-21 12:11:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `profile_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_role` (`role`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `profile_image`, `created_at`, `updated_at`, `last_login`, `status`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$lMHexS1ocm7nk.PtQPYtV.K2MknKXQaNdGJqfL5i3g7W4pMoyrLcO', 'admin', NULL, '2025-08-21 08:56:56', '2025-08-21 12:45:54', NULL, 'active'),
(2, 'dilan', 'dilan@gmail.com', '$2y$10$6TC4VBQZ9TGOCatPXguRn.aIs1QevAdza9m0hAtfDam/76dYdMdZa', 'user', NULL, '2025-08-21 09:11:37', '2025-08-21 09:11:37', NULL, 'active'),
(3, 'nimal', 'nimal@gmail.com', '$2y$10$ASp5jTc5t9PjIDakiFqNHeeb/nk7ZMPmAPXSfexyf1XumCzHFNyQu', 'user', NULL, '2025-08-21 09:44:42', '2025-08-21 10:34:59', NULL, 'active'),
(4, 'ravi', 'ravi@gmail.com', '$2y$10$edo2VLtDg.2uGT4Ed7p/9OBAcPjfhkeBCHzecXxlYJUNhaZnYs6ge', 'user', NULL, '2025-08-21 12:46:20', '2025-08-21 12:46:20', NULL, 'active'),
(5, 'rayan', 'rayan@gmail.com', '$2y$10$h.c/yE9lnS/Qg534M4qAvuxeqpQNptl69BFn9CC2CB6B7wovxXzhO', 'user', NULL, '2025-08-21 18:25:41', '2025-08-21 18:25:41', NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `user_achievements`
--

DROP TABLE IF EXISTS `user_achievements`;
CREATE TABLE IF NOT EXISTS `user_achievements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `achievement_id` int NOT NULL,
  `earned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_achievement` (`user_id`,`achievement_id`),
  KEY `achievement_id` (`achievement_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_answers`
--

DROP TABLE IF EXISTS `user_answers`;
CREATE TABLE IF NOT EXISTS `user_answers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `attempt_id` int NOT NULL,
  `question_id` int NOT NULL,
  `answer` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `attempt_id` (`attempt_id`),
  KEY `question_id` (`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

DROP TABLE IF EXISTS `user_progress`;
CREATE TABLE IF NOT EXISTS `user_progress` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `total_quizzes_taken` int DEFAULT '0',
  `average_score` decimal(5,2) DEFAULT '0.00',
  `best_score` decimal(5,2) DEFAULT '0.00',
  `total_time_spent` int DEFAULT '0',
  `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_subject` (`user_id`,`subject_id`),
  KEY `subject_id` (`subject_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_statistics`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `user_statistics`;
CREATE TABLE IF NOT EXISTS `user_statistics` (
`id` int
,`username` varchar(50)
,`email` varchar(100)
,`total_quizzes_taken` bigint
,`average_score` decimal(9,6)
,`best_score` decimal(5,2)
,`unique_quizzes` bigint
,`total_time_spent` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Structure for view `quiz_statistics`
--
DROP TABLE IF EXISTS `quiz_statistics`;

DROP VIEW IF EXISTS `quiz_statistics`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `quiz_statistics`  AS SELECT `q`.`id` AS `id`, `q`.`title` AS `title`, `q`.`subject_id` AS `subject_id`, `s`.`name` AS `subject_name`, count(`qa`.`id`) AS `total_attempts`, avg(`qa`.`score`) AS `average_score`, max(`qa`.`score`) AS `highest_score`, min(`qa`.`score`) AS `lowest_score`, count(distinct `qa`.`user_id`) AS `unique_users` FROM ((`quizzes` `q` left join `quiz_attempts` `qa` on((`q`.`id` = `qa`.`quiz_id`))) left join `subjects` `s` on((`q`.`subject_id` = `s`.`id`))) GROUP BY `q`.`id`, `q`.`title`, `q`.`subject_id`, `s`.`name` ;

-- --------------------------------------------------------

--
-- Structure for view `user_statistics`
--
DROP TABLE IF EXISTS `user_statistics`;

DROP VIEW IF EXISTS `user_statistics`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_statistics`  AS SELECT `u`.`id` AS `id`, `u`.`username` AS `username`, `u`.`email` AS `email`, count(`qa`.`id`) AS `total_quizzes_taken`, avg(`qa`.`score`) AS `average_score`, max(`qa`.`score`) AS `best_score`, count(distinct `qa`.`quiz_id`) AS `unique_quizzes`, sum(`qa`.`time_taken`) AS `total_time_spent` FROM (`users` `u` left join `quiz_attempts` `qa` on((`u`.`id` = `qa`.`user_id`))) WHERE (`u`.`role` = 'user') GROUP BY `u`.`id`, `u`.`username`, `u`.`email` ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
