-- ========================================
-- SQL Script لتحديث نظام الاستثمارات
-- تشغيل في phpMyAdmin بعد رفع الملفات
-- ========================================

-- 1. إنشاء جدول الاستثمارات
CREATE TABLE IF NOT EXISTS `investments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `percentage` decimal(5,2) DEFAULT 0.00,
  `profit_share` decimal(10,2) DEFAULT 0.00,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','withdrawn') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `investments_user_id_foreign` (`user_id`),
  KEY `investments_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `investments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. إضافة أنواع المعاملات الجديدة للاستثمارات
INSERT IGNORE INTO `transactions` (`type`, `description`, `created_at`, `updated_at`) VALUES 
('investment', 'استثمار من المحفظة', NOW(), NOW()),
('investment_withdrawal', 'سحب استثمار', NOW(), NOW());

-- 3. التحقق من البيانات
SELECT 
    'investments_table' as table_name,
    COUNT(*) as count
FROM investments
UNION ALL
SELECT 
    'active_investments' as table_name,
    COUNT(*) as count
FROM investments 
WHERE status = 'active'
UNION ALL
SELECT 
    'total_investment_amount' as table_name,
    SUM(amount) as count
FROM investments 
WHERE status = 'active';

-- 4. عرض المستثمرين النشطين
SELECT 
    u.name as investor_name,
    u.email,
    i.amount,
    i.percentage,
    i.profit_share,
    i.note,
    i.created_at
FROM investments i
JOIN users u ON i.user_id = u.id
WHERE i.status = 'active'
ORDER BY i.amount DESC;
