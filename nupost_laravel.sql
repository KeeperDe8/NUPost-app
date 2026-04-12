-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table nupost_laravel.cache: ~0 rows (approximately)

-- Dumping data for table nupost_laravel.cache_locks: ~0 rows (approximately)

-- Dumping data for table nupost_laravel.failed_jobs: ~0 rows (approximately)

-- Dumping data for table nupost_laravel.jobs: ~0 rows (approximately)

-- Dumping data for table nupost_laravel.job_batches: ~0 rows (approximately)

-- Dumping data for table nupost_laravel.login_attempts: ~19 rows (approximately)
INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `success`, `attempted_at`) VALUES
	(1, 'mjamelkim@gmail.com', '127.0.0.1', 0, '2026-04-05 12:28:54'),
	(2, 'mjamelkim@gmail.com', '127.0.0.1', 0, '2026-04-05 12:29:03'),
	(3, 'mjamelkim@gmail.com', '127.0.0.1', 0, '2026-04-05 12:29:15'),
	(4, 'offthreadzapp@gmail.com', '127.0.0.1', 0, '2026-04-05 12:30:35'),
	(5, 'offthreadzapp@gmail.com', '127.0.0.1', 0, '2026-04-05 12:30:40'),
	(6, 'mjamelkim@gmail.com', '127.0.0.1', 0, '2026-04-05 12:41:17'),
	(7, 'mjamelkim@gmail.com', '127.0.0.1', 0, '2026-04-05 12:42:44'),
	(8, 'mjamelkim@gmail.com', '127.0.0.1', 1, '2026-04-05 12:50:48'),
	(9, 'mjamelkim@gmail.com', '127.0.0.1', 1, '2026-04-05 13:00:57'),
	(10, 'mjamelkim@gmail.com', '127.0.0.1', 1, '2026-04-05 13:23:50'),
	(11, 'mjamelkim@gmail.com', '127.0.0.1', 1, '2026-04-06 02:55:47'),
	(12, 'mjamelkim@gmail.com', '127.0.0.1', 1, '2026-04-06 03:47:33'),
	(13, 'admin@nupost.com', '127.0.0.1', 0, '2026-04-06 04:13:14'),
	(14, 'mjamelkim@gmail.com', '127.0.0.1', 1, '2026-04-08 11:10:43'),
	(15, 'admin@nupost.com', '127.0.0.1', 0, '2026-04-08 12:28:24'),
	(16, 'admin@nupost.com', '127.0.0.1', 0, '2026-04-08 12:28:29'),
	(17, 'admin@nupost.com', '127.0.0.1', 0, '2026-04-08 12:28:42'),
	(18, 'admin@nupost.com', '127.0.0.1', 0, '2026-04-08 12:28:49'),
	(19, 'admin@nupost.com', '127.0.0.1', 0, '2026-04-09 01:02:11');

-- Dumping data for table nupost_laravel.migrations: ~11 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2026_04_04_123335_create_post_requests_table', 1),
	(5, '2026_04_04_123341_create_notifications_table', 1),
	(6, '2026_04_04_123347_create_otp_codes_table', 1),
	(7, '2026_04_04_123352_create_otp_attempts_table', 1),
	(8, '2026_04_04_123356_create_login_attempts_table', 1),
	(9, '2026_04_04_123401_create_remembered_devices_table', 1),
	(10, '2026_04_04_123406_create_request_comments_table', 1),
	(11, '2026_04_04_123411_create_request_activity_table', 1);

-- Dumping data for table nupost_laravel.notifications: ~1 rows (approximately)
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`, `updated_at`) VALUES
	(1, 4, 'New Comment from Admin', 'Admin commented on your request "hello": teka lang ya, patukan kita e', 'comment', 1, '2026-04-05 20:40:15', '2026-04-08 03:11:16');

-- Dumping data for table nupost_laravel.otp_attempts: ~1 rows (approximately)
INSERT INTO `otp_attempts` (`id`, `user_id`, `success`, `attempted_at`) VALUES
	(1, 4, 1, '2026-04-05 12:50:41');

-- Dumping data for table nupost_laravel.otp_codes: ~1 rows (approximately)
INSERT INTO `otp_codes` (`id`, `user_id`, `email`, `otp_code`, `expires_at`, `is_used`, `created_at`, `updated_at`) VALUES
	(6, 4, 'mjamelkim@gmail.com', '482435', '2026-04-05 12:59:43', 1, '2026-04-05 04:49:43', '2026-04-05 04:50:41');

-- Dumping data for table nupost_laravel.password_reset_tokens: ~0 rows (approximately)

-- Dumping data for table nupost_laravel.post_requests: ~2 rows (approximately)
INSERT INTO `post_requests` (`id`, `request_id`, `title`, `requester`, `category`, `priority`, `status`, `description`, `platform`, `caption`, `preferred_date`, `media_file`, `created_at`, `updated_at`) VALUES
	(1, 'REQ-00001', 'hello NU LIPA Fam!', 'Jamel Kim Magat', 'Announcements', 'Medium', 'Pending Review', 'Welcome back NU PEEPS,', 'Facebook', '', '2026-04-23', '', '2026-04-05 19:07:29', '2026-04-05 19:07:29'),
	(2, 'REQ-00002', 'hello', 'Jamel Kim Magat', 'Announcements', 'High', 'Pending Review', 'hello nu lipa bulldogs', 'Facebook', 'Hello, NU Lipa Bulldogs! 👋🐾 Ready to make this year legendary? Let’s get it! #NULipa #BulldogPride', '2026-04-30', 'media_69d332c6432f1.png,media_69d332c643b91.png', '2026-04-05 20:12:54', '2026-04-08 03:24:33');

-- Dumping data for table nupost_laravel.remembered_devices: ~0 rows (approximately)

-- Dumping data for table nupost_laravel.request_activity: ~0 rows (approximately)

-- Dumping data for table nupost_laravel.request_comments: ~2 rows (approximately)
INSERT INTO `request_comments` (`id`, `request_id`, `sender_role`, `sender_name`, `message`, `created_at`, `updated_at`) VALUES
	(1, 2, 'requestor', 'Jamel Kim Magat', 'hello kelan ippost to? tagal ha', '2026-04-05 20:22:23', '2026-04-05 20:22:23'),
	(2, 2, 'admin', 'admin@nupost.com', 'teka lang ya, patukan kita e', '2026-04-05 20:40:15', '2026-04-05 20:40:15');

-- Dumping data for table nupost_laravel.sessions: ~0 rows (approximately)

-- Dumping data for table nupost_laravel.users: ~1 rows (approximately)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `is_verified`, `phone`, `organization`, `department`, `bio`, `profile_photo`, `email_notif`, `status_updates`, `public_profile`, `remember_token`, `created_at`, `updated_at`) VALUES
	(4, 'Jamel Kim Magat', 'mjamelkim@gmail.com', '$2y$12$ILVdTuLTIfuYdgHqBDMD0.4NXTyIDIc66FUf9xZeykDcBg8FW3C5S', 1, '09451039955', 'JOLLIBEE', 'BOSS', 'ako \'to', 'avatar_4_1775448910.png', 1, 1, 0, NULL, '2026-04-05 04:45:23', '2026-04-05 20:15:10');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
