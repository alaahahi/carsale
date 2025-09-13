-- ========================================
-- SQL Script نهائي لحل جميع المشاكل
-- تشغيل في phpMyAdmin
-- ========================================

-- 1. إنشاء أنواع المستخدمين
INSERT IGNORE INTO `user_type` (`name`, `created_at`, `updated_at`) VALUES 
('admin', NOW(), NOW()),
('seles', NOW(), NOW()),
('client', NOW(), NOW()),
('account', NOW(), NOW());

-- 2. إنشاء حسابات المحاسبة الأساسية
INSERT IGNORE INTO `users` (`name`, `email`, `password`, `type_id`, `show_wallet`, `created_at`, `updated_at`)
SELECT 'حساب الخرج', 'out@account.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
       (SELECT id FROM user_type WHERE name = 'account' LIMIT 1), 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'out@account.com');

INSERT IGNORE INTO `users` (`name`, `email`, `password`, `type_id`, `show_wallet`, `created_at`, `updated_at`)
SELECT 'حساب الدخل', 'in@account.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
       (SELECT id FROM user_type WHERE name = 'account' LIMIT 1), 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'in@account.com');

INSERT IGNORE INTO `users` (`name`, `email`, `password`, `type_id`, `show_wallet`, `created_at`, `updated_at`)
SELECT 'حساب التحويلات', 'transfers@account.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
       (SELECT id FROM user_type WHERE name = 'account' LIMIT 1), 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'transfers@account.com');

INSERT IGNORE INTO `users` (`name`, `email`, `password`, `type_id`, `show_wallet`, `created_at`, `updated_at`)
SELECT 'مدير النظام', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
       (SELECT id FROM user_type WHERE name = 'admin' LIMIT 1), 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@admin.com');

-- 3. إنشاء wallets لجميع المستخدمين الذين لا يملكون wallets
INSERT INTO `wallets` (`user_id`, `balance`, `created_at`, `updated_at`)
SELECT u.id, 0, NOW(), NOW()
FROM `users` u
WHERE NOT EXISTS (SELECT 1 FROM `wallets` w WHERE w.user_id = u.id);

-- 4. إضافة عمود show_wallet إذا لم يكن موجوداً
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `show_wallet` BOOLEAN DEFAULT FALSE;

-- 5. تفعيل عرض المحفظة للمديرين
UPDATE `users` u 
JOIN `user_type` ut ON u.type_id = ut.id 
SET u.show_wallet = 1 
WHERE ut.name = 'admin';

-- 6. إنشاء أنواع المصروفات الأساسية
INSERT IGNORE INTO `expenses_type` (`name`, `created_at`, `updated_at`) VALUES 
('مصاريف دبي', NOW(), NOW()),
('مصاريف أربيل', NOW(), NOW()),
('شحن دبي', NOW(), NOW()),
('شحن أربيل', NOW(), NOW()),
('دفعة سيارة', NOW(), NOW());

-- 7. التحقق من النتائج
SELECT 
    'user_type' as table_name,
    COUNT(*) as count
FROM user_type
UNION ALL
SELECT 
    'accounting_users' as table_name,
    COUNT(*) as count
FROM users u
JOIN user_type ut ON u.type_id = ut.id
WHERE ut.name = 'account'
UNION ALL
SELECT 
    'wallets' as table_name,
    COUNT(*) as count
FROM wallets
UNION ALL
SELECT 
    'expenses_type' as table_name,
    COUNT(*) as count
FROM expenses_type;

-- 8. عرض تفاصيل الحسابات المحاسبية
SELECT 
    u.id as user_id,
    u.name,
    u.email,
    ut.name as user_type,
    u.show_wallet,
    w.id as wallet_id,
    w.balance
FROM users u
LEFT JOIN user_type ut ON u.type_id = ut.id
LEFT JOIN wallets w ON u.id = w.user_id
WHERE ut.name IN ('admin', 'account')
ORDER BY ut.name, u.email;
