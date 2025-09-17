-- ========================================
-- SQL Script لتحديث نظام الاستثمارات بالسيارات
-- تشغيل في phpMyAdmin بعد رفع الملفات
-- ========================================

-- 1. إنشاء جدول الاستثمارات (إذا لم يكن موجوداً)
CREATE TABLE IF NOT EXISTS `investments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `investment_type` enum('specific_cars') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'specific_cars',
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

-- 2. إنشاء جدول استثمارات السيارات (إذا لم يكن موجوداً)
CREATE TABLE IF NOT EXISTS `investment_cars` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `investment_id` bigint(20) UNSIGNED NOT NULL,
  `car_id` bigint(20) UNSIGNED NOT NULL,
  `invested_amount` decimal(10,2) NOT NULL,
  `percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `profit_share` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `investment_cars_investment_id_foreign` (`investment_id`),
  KEY `investment_cars_car_id_foreign` (`car_id`),
  KEY `investment_cars_car_investment_index` (`car_id`,`investment_id`),
  CONSTRAINT `investment_cars_investment_id_foreign` FOREIGN KEY (`investment_id`) REFERENCES `investments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `investment_cars_car_id_foreign` FOREIGN KEY (`car_id`) REFERENCES `car` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. إضافة عمود investment_type إلى جدول investments إذا لم يكن موجوداً
ALTER TABLE `investments` 
ADD COLUMN IF NOT EXISTS `investment_type` enum('specific_cars') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'specific_cars' AFTER `amount`;

-- 4. إضافة عمود total_cost إلى جدول car (محسوب من الأعمدة الموجودة)
ALTER TABLE `car` 
ADD COLUMN IF NOT EXISTS `total_cost` decimal(10,2) GENERATED ALWAYS AS (
    COALESCE(purchase_price, 0) + 
    COALESCE(dubai_shipping, 0) + 
    COALESCE(dubai_exp, 0) + 
    COALESCE(erbil_shipping, 0) + 
    COALESCE(erbil_exp, 0)
) STORED AFTER `erbil_exp`;

-- 5. إضافة عمود profit إلى جدول car (محسوب من الأعمدة الموجودة)
ALTER TABLE `car` 
ADD COLUMN IF NOT EXISTS `profit` decimal(10,2) GENERATED ALWAYS AS (
    COALESCE(pay_price, 0) - (
        COALESCE(purchase_price, 0) + 
        COALESCE(dubai_shipping, 0) + 
        COALESCE(dubai_exp, 0) + 
        COALESCE(erbil_shipping, 0) + 
        COALESCE(erbil_exp, 0)
    )
) STORED AFTER `total_cost`;

-- 6. إضافة أنواع المعاملات الجديدة للاستثمارات
INSERT IGNORE INTO `transactions` (`type`, `description`, `created_at`, `updated_at`) VALUES 
('investment', 'استثمار من المحفظة', NOW(), NOW()),
('investment_withdrawal', 'سحب استثمار', NOW(), NOW()),
('car_investment', 'استثمار في سيارة محددة', NOW(), NOW()),
('profit_distribution', 'توزيع ربح من بيع السيارة', NOW(), NOW());

-- 7. التحقق من البيانات
SELECT 
    'investments_table' as table_name,
    COUNT(*) as count
FROM investments
UNION ALL
SELECT 
    'investment_cars_table' as table_name,
    COUNT(*) as count
FROM investment_cars
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
WHERE status = 'active'
UNION ALL
SELECT 
    'total_car_investments' as table_name,
    COUNT(*) as count
FROM investment_cars;

-- 8. عرض المستثمرين النشطين مع تفاصيل السيارات
SELECT 
    u.name as investor_name,
    u.email,
    i.amount as total_investment,
    i.investment_type,
    i.note,
    i.created_at as investment_date,
    GROUP_CONCAT(
        CONCAT(
            'السيارة: ', c.name, 
            ' (رقم: ', c.no, ')',
            ' - المبلغ: $', ic.invested_amount,
            ' - النسبة: ', ic.percentage, '%',
            ' - الربح: $', COALESCE(ic.profit_share, 0)
        ) 
        SEPARATOR ' | '
    ) as car_details
FROM investments i
JOIN users u ON i.user_id = u.id
LEFT JOIN investment_cars ic ON i.id = ic.investment_id
LEFT JOIN car c ON ic.car_id = c.id
WHERE i.status = 'active'
GROUP BY i.id, u.name, u.email, i.amount, i.investment_type, i.note, i.created_at
ORDER BY i.amount DESC;

-- 9. عرض السيارات المستثمرة مع تفاصيل المستثمرين (باستخدام الأعمدة المحسوبة الجديدة)
SELECT 
    c.no as car_number,
    c.name as car_name,
    c.total_cost,
    c.pay_price as sale_price,
    c.profit,
    CASE 
        WHEN c.results = 0 THEN 'في المخزن'
        WHEN c.results = 1 THEN 'مباعة'
        WHEN c.results = 2 THEN 'مباعة ومكتملة'
        ELSE 'غير محدد'
    END as sale_status,
    COUNT(ic.id) as investor_count,
    SUM(ic.invested_amount) as total_invested,
    SUM(ic.profit_share) as total_profit_distributed
FROM car c
LEFT JOIN investment_cars ic ON c.id = ic.car_id
LEFT JOIN investments i ON ic.investment_id = i.id AND i.status = 'active'
GROUP BY c.id, c.no, c.name, c.total_cost, c.pay_price, c.profit, c.results
HAVING investor_count > 0
ORDER BY total_invested DESC;

-- 10. عرض السيارات المتاحة للاستثمار (غير مباعة)
SELECT 
    c.id,
    c.no as car_number,
    c.name as car_name,
    c.total_cost,
    c.pay_price as sale_price,
    c.profit,
    'متاحة للاستثمار' as status
FROM car c
WHERE c.results = 0 AND c.deleted_at IS NULL
ORDER BY c.no DESC
LIMIT 10;