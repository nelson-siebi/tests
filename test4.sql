-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 19, 2025 at 08:59 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test4`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('superadmin','support','finance','moderator') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'support',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `role`, `email`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'changer', '$2y$10$DS/CFm/Xp/n/HDiKrrVQReSQt8CpM0guW8bcZBqzDXb3AwEB1qZRe', 'superadmin', 'changerlemonde@gmail.com', '2025-12-19 20:44:33', '2025-12-02 13:00:33', '2025-12-19 20:44:33');

-- --------------------------------------------------------

--
-- Table structure for table `ads_videos`
--

CREATE TABLE `ads_videos` (
  `id` bigint UNSIGNED NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `youtube_url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `youtube_video_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ads_videos`
--

INSERT INTO `ads_videos` (`id`, `titre`, `youtube_url`, `youtube_video_id`, `actif`, `created_at`, `updated_at`) VALUES
(1, 'ffffff', 'https://youtube.com/fdhjfdj', '', 1, '2025-12-02 14:02:59', '2025-12-02 14:02:59'),
(2, 'tuto enfant', 'https://www.youtube.com/watch?v=9Y9ziyp3JnM', '9Y9ziyp3JnM', 1, '2025-12-02 14:03:29', '2025-12-16 12:18:07');

-- --------------------------------------------------------

--
-- Table structure for table `ads_views`
--

CREATE TABLE `ads_views` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `video_id` bigint UNSIGNED NOT NULL,
  `date_view` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `gain` decimal(18,2) NOT NULL DEFAULT '0.00',
  `valide` tinyint(1) NOT NULL DEFAULT '1',
  `ip_addr` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ads_views`
--

INSERT INTO `ads_views` (`id`, `user_id`, `video_id`, `date_view`, `gain`, `valide`, `ip_addr`, `user_agent`) VALUES
(2, 1, 2, '2025-12-02 22:19:39', 50.00, 1, '::1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36'),
(3, 1, 1, '2025-12-02 22:20:07', 50.00, 1, '::1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36'),
(4, 1, 2, '2025-12-10 17:43:16', 50.00, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(5, 1, 1, '2025-12-13 13:01:15', 50.00, 1, '::1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36'),
(6, 1, 2, '2025-12-13 13:04:02', 50.00, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(7, 8, 2, '2025-12-15 19:28:22', 10.00, 1, '102.244.222.121', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36'),
(8, 8, 1, '2025-12-15 19:28:44', 10.00, 1, '102.244.222.121', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36'),
(9, 5, 1, '2025-12-15 19:35:59', 100.00, 1, '41.202.219.76', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36'),
(10, 5, 2, '2025-12-15 19:36:32', 100.00, 1, '41.202.219.76', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36'),
(11, 5, 2, '2025-12-16 06:29:09', 100.00, 1, '102.244.44.37', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36'),
(12, 5, 1, '2025-12-16 06:29:31', 100.00, 1, '102.244.44.37', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36'),
(13, 9, 2, '2025-12-16 09:14:29', 100.00, 1, '102.244.44.37', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36'),
(14, 9, 1, '2025-12-16 09:14:59', 100.00, 1, '102.244.44.37', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36'),
(15, 1, 2, '2025-12-16 12:18:46', 50.00, 1, '129.0.99.210', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `admin_id` int UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` bigint UNSIGNED DEFAULT NULL,
  `description` text,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kyc`
--

CREATE TABLE `kyc` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `type_document` enum('id_card','passport','driving_license','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_document` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fichier_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `date_soumission` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_validation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'fas fa-bell',
  `icon_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'text-green-600',
  `bg_color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'bg-green-100',
  `action_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_text` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `is_read`, `created_at`, `icon`, `icon_color`, `bg_color`, `action_url`, `action_text`) VALUES
(33, 8, 'withdrawal', 'Demande de retrait envoyée', 'Votre demande de retrait de 1500 FCFA via orange est en cours de traitement (24-48h).', 0, '2025-12-14 23:56:54', 'fas fa-download', 'text-blue-600', 'bg-blue-100', '?page=transactions', 'Suivre ma demande'),
(34, 8, 'withdrawal', 'Demande de retrait envoyée', 'Votre demande de retrait de 1500 FCFA via orange est en cours de traitement (24-48h).', 0, '2025-12-14 23:58:27', 'fas fa-download', 'text-blue-600', 'bg-blue-100', '?page=transactions', 'Suivre ma demande'),
(35, 8, 'withdrawal', 'Demande de retrait envoyée', 'Votre demande de retrait de 1500 FCFA via orange est en cours de traitement (24-48h).', 0, '2025-12-14 23:59:02', 'fas fa-download', 'text-blue-600', 'bg-blue-100', '?page=transactions', 'Suivre ma demande'),
(36, 9, 'withdrawal', 'Demande de retrait envoyée', 'Votre demande de retrait de 33200 FCFA via mtn est en cours de traitement (24-48h).', 0, '2025-12-17 18:41:37', 'fas fa-download', 'text-blue-600', 'bg-blue-100', '?page=transactions', 'Suivre ma demande'),
(37, 5, 'withdrawal', 'Demande de retrait envoyée', 'Votre demande de retrait de 66200 FCFA via mtn est en cours de traitement (24-48h).', 0, '2025-12-17 18:45:37', 'fas fa-download', 'text-blue-600', 'bg-blue-100', '?page=transactions', 'Suivre ma demande'),
(38, 10, 'withdrawal', 'Demande de retrait envoyée', 'Votre demande de retrait de 9300 FCFA via mtn est en cours de traitement (24-48h).', 0, '2025-12-17 19:05:06', 'fas fa-download', 'text-blue-600', 'bg-blue-100', '?page=transactions', 'Suivre ma demande'),
(39, 9, 'withdrawal', 'Demande de retrait envoyée', 'Votre demande de retrait de 1200 FCFA via mtn est en cours de traitement (24-48h).', 0, '2025-12-18 05:44:38', 'fas fa-download', 'text-blue-600', 'bg-blue-100', '?page=transactions', 'Suivre ma demande');

-- --------------------------------------------------------

--
-- Table structure for table `pending_transactions`
--

CREATE TABLE `pending_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `plan_id` int UNSIGNED NOT NULL,
  `montant` decimal(18,2) NOT NULL,
  `methode` enum('orange','mtn','visa','mobile_money','autre') NOT NULL DEFAULT 'orange',
  `numero_telephone` varchar(50) DEFAULT NULL,
  `transaction_code` varchar(100) NOT NULL,
  `status` enum('pending','processing','completed','failed','expired') NOT NULL DEFAULT 'pending',
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `session_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` int UNSIGNED NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prix` decimal(18,2) NOT NULL,
  `roi_journalier` decimal(18,2) NOT NULL,
  `duree_jours` int UNSIGNED NOT NULL,
  `videos_par_jour` int UNSIGNED NOT NULL DEFAULT '0',
  `gain_par_video` decimal(18,2) NOT NULL DEFAULT '0.00',
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `nom`, `prix`, `roi_journalier`, `duree_jours`, `videos_par_jour`, `gain_par_video`, `actif`, `description`, `image`, `created_at`, `updated_at`) VALUES
(3, 'vip1', 4000.00, 1000.00, 30, 10, 50.00, 1, 'bonbon', NULL, '2025-12-02 18:43:31', '2025-12-19 21:52:53'),
(7, 'Vip 4', 20000.00, 5800.00, 30, 3, 40.00, 1, '', NULL, '2025-12-15 18:45:29', '2025-12-15 18:45:29'),
(8, 'Vip 5', 30000.00, 9100.00, 30, 5, 30.00, 1, '', NULL, '2025-12-15 18:46:32', '2025-12-15 18:46:32'),
(10, 'Vip 6', 50000.00, 16000.00, 30, 7, 30.00, 1, '', NULL, '2025-12-15 18:50:41', '2025-12-15 18:50:41'),
(11, 'Vip 2', 6000.00, 1500.00, 30, 3, 10.00, 1, '', NULL, '2025-12-15 18:51:40', '2025-12-15 18:51:40'),
(12, 'Vip 7', 100000.00, 33000.00, 30, 3, 100.00, 1, '', NULL, '2025-12-15 18:58:13', '2025-12-15 18:58:13'),
(13, 'vip3', 10000.00, 2000.00, 30, 10, 25.00, 1, 'top', NULL, '2025-12-19 20:50:18', '2025-12-19 20:50:18');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` bigint UNSIGNED NOT NULL,
  `parrain_id` int UNSIGNED NOT NULL,
  `filleul_id` int UNSIGNED NOT NULL,
  `bonus` decimal(18,2) NOT NULL DEFAULT '0.00',
  `valide` tinyint(1) NOT NULL DEFAULT '0',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_validation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `parrain_id`, `filleul_id`, `bonus`, `valide`, `date_creation`, `date_validation`) VALUES
(1, 2, 3, 500.00, 0, '2025-12-02 14:56:14', NULL),
(2, 5, 9, 1100.00, 1, '2025-12-16 06:50:51', '2025-12-18 05:42:33'),
(3, 1, 11, 500.00, 0, '2025-12-17 18:59:38', NULL),
(4, 10, 12, 1400.00, 1, '2025-12-18 11:56:48', '2025-12-18 11:58:00');

-- --------------------------------------------------------

--
-- Table structure for table `roi_history`
--

CREATE TABLE `roi_history` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `user_plan_id` bigint UNSIGNED NOT NULL,
  `montant` decimal(18,2) NOT NULL,
  `date_versement` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `type` enum('depot','retrait','gain','achat','bonus') COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `montant` decimal(18,2) NOT NULL,
  `methode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_telephone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` enum('attente','success','failed','annule') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'attente',
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `nom` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenom` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referral_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referred_by` int UNSIGNED DEFAULT NULL,
  `statut` enum('active','banned','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email_notifications` tinyint(1) DEFAULT '1',
  `sms_notifications` tinyint(1) DEFAULT '0',
  `push_notifications` tinyint(1) DEFAULT '1',
  `marketing_emails` tinyint(1) DEFAULT '1',
  `two_factor_auth` tinyint(1) DEFAULT '0',
  `login_alerts` tinyint(1) DEFAULT '1',
  `session_timeout` int DEFAULT '60',
  `pays` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `phone`, `password`, `referral_code`, `referred_by`, `statut`, `created_at`, `updated_at`, `email_notifications`, `sms_notifications`, `push_notifications`, `marketing_emails`, `two_factor_auth`, `login_alerts`, `session_timeout`, `pays`, `ville`, `last_login`) VALUES
(1, 'Siebi', 'Nelson', 'nelsonsiebie237@gmail.com', '+237676676120', '$2y$10$2uoG9uX40fmVDw0PKrlaYel330t/aBUxGG3nS15kDm9viqjoI44Ly', 'IZYNS4562', NULL, 'active', '2025-12-01 17:51:21', '2025-12-19 20:34:08', 1, 1, 1, 1, 0, 1, 60, 'FR', 'Douala', '2025-12-19 12:31:45'),
(2, 'Siebi', 'Nelson', 'nelsonsiebi237@gmail.com', '+237676676120', '$2y$10$C8L8pT5JtxIlpPI7hiLmnuBP1wb5VHZUgv5amp30YMQsrNIc8N7TS', 'IZYNS6208', NULL, 'active', '2025-12-02 10:11:23', '2025-12-02 10:11:23', 1, 0, 1, 1, 0, 1, 60, NULL, NULL, NULL),
(3, 'junior1', 'NelsFon', 'nelsonsiebi@gmail.com', '+237 676 67 61 20', '$2y$10$vupgVJq.g/E6Yo3d6WuTW.KJ9zP1Cv9kOaNydVoL7EyFBy4NYadXG', 'IZYNJ2AE5B1', 2, 'active', '2025-12-02 14:56:14', '2025-12-02 14:56:14', 1, 0, 1, 1, 0, 1, 60, NULL, NULL, NULL),
(4, 'john', 'jony', 'john@gmail.com', '+237 651 17 00 75', '$2y$10$2s3/peVKP48SpEnZIkJEuOmNAC.p2EcZ5EAkph6LsbXf5ZtVZAKCG', 'IZYJJ3C406F', NULL, 'active', '2025-12-10 19:17:43', '2025-12-10 19:17:43', 1, 0, 1, 1, 0, 1, 60, NULL, NULL, NULL),
(5, 'Test1', '11', 'test1@gmail.com', '+237 640 20 47 47', '$2y$10$gi1WDs59OBm/V1DpDQtqze/YYKygfiGHLWj3HJMoymxewK7bmjgeK', 'IZY1T338C42', NULL, 'active', '2025-12-14 05:15:45', '2025-12-17 18:43:11', 1, 0, 1, 1, 0, 1, 60, NULL, NULL, '2025-12-17 18:43:11'),
(6, 'Test2', '22', 'test2@gmail.com', '+237 640 20 47 47', '$2y$10$HlPe6Jx7/zJ0hcpPGY1edeFfF4iRMgwb1jsvCF4V5I6oeQxHXLDw6', 'IZY2T49AE9E', NULL, 'active', '2025-12-14 05:50:07', '2025-12-16 12:41:47', 1, 0, 1, 1, 0, 1, 60, NULL, NULL, '2025-12-16 12:41:47'),
(7, 'Test', '33', 'test3@gmail.com', '+237 640 20 47 47', '$2y$10$wpc0/ZknaVUyUYLxbN0Y9eki0CYTe4pt4pFRZOsK/pFyrdXpwINxW', 'IZY3TD130E6', NULL, 'active', '2025-12-14 05:51:20', '2025-12-16 12:40:38', 1, 0, 1, 1, 0, 1, 60, NULL, NULL, '2025-12-16 12:40:38'),
(8, 'Test4', '44', 'test4@gmail.com', '+237 640 20 47 89', '$2y$10$xECxibxxyut5rHIn7P1q1.OqDoKThM6DX9z4vDtoMsjsyFR4yAe0i', 'IZY4TFAB98C', NULL, 'active', '2025-12-14 23:13:46', '2025-12-16 12:41:14', 1, 0, 1, 1, 0, 1, 60, NULL, NULL, '2025-12-16 12:41:14'),
(9, 'Tesp', 'Pp', 'testp@gmail.com', '+237 654 65 65 65', '$2y$10$hvaellFofw/FEguNJrK/6uvBzxGtlY5ggTEEumj2HbWowgG8qQHWi', 'IZYPTCD1B65', 5, 'active', '2025-12-16 06:50:51', '2025-12-17 18:40:06', 1, 0, 1, 1, 0, 1, 60, NULL, NULL, '2025-12-17 18:40:06'),
(10, 'Testp2', 'P2', 'testp2@gmail.com', '+237 670 53 07 12', '$2y$10$3zJsrbfOciraRJFsRgDjruLF7PlU3mMSB5hqsIPNTyON2MjUaTJAe', 'IZYPT3D5DD2', NULL, 'active', '2025-12-17 18:56:26', '2025-12-17 18:56:26', 1, 0, 1, 1, 0, 1, 60, NULL, NULL, NULL),
(11, 'ymeM', 'ymeleM', 'nelsonsiebi98@gmail.com', '+237 600 00 00 04', '$2y$10$dcDPtbydhLAwG./lao1akO2J67J08UCmKRs49GVhnpxGEVI8hVe5m', 'IZYYY4F85FF', 1, 'active', '2025-12-17 18:59:38', '2025-12-17 18:59:38', 1, 0, 1, 1, 0, 1, 60, NULL, NULL, NULL),
(12, 'Pa', 'Pp', 'p@gmail.com', '+237 640 20 47 89', '$2y$10$kO1d6JMjb.C2DDfJI7wkROxnjOfRk1vqjmnvOmZxJl473L3Fbj2iG', 'IZYPPD2F3D3', 10, 'active', '2025-12-18 11:56:48', '2025-12-19 20:58:20', 1, 0, 1, 1, 0, 1, 60, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_payment_methods`
--

CREATE TABLE `user_payment_methods` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `methode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` enum('active','pending','blocked') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_plans`
--

CREATE TABLE `user_plans` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `plan_id` int UNSIGNED NOT NULL,
  `montant_investi` decimal(18,2) NOT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `statut` enum('active','termine','expire','annule') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `last_activity` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `selector` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hashed_validator` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `user_id` int UNSIGNED NOT NULL,
  `solde_investissement` decimal(18,2) NOT NULL DEFAULT '0.00',
  `solde_publicite` decimal(18,2) NOT NULL DEFAULT '0.00',
  `solde_parrainage` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_retrait_invest` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_retrait_pub` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_retrait_parrain` decimal(18,2) NOT NULL DEFAULT '0.00',
  `total_depots` decimal(18,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`user_id`, `solde_investissement`, `solde_publicite`, `solde_parrainage`, `total_retrait_invest`, `total_retrait_pub`, `total_retrait_parrain`, `total_depots`, `created_at`, `updated_at`) VALUES
(1, 2200.00, 350.00, 0.00, 60010.00, 0.00, 0.00, 46000.00, '2025-12-01 17:51:21', '2025-12-18 05:41:35'),
(2, 500.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2025-12-02 10:11:23', '2025-12-02 13:41:12'),
(3, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2025-12-02 14:56:14', '2025-12-02 14:56:14'),
(4, 9005.00, 0.00, 0.00, 0.00, 0.00, 0.00, 4000.00, '2025-12-10 19:17:43', '2025-12-18 05:41:35'),
(5, 0.00, 400.00, 600.00, 71400.00, 0.00, 0.00, 104000.00, '2025-12-14 05:15:45', '2025-12-18 05:42:33'),
(6, 18200.00, 0.00, 0.00, 0.00, 0.00, 0.00, 10000.00, '2025-12-14 05:50:07', '2025-12-18 05:41:35'),
(7, 8200.00, 0.00, 0.00, 0.00, 0.00, 0.00, 4000.00, '2025-12-14 05:51:20', '2025-12-18 05:41:35'),
(8, 6200.00, 20.00, 0.00, 2700.00, 0.00, 0.00, 10000.00, '2025-12-14 23:13:46', '2025-12-18 05:41:35'),
(9, 0.00, 200.00, 0.00, 34400.00, 0.00, 0.00, 104000.00, '2025-12-16 06:50:51', '2025-12-18 05:44:38'),
(10, 0.00, 0.00, 900.00, 9300.00, 0.00, 0.00, 30000.00, '2025-12-17 18:56:26', '2025-12-18 11:58:00'),
(11, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2025-12-17 18:59:38', '2025-12-17 18:59:38'),
(12, 200.00, 0.00, 0.00, 0.00, 0.00, 0.00, 6000.00, '2025-12-18 11:56:48', '2025-12-18 11:58:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `ads_videos`
--
ALTER TABLE `ads_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ads_actif` (`actif`);

--
-- Indexes for table `ads_views`
--
ALTER TABLE `ads_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ads_views_user_date` (`user_id`,`date_view`),
  ADD KEY `fk_ads_views_video` (`video_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kyc`
--
ALTER TABLE `kyc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kyc_user` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pending_transactions`
--
ALTER TABLE `pending_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_plan_nom` (`nom`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_parrain_filleul` (`parrain_id`,`filleul_id`),
  ADD KEY `idx_ref_parrain` (`parrain_id`),
  ADD KEY `idx_ref_filleul` (`filleul_id`);

--
-- Indexes for table `roi_history`
--
ALTER TABLE `roi_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_roi_user_date` (`user_id`,`date_versement`),
  ADD KEY `fk_roi_user_plan` (`user_plan_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transactions_user` (`user_id`),
  ADD KEY `idx_transactions_type` (`type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `referral_code` (`referral_code`),
  ADD KEY `idx_referred_by` (`referred_by`);

--
-- Indexes for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_plans`
--
ALTER TABLE `user_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_plan_user` (`user_id`),
  ADD KEY `idx_user_plan_plan` (`plan_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ads_videos`
--
ALTER TABLE `ads_videos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ads_views`
--
ALTER TABLE `ads_views`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kyc`
--
ALTER TABLE `kyc`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `pending_transactions`
--
ALTER TABLE `pending_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roi_history`
--
ALTER TABLE `roi_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_plans`
--
ALTER TABLE `user_plans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ads_views`
--
ALTER TABLE `ads_views`
  ADD CONSTRAINT `fk_ads_views_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ads_views_video` FOREIGN KEY (`video_id`) REFERENCES `ads_videos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kyc`
--
ALTER TABLE `kyc`
  ADD CONSTRAINT `fk_kyc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `fk_ref_filleul` FOREIGN KEY (`filleul_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ref_parrain` FOREIGN KEY (`parrain_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `roi_history`
--
ALTER TABLE `roi_history`
  ADD CONSTRAINT `fk_roi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_roi_user_plan` FOREIGN KEY (`user_plan_id`) REFERENCES `user_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  ADD CONSTRAINT `user_payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_plans`
--
ALTER TABLE `user_plans`
  ADD CONSTRAINT `fk_user_plans_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`),
  ADD CONSTRAINT `fk_user_plans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `fk_wallets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
