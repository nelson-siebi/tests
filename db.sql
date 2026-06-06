-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 14, 2025 at 01:21 AM
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
-- Database: `invest`
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
(1, 'changer', '$2y$10$DS/CFm/Xp/n/HDiKrrVQReSQt8CpM0guW8bcZBqzDXb3AwEB1qZRe', 'superadmin', 'changerlemonde@gmail.com', '2025-12-13 11:12:08', '2025-12-02 13:00:33', '2025-12-13 11:12:08');

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
(2, 'ffffff', 'https://youtube.com/fdhjfdj', '', 1, '2025-12-02 14:03:29', '2025-12-02 14:03:29');

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
(6, 1, 2, '2025-12-13 13:04:02', 50.00, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `admin_id` int UNSIGNED DEFAULT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_id` bigint UNSIGNED DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 1, 'investment', 'Nouveau ROI disponible', 'Votre investissement \"Plan Starter\" a généré un ROI de 750 FCFA', 1, '2025-12-02 09:41:17', 'fas fa-coins', 'text-yellow-600', 'bg-yellow-100', '?page=wallet', 'Voir mon portefeuille'),
(2, 1, 'withdrawal', 'Retrait approuvé', 'Votre retrait de 25,000 FCFA a été traité et sera crédité sous 24h', 1, '2025-12-02 08:41:18', 'fas fa-check-circle', 'text-green-600', 'bg-green-100', '?page=transactions', 'Voir la transaction'),
(3, 1, 'referral', 'Nouveau filleul', 'Marie Konaté s\'est inscrit avec votre code de parrainage', 1, '2025-12-02 07:41:18', 'fas fa-user-plus', 'text-blue-600', 'bg-blue-100', '?page=parrainage', 'Voir mes filleuls'),
(4, 1, 'video', 'Vidéos disponibles', '5 nouvelles vidéos rémunérées sont disponibles aujourd\'hui', 1, '2025-12-02 06:41:18', 'fas fa-video', 'text-purple-600', 'bg-purple-100', '?page=videos', 'Regarder maintenant'),
(5, 1, 'promotion', 'Offre spéciale', 'Investissez 50,000 FCFA et obtenez 10% de bonus supplémentaire', 1, '2025-12-02 05:41:18', 'fas fa-gift', 'text-red-600', 'bg-red-100', '?page=investissement', 'Profiter de l\'offre'),
(6, 1, 'security', 'Connexion détectée', 'Une nouvelle connexion a été détectée depuis un appareil Windows', 1, '2025-12-02 04:41:18', 'fas fa-shield-alt', 'text-orange-600', 'bg-orange-100', '?page=settings&section=securite', 'Vérifier la sécurité'),
(7, 1, 'update', 'Mise à jour disponible', 'Une nouvelle version de l\'application est disponible', 1, '2025-12-02 03:41:18', 'fas fa-sync-alt', 'text-indigo-600', 'bg-indigo-100', '#', 'Mettre à jour'),
(15, 2, 'investment', 'Nouveau ROI disponible', 'Votre investissement \"Plan Starter\" a généré un ROI de 750 FCFA', 1, '2025-12-02 10:31:21', 'fas fa-coins', 'text-yellow-600', 'bg-yellow-100', '?page=wallet', 'Voir mon portefeuille'),
(16, 2, 'withdrawal', 'Retrait approuvé', 'Votre retrait de 25,000 FCFA a été traité et sera crédité sous 24h', 1, '2025-12-02 09:31:21', 'fas fa-check-circle', 'text-green-600', 'bg-green-100', '?page=transactions', 'Voir la transaction'),
(17, 2, 'referral', 'Nouveau filleul', 'Marie Konaté s\'est inscrit avec votre code de parrainage', 1, '2025-12-02 08:31:21', 'fas fa-user-plus', 'text-blue-600', 'bg-blue-100', '?page=parrainage', 'Voir mes filleuls'),
(18, 2, 'video', 'Vidéos disponibles', '5 nouvelles vidéos rémunérées sont disponibles aujourd\'hui', 1, '2025-12-02 07:31:21', 'fas fa-video', 'text-purple-600', 'bg-purple-100', '?page=videos', 'Regarder maintenant'),
(19, 2, 'promotion', 'Offre spéciale', 'Investissez 50,000 FCFA et obtenez 10% de bonus supplémentaire', 1, '2025-12-02 06:31:21', 'fas fa-gift', 'text-red-600', 'bg-red-100', '?page=investissement', 'Profiter de l\'offre'),
(20, 2, 'security', 'Connexion détectée', 'Une nouvelle connexion a été détectée depuis un appareil Windows', 1, '2025-12-02 05:31:21', 'fas fa-shield-alt', 'text-orange-600', 'bg-orange-100', '?page=settings&section=securite', 'Vérifier la sécurité'),
(21, 2, 'update', 'Mise à jour disponible', 'Une nouvelle version de l\'application est disponible', 1, '2025-12-02 04:31:21', 'fas fa-sync-alt', 'text-indigo-600', 'bg-indigo-100', '#', 'Mettre à jour'),
(22, 1, 'withdrawal', 'Demande de retrait envoyée', 'Votre demande de retrait de  FCFA via  est en cours de traitement (24-48h).', 1, '2025-12-09 20:16:58', 'fas fa-download', 'text-blue-600', 'bg-blue-100', '?page=transactions', 'Suivre ma demande'),
(23, 1, 'withdrawal', 'Retrait approuvé !', 'Votre retrait de  FCFA a été approuvé et sera crédité sous 24h.', 1, '2025-12-09 20:16:58', 'fas fa-download', 'text-blue-600', 'bg-blue-100', '?page=transactions', 'Voir la transaction'),
(24, 1, 'withdrawal', 'Retrait approuvé', 'Votre retrait de 10,000.00 FCFA a été approuvé. Le virement sera effectué sous 24h.', 1, '2025-12-09 20:20:32', 'fas fa-check-circle', 'text-green-600', 'bg-green-100', '?page=transactions', 'Voir la transaction'),
(25, 1, 'withdrawal', 'Demande de retrait envoyée', 'Votre demande de retrait de  FCFA via  est en cours de traitement (24-48h).', 1, '2025-12-13 11:23:43', 'fas fa-download', 'text-blue-600', 'bg-blue-100', '?page=transactions', 'Suivre ma demande'),
(26, 1, 'withdrawal', 'Retrait approuvé !', 'Votre retrait de  FCFA a été approuvé et sera crédité sous 24h.', 1, '2025-12-13 11:23:43', 'fas fa-download', 'text-blue-600', 'bg-blue-100', '?page=transactions', 'Voir la transaction'),
(27, 1, 'withdrawal', 'Demande de retrait envoyée', 'Votre demande de retrait de  FCFA via  est en cours de traitement (24-48h).', 1, '2025-12-13 11:30:56', 'fas fa-download', 'text-blue-600', 'bg-blue-100', '?page=transactions', 'Suivre ma demande'),
(28, 1, 'withdrawal', 'Retrait approuvé !', 'Votre retrait de  FCFA a été approuvé et sera crédité sous 24h.', 1, '2025-12-13 11:30:56', 'fas fa-download', 'text-blue-600', 'bg-blue-100', '?page=transactions', 'Voir la transaction'),
(29, 1, 'withdrawal', 'Retrait approuvé', 'Votre retrait de 5,005.00 FCFA a été approuvé. Le virement sera effectué sous 24h.', 1, '2025-12-13 11:32:42', 'fas fa-check-circle', 'text-green-600', 'bg-green-100', '?page=transactions', 'Voir la transaction');

-- --------------------------------------------------------

--
-- Table structure for table `pending_transactions`
--

CREATE TABLE `pending_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `plan_id` int UNSIGNED NOT NULL,
  `montant` decimal(18,2) NOT NULL,
  `methode` enum('orange','mtn','visa','mobile_money','autre') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'orange',
  `numero_telephone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','processing','completed','failed','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `expires_at` datetime NOT NULL,
  `session_data` json DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pending_transactions`
--

INSERT INTO `pending_transactions` (`id`, `user_id`, `plan_id`, `montant`, `methode`, `numero_telephone`, `transaction_code`, `status`, `expires_at`, `session_data`, `created_at`, `updated_at`) VALUES
(7, 1, 3, 4000.00, 'mtn', '237676676120', 'TXN202512021808185716661D', 'pending', '2025-12-02 18:23:18', '{\"referrer\": \"http://localhost/invest/investissement\", \"timestamp\": 1764698898, \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36\"}', '2025-12-02 18:08:18', '2025-12-02 18:08:18'),
(8, 1, 3, 4000.00, 'orange', '237676676120', 'TXN20251202181032A3EABA5E', 'pending', '2025-12-02 18:25:32', '{\"referrer\": \"http://localhost/invest/investissement\", \"timestamp\": 1764699032, \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36\"}', '2025-12-02 18:10:32', '2025-12-02 18:10:32'),
(9, 1, 3, 4000.00, 'orange', '237676676120', 'TXN2025120218131273F038FF', 'failed', '2026-03-16 22:13:12', '{\"referrer\": \"http://localhost/invest/investissement\", \"timestamp\": 1764699192, \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36\"}', '2025-12-02 18:13:12', '2025-12-09 19:47:33'),
(10, 1, 3, 4000.00, 'orange', '237676676120', 'TXN202512022213485D4B2D11', 'completed', '2026-03-17 02:13:48', '{\"referrer\": \"http://localhost/invest/investissement\", \"timestamp\": 1764713628, \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36\"}', '2025-12-02 22:13:48', '2025-12-09 19:47:26'),
(11, 1, 3, 4000.00, 'orange', '237676676120', 'TXN20251202230325DE9C4675', 'completed', '2026-03-17 03:03:25', '{\"referrer\": \"http://localhost/invest/investissement\", \"timestamp\": 1764716605, \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36\"}', '2025-12-02 23:03:25', '2025-12-09 19:45:23'),
(12, 4, 3, 4000.00, 'orange', '237651170075', 'TXN20251210192924E1B6A2E4', 'completed', '2026-03-24 23:29:24', '{\"referrer\": \"http://localhost/htdocs/investissement\", \"timestamp\": 1765394964, \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0\"}', '2025-12-10 19:29:24', '2025-12-13 11:21:38'),
(13, 1, 5, 10000.00, 'orange', '+237676676120', 'TXN202512112017546BD5480A', 'completed', '2026-03-26 00:17:54', '{\"referrer\": \"http://localhost/htdocs/investissement\", \"timestamp\": 1765484274, \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36\"}', '2025-12-11 20:17:54', '2025-12-11 21:40:34'),
(14, 1, 3, 4000.00, 'orange', '+237676676120', 'TXN20251213112606EE5E9F54', 'completed', '2026-03-27 15:26:06', '{\"referrer\": \"http://localhost/htdocs/investissement\", \"timestamp\": 1765625166, \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36\"}', '2025-12-13 11:26:06', '2025-12-13 11:26:26'),
(15, 1, 3, 4000.00, 'orange', '+237676676120', 'TXN20251213113326055B4207', 'completed', '2026-03-27 15:33:26', '{\"referrer\": \"http://localhost/htdocs/investissement\", \"timestamp\": 1765625606, \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36\"}', '2025-12-13 11:33:26', '2025-12-13 11:33:33'),
(16, 1, 3, 4000.00, 'orange', '+237676676120', 'TXN20251213113926A1A3A002', 'completed', '2026-03-27 15:39:26', '{\"referrer\": \"http://localhost/htdocs/investissement\", \"timestamp\": 1765625966, \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36\"}', '2025-12-13 11:39:26', '2025-12-13 11:39:33'),
(17, 1, 3, 4000.00, 'orange', '+237676676120', 'TXN202512131327357DE2280E', 'completed', '2026-03-27 17:27:35', '{\"referrer\": \"http://localhost/htdocs/investissement\", \"timestamp\": 1765632455, \"ip_address\": \"::1\", \"user_agent\": \"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36\"}', '2025-12-13 13:27:35', '2025-12-13 13:27:53');

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
(3, 'vip2', 4000.00, 1000.00, 30, 10, 50.00, 1, 'bonbon', NULL, '2025-12-02 18:43:31', '2025-12-02 18:43:31'),
(5, 'junior1', 10000.00, 2000.00, 20, 20, 20.00, 1, 'bon bon', NULL, '2025-12-09 19:50:19', '2025-12-09 19:50:19');

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
(1, 2, 3, 500.00, 0, '2025-12-02 14:56:14', NULL);

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

--
-- Dumping data for table `roi_history`
--

INSERT INTO `roi_history` (`id`, `user_id`, `user_plan_id`, `montant`, `date_versement`, `note`) VALUES
(1, 1, 2, 1000.00, '2025-12-09 15:53:27', ''),
(2, 1, 2, 1000.00, '2025-12-09 19:29:09', ''),
(3, 1, 2, 1000.00, '2025-12-09 19:36:50', ''),
(11, 1, 2, 1000.00, '2025-12-11 09:06:04', 'ROI Automatique - 2025-12-11 - Plan: vip2'),
(12, 1, 3, 1000.00, '2025-12-11 09:06:04', 'ROI Automatique - 2025-12-11 - Plan: vip2'),
(13, 1, 4, 1000.00, '2025-12-11 09:06:04', 'ROI Automatique - 2025-12-11 - Plan: vip2'),
(14, 1, 2, 1000.00, '2025-12-13 11:19:44', 'ROI Manuel - 2025-12-13 - Plan: vip2 - Validé par Admin'),
(15, 1, 3, 1000.00, '2025-12-13 11:19:44', 'ROI Manuel - 2025-12-13 - Plan: vip2 - Validé par Admin'),
(16, 1, 4, 1000.00, '2025-12-13 11:19:44', 'ROI Manuel - 2025-12-13 - Plan: vip2 - Validé par Admin'),
(17, 1, 14, 2000.00, '2025-12-13 11:19:44', 'ROI Manuel - 2025-12-13 - Plan: junior1 - Validé par Admin'),
(18, 4, 15, 1000.00, '2025-12-13 11:21:54', 'ROI Manuel - 2025-12-13 - Plan: vip2 - Validé par Admin'),
(19, 1, 16, 1000.00, '2025-12-13 11:27:06', 'ROI Manuel - 2025-12-13 - Plan: vip2 - Validé par Admin'),
(20, 1, 17, 1000.00, '2025-12-13 23:12:57', 'ROI Manuel - 2025-12-13 - Plan: vip2 - Validé par Admin'),
(21, 1, 18, 1000.00, '2025-12-13 23:12:57', 'ROI Manuel - 2025-12-13 - Plan: vip2 - Validé par Admin'),
(22, 1, 19, 1000.00, '2025-12-13 23:12:57', 'ROI Manuel - 2025-12-13 - Plan: vip2 - Validé par Admin');

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
  `statut` enum('attente','success','failed','annule') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'attente',
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `type`, `source`, `montant`, `methode`, `statut`, `reference`, `note`, `created_at`, `updated_at`) VALUES
(1, 2, 'depot', 'autre', 500.00, 'autre', 'success', 'ADJUST_692ede68462d3', 'Ajustement administratif: + 500 FCFA', '2025-12-02 13:41:12', '2025-12-02 13:41:12'),
(2, 1, 'depot', 'investissement', 4000.00, 'orange', 'success', 'TXN20251202230325DE9C4675', 'Investissement validé - Plan: vip2', '2025-12-09 19:45:23', '2025-12-09 19:45:23'),
(3, 1, 'depot', 'investissement', 4000.00, 'orange', 'success', 'TXN202512022213485D4B2D11', 'Investissement validé - Plan: vip2', '2025-12-09 19:47:26', '2025-12-09 19:47:26'),
(4, 1, 'retrait', 'investissement', 10000.00, 'orange', 'success', 'RET17653114179070', 'Retrait via orange - Numéro modifié pour premier retrait | Approuvé par admin', '2025-12-09 20:16:57', '2025-12-09 20:20:32'),
(11, 1, 'gain', 'investissement', 1000.00, 'systeme', 'success', 'ROI-2-20251211', 'Gain journalier : vip2', '2025-12-11 09:06:04', '2025-12-11 09:06:04'),
(12, 1, 'gain', 'investissement', 1000.00, 'systeme', 'success', 'ROI-3-20251211', 'Gain journalier : vip2', '2025-12-11 09:06:04', '2025-12-11 09:06:04'),
(13, 1, 'gain', 'investissement', 1000.00, 'systeme', 'success', 'ROI-4-20251211', 'Gain journalier : vip2', '2025-12-11 09:06:04', '2025-12-11 09:06:04'),
(17, 1, 'depot', 'investissement', 10000.00, 'orange', 'success', 'TXN202512112017546BD5480A', 'Investissement validé - Plan: junior1', '2025-12-11 21:40:34', '2025-12-11 21:40:34'),
(18, 1, 'bonus', 'systeme', 5.00, 'auto', 'success', 'BNS-1765489234-623', 'Bonus de validation', '2025-12-11 21:40:34', '2025-12-11 21:40:34'),
(19, 1, 'gain', 'investissement', 1000.00, 'systeme', 'success', 'ROI-2-20251213', 'Gain journalier : vip2 (Validation manuelle)', '2025-12-13 11:19:44', '2025-12-13 11:19:44'),
(20, 1, 'gain', 'investissement', 1000.00, 'systeme', 'success', 'ROI-3-20251213', 'Gain journalier : vip2 (Validation manuelle)', '2025-12-13 11:19:44', '2025-12-13 11:19:44'),
(21, 1, 'gain', 'investissement', 1000.00, 'systeme', 'success', 'ROI-4-20251213', 'Gain journalier : vip2 (Validation manuelle)', '2025-12-13 11:19:44', '2025-12-13 11:19:44'),
(22, 1, 'gain', 'investissement', 2000.00, 'systeme', 'success', 'ROI-14-20251213', 'Gain journalier : junior1 (Validation manuelle)', '2025-12-13 11:19:44', '2025-12-13 11:19:44'),
(23, 4, 'depot', 'investissement', 4000.00, 'orange', 'success', 'TXN20251210192924E1B6A2E4', 'Investissement validé - Plan: vip2', '2025-12-13 11:21:38', '2025-12-13 11:21:38'),
(24, 4, 'bonus', 'systeme', 5.00, 'auto', 'success', 'BNS-1765624898-602', 'Bonus de validation', '2025-12-13 11:21:38', '2025-12-13 11:21:38'),
(25, 4, 'gain', 'investissement', 1000.00, 'systeme', 'success', 'ROI-15-20251213', 'Gain journalier : vip2 (Validation manuelle)', '2025-12-13 11:21:54', '2025-12-13 11:21:54'),
(26, 1, 'retrait', 'investissement', 9005.00, 'orange', 'attente', 'RET17656250213106', 'Retrait via orange - transaction normal', '2025-12-13 11:23:41', '2025-12-13 11:23:41'),
(27, 1, 'depot', 'investissement', 4000.00, 'orange', 'success', 'TXN20251213112606EE5E9F54', 'Investissement validé - Plan: vip2', '2025-12-13 11:26:26', '2025-12-13 11:26:26'),
(28, 1, 'bonus', 'systeme', 5.00, 'auto', 'success', 'BNS-1765625186-389', 'Bonus de validation', '2025-12-13 11:26:26', '2025-12-13 11:26:26'),
(29, 1, 'gain', 'investissement', 1000.00, 'systeme', 'success', 'ROI-16-20251213', 'Gain journalier : vip2 (Validation manuelle)', '2025-12-13 11:27:06', '2025-12-13 11:27:06'),
(30, 1, 'retrait', 'investissement', 5005.00, 'orange', 'success', 'RET17656254562719', 'Retrait via orange - transaction normal | Approuvé par admin', '2025-12-13 11:30:56', '2025-12-13 11:32:42'),
(31, 1, 'depot', 'investissement', 4000.00, 'orange', 'success', 'TXN20251213113326055B4207', 'Investissement validé - Plan: vip2', '2025-12-13 11:33:33', '2025-12-13 11:33:33'),
(32, 1, 'bonus', 'systeme', 200.00, 'auto', 'success', 'BNS-1765625613-631', 'Bonus de validation', '2025-12-13 11:33:33', '2025-12-13 11:33:33'),
(33, 1, 'depot', 'investissement', 4000.00, 'orange', 'success', 'TXN20251213113926A1A3A002', 'Investissement validé - Plan: vip2', '2025-12-13 11:39:33', '2025-12-13 11:39:33'),
(34, 1, 'bonus', 'systeme', 200.00, 'auto', 'success', 'BNS-1765625973-592', 'Bonus de validation', '2025-12-13 11:39:33', '2025-12-13 11:39:33'),
(35, 1, 'depot', 'investissement', 4000.00, 'orange', 'success', 'TXN202512131327357DE2280E', 'Investissement validé - Plan: vip2', '2025-12-13 13:27:53', '2025-12-13 13:27:53'),
(36, 1, 'bonus', 'systeme', 200.00, 'auto', 'success', 'BNS-1765632473-388', 'Bonus de validation', '2025-12-13 13:27:54', '2025-12-13 13:27:54'),
(37, 1, 'gain', 'investissement', 1000.00, 'systeme', 'success', 'ROI-17-20251213', 'Gain journalier : vip2 (Validation manuelle)', '2025-12-13 23:12:57', '2025-12-13 23:12:57'),
(38, 1, 'gain', 'investissement', 1000.00, 'systeme', 'success', 'ROI-18-20251213', 'Gain journalier : vip2 (Validation manuelle)', '2025-12-13 23:12:57', '2025-12-13 23:12:57'),
(39, 1, 'gain', 'investissement', 1000.00, 'systeme', 'success', 'ROI-19-20251213', 'Gain journalier : vip2 (Validation manuelle)', '2025-12-13 23:12:57', '2025-12-13 23:12:57');

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
(1, 'Siebi', 'Nelson', 'nelsonsiebie237@gmail.com', '+237676676120', '$2y$10$OmJu5si1awK43arZ9E/Mr.5yY9AsklTezPm6V0X3mZohkbfeIDIGi', 'IZYNS4562', NULL, 'active', '2025-12-01 17:51:21', '2025-12-13 11:11:55', 1, 1, 1, 1, 0, 1, 60, NULL, NULL, '2025-12-13 11:11:55'),
(2, 'Siebi', 'Nelson', 'nelsonsiebi237@gmail.com', '+237676676120', '$2y$10$C8L8pT5JtxIlpPI7hiLmnuBP1wb5VHZUgv5amp30YMQsrNIc8N7TS', 'IZYNS6208', NULL, 'active', '2025-12-02 10:11:23', '2025-12-02 10:11:23', 1, 0, 1, 1, 0, 1, 60, NULL, NULL, NULL),
(3, 'junior1', 'NelsFon', 'nelsonsiebi@gmail.com', '+237 676 67 61 20', '$2y$10$vupgVJq.g/E6Yo3d6WuTW.KJ9zP1Cv9kOaNydVoL7EyFBy4NYadXG', 'IZYNJ2AE5B1', 2, 'active', '2025-12-02 14:56:14', '2025-12-02 14:56:14', 1, 0, 1, 1, 0, 1, 60, NULL, NULL, NULL),
(4, 'john', 'jony', 'john@gmail.com', '+237 651 17 00 75', '$2y$10$2s3/peVKP48SpEnZIkJEuOmNAC.p2EcZ5EAkph6LsbXf5ZtVZAKCG', 'IZYJJ3C406F', NULL, 'active', '2025-12-10 19:17:43', '2025-12-10 19:17:43', 1, 0, 1, 1, 0, 1, 60, NULL, NULL, NULL);

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

--
-- Dumping data for table `user_plans`
--

INSERT INTO `user_plans` (`id`, `user_id`, `plan_id`, `montant_investi`, `date_debut`, `date_fin`, `statut`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 4000.00, '2025-12-02 22:52:31', '2025-12-02 23:14:49', 'annule', '2025-12-02 22:52:31', '2025-12-02 23:14:49'),
(2, 1, 3, 4000.00, '2025-12-02 22:58:00', '2025-12-13 11:23:42', 'annule', '2025-12-02 22:58:00', '2025-12-13 11:23:42'),
(3, 1, 3, 4000.00, '2025-12-09 19:45:23', '2025-12-13 11:23:42', 'annule', '2025-12-09 19:45:23', '2025-12-13 11:23:42'),
(4, 1, 3, 4000.00, '2025-12-09 19:47:26', '2025-12-13 11:23:42', 'annule', '2025-12-09 19:47:26', '2025-12-13 11:23:42'),
(14, 1, 5, 10000.00, '2025-12-11 21:40:34', '2025-12-13 11:23:42', 'annule', '2025-12-11 21:40:34', '2025-12-13 11:23:42'),
(15, 4, 3, 4000.00, '2025-12-13 11:21:38', '2026-01-12 11:21:38', 'active', '2025-12-13 11:21:38', '2025-12-13 11:21:38'),
(16, 1, 3, 4000.00, '2025-12-13 11:26:26', '2025-12-13 11:30:56', 'annule', '2025-12-13 11:26:26', '2025-12-13 11:30:56'),
(17, 1, 3, 4000.00, '2025-12-13 11:33:33', '2026-01-12 11:33:33', 'active', '2025-12-13 11:33:33', '2025-12-13 11:33:33'),
(18, 1, 3, 4000.00, '2025-12-13 11:39:33', '2026-01-12 11:39:33', 'active', '2025-12-13 11:39:33', '2025-12-13 11:39:33'),
(19, 1, 3, 4000.00, '2025-12-13 13:27:53', '2026-01-12 13:27:53', 'active', '2025-12-13 13:27:53', '2025-12-13 13:27:53');

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
(1, 6595.00, 300.00, 0.00, 39015.00, 0.00, 0.00, 34000.00, '2025-12-01 17:51:21', '2025-12-13 23:12:57'),
(2, 500.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2025-12-02 10:11:23', '2025-12-02 13:41:12'),
(3, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2025-12-02 14:56:14', '2025-12-02 14:56:14'),
(4, 5005.00, 0.00, 0.00, 0.00, 0.00, 0.00, 4000.00, '2025-12-10 19:17:43', '2025-12-13 11:21:54');

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_admin` (`admin_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_created` (`created_at`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `idx_pending_transactions_user` (`user_id`),
  ADD KEY `idx_pending_transactions_plan` (`plan_id`),
  ADD KEY `idx_pending_transactions_status` (`status`),
  ADD KEY `idx_pending_transactions_expires` (`expires_at`);

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `pending_transactions`
--
ALTER TABLE `pending_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roi_history`
--
ALTER TABLE `roi_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_plans`
--
ALTER TABLE `user_plans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `pending_transactions`
--
ALTER TABLE `pending_transactions`
  ADD CONSTRAINT `fk_pending_transactions_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pending_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_user_plans_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT,
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
