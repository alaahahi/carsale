-- CENTRAL restore: domains + tenants + tenant_database_configs ONLY
-- Target DB: intellij_2025kamo (MAIN/central) — do NOT import into tenant DBs
-- phpMyAdmin: select intellij_2025kamo → Import this file
-- CLI: mysql -u USER -p intellij_2025kamo < restore_central_tenancy_from_2025kamo.sql
-- WARNING: tenant subdomain 2025kamo points database_name=intellij_2025kamo (same as central).
-- Never run tenants:repair-db --all / repair on 2025kamo — it drops central domains/tenants.
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;

-- -------- tenants --------
DROP TABLE IF EXISTS `domains`;
DROP TABLE IF EXISTS `tenant_database_configs`;
DROP TABLE IF EXISTS `tenants`;
CREATE TABLE `tenants` (
  `id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `subscription_plan` varchar(255) NOT NULL DEFAULT 'basic',
  `subscription_expires_at` timestamp NULL DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `tenants` (`id`,`created_at`,`updated_at`,`data`,`name`,`domain`,`email`,`phone`,`address`,`status`,`subscription_plan`,`subscription_expires_at`,`settings`) VALUES
('3b2e46e1-74ed-4a3e-865e-5838df96f0f3','2025-09-17 09:58:35','2025-09-17 09:58:35','{}','كامو خاص',NULL,'a@l.com','07511077812','erbil','active','basic','2027-01-01 00:00:00',NULL),
('5349a467-3af6-4689-a5e5-22f55b4d4795','2026-06-30 15:31:53','2026-07-15 15:47:05','{}','sarwan',NULL,'sarwan.salambussnise@gmail.com','00964750169964','اربيل','active','basic','2027-07-01 00:00:00',NULL),
('630ac488-1380-41b2-a4b4-91e18af50eb2','2025-09-13 09:14:47','2025-09-13 09:14:47','{}','كامو 2025',NULL,'a@c.com','12345678','اربيل','active','basic','2026-12-31 00:00:00',NULL),
('8b36c8d8-f869-48fa-9bce-5beaca126432','2025-09-13 13:53:24','2025-09-13 13:53:24','{}','عمر و كامو',NULL,'a@m.com','07511077812','erbil','active','basic','2027-12-31 00:00:00',NULL),
('9569edad-1fc1-4a3d-844a-9eb4a26e098e','2025-12-24 17:07:25','2025-12-24 17:07:25','{}','2024kamo',NULL,'admin@gmail.com','07511077812','56','active','basic','2026-12-31 00:00:00',NULL),
('95a215b7-b269-4f22-a3b1-40297687ec62','2025-10-27 07:21:48','2025-10-27 07:25:03','{}','hrq',NULL,'hrq@gmail.com','07511077812','erbil','active','basic','2026-10-28 00:00:00',NULL),
('f62540d7-79b6-4b1d-81d2-b279e200ce59','2026-06-14 17:56:21','2026-06-14 17:56:21','{}','كامو فريدون',NULL,'a@f-kaml.intellij-app.com','07511077812','erbil','active','basic',NULL,NULL);
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenants_domain_unique` (`domain`);

-- -------- domains --------
DROP TABLE IF EXISTS `domains`;
CREATE TABLE `domains` (
  `id` int(10) UNSIGNED NOT NULL,
  `domain` varchar(255) NOT NULL,
  `tenant_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `domains` (`id`, `domain`, `tenant_id`, `created_at`, `updated_at`) VALUES
(14, '2025kamo.aindubaico.com', '630ac488-1380-41b2-a4b4-91e18af50eb2', '2025-09-13 09:14:47', '2025-09-13 09:14:47'),
(15, 'omar-kaml.aindubaico.com', '8b36c8d8-f869-48fa-9bce-5beaca126432', '2025-09-13 13:53:24', '2025-09-13 13:53:24'),
(16, 'kamo.aindubaico.com', '3b2e46e1-74ed-4a3e-865e-5838df96f0f3', '2025-09-17 09:58:35', '2025-09-17 09:58:35'),
(17, 'hrq.aindubaico.com', '95a215b7-b269-4f22-a3b1-40297687ec62', '2025-10-27 07:21:48', '2025-10-27 07:25:03'),
(18, '2024kamo.aindubaico.com', '9569edad-1fc1-4a3d-844a-9eb4a26e098e', '2025-12-24 17:07:25', '2025-12-24 17:07:25'),
(19, 'f-kaml.intellij-app.com', 'f62540d7-79b6-4b1d-81d2-b279e200ce59', '2026-06-14 17:56:21', '2026-06-14 17:56:21'),
(20, 'sarwan.intellij-app.com', '5349a467-3af6-4689-a5e5-22f55b4d4795', '2026-06-30 15:31:53', '2026-06-30 15:31:53');
ALTER TABLE `domains`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domains_domain_unique` (`domain`),
  ADD KEY `domains_tenant_id_foreign` (`tenant_id`);
ALTER TABLE `domains`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

-- -------- tenant_database_configs --------
DROP TABLE IF EXISTS `tenant_database_configs`;
CREATE TABLE `tenant_database_configs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` varchar(255) DEFAULT NULL,
  `subdomain` varchar(255) NOT NULL,
  `driver` varchar(255) NOT NULL DEFAULT 'mysql',
  `host` varchar(255) NOT NULL,
  `port` varchar(255) NOT NULL DEFAULT '3306',
  `database_name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `charset` varchar(255) NOT NULL DEFAULT 'utf8mb4',
  `collation` varchar(255) NOT NULL DEFAULT 'utf8mb4_unicode_ci',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `tenant_database_configs` (`id`, `tenant_id`, `subdomain`, `driver`, `host`, `port`, `database_name`, `username`, `password`, `charset`, `collation`, `is_active`, `description`, `created_at`, `updated_at`) VALUES
(2, '630ac488-1380-41b2-a4b4-91e18af50eb2', '2025kamo', 'mysql', '127.0.0.1', '3306', 'intellij_2025kamo', 'intellij_kamoo', 'ms{dPb9zinMUC~eE', 'utf8mb4', 'utf8mb4_unicode_ci', 1, 'إعداد قاعدة البيانات للمستأجر: كامو 2025', '2025-09-13 09:16:00', '2025-09-13 09:16:00'),
(3, '8b36c8d8-f869-48fa-9bce-5beaca126432', 'omar-kaml', 'mysql', '127.0.0.1', '3306', 'intellij_omar', 'intellij_omar', 'q9qyIW2Zbju@', 'utf8mb4', 'utf8mb4_unicode_ci', 1, 'إعداد قاعدة البيانات للمستأجر: عمر و كامو', '2025-09-13 13:56:28', '2025-09-13 13:56:28'),
(4, '3b2e46e1-74ed-4a3e-865e-5838df96f0f3', 'kamo', 'mysql', '127.0.0.1', '3306', 'intellij_kamo', 'intellij_kamo', 'GA26Ur!dbM~hnVpj', 'utf8mb4', 'utf8mb4_unicode_ci', 1, 'إعداد قاعدة البيانات للمستأجر: كامو خاص', '2025-09-17 09:59:38', '2025-09-17 09:59:38'),
(5, '95a215b7-b269-4f22-a3b1-40297687ec62', 'https://hrq.aindubaico.com', 'mysql', '127.0.0.1', '3306', 'intellij_hrq', 'intellij_hrq', '1%O;$3JQb&8X', 'utf8mb4', 'utf8mb4_unicode_ci', 1, 'إعداد قاعدة البيانات للمستأجر: hrq', '2025-10-27 07:22:57', '2025-10-27 07:24:39'),
(6, '9569edad-1fc1-4a3d-844a-9eb4a26e098e', '2024kamo', 'mysql', '127.0.0.1', '3306', 'intellij_2024kamo', 'intellij_2024kamo', ']]qeu?ax75CU', 'utf8mb4', 'utf8mb4_unicode_ci', 1, 'إعداد قاعدة البيانات للمستأجر: 2024kamo', '2025-12-24 17:09:03', '2025-12-24 17:09:03'),
(7, 'f62540d7-79b6-4b1d-81d2-b279e200ce59', 'f-kaml', 'mysql', '127.0.0.1', '3306', 'intellij_f', 'intellij_f', 'uc~JXZ28wr;4Q^cc', 'utf8mb4', 'utf8mb4_unicode_ci', 1, 'إعداد قاعدة البيانات للمستأجر: كامو فريدون', '2026-06-14 17:57:15', '2026-06-14 17:57:15'),
(8, '5349a467-3af6-4689-a5e5-22f55b4d4795', 'sarwan', 'mysql', '127.0.0.1', '3306', 'intellij_sarwan', 'intellij_sarwan', 'Alaa.hahe@1', 'utf8mb4', 'utf8mb4_unicode_ci', 1, 'إعداد قاعدة البيانات للمستأجر: sarwan', '2026-06-30 15:32:45', '2026-07-15 15:47:05');
ALTER TABLE `tenant_database_configs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenant_database_configs_subdomain_unique` (`subdomain`),
  ADD KEY `tenant_database_configs_subdomain_is_active_index` (`subdomain`,`is_active`),
  ADD KEY `tenant_database_configs_tenant_id_foreign` (`tenant_id`);
ALTER TABLE `tenant_database_configs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
ALTER TABLE `tenant_database_configs`
  ADD CONSTRAINT `tenant_database_configs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS=1;