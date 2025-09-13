-- ========================================
-- SQL Script لحل مشكلة Foreign Key في transactions
-- تشغيل هذا السكريبت في phpMyAdmin أو MySQL Workbench
-- ========================================

-- 1. إضافة حقل show_wallet إلى جدول users (إذا لم يكن موجوداً)
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `show_wallet` TINYINT(1) NOT NULL DEFAULT 0;

-- 2. إنشاء wallets للمستخدمين الذين لا يملكون wallets
INSERT INTO `wallets` (`user_id`, `balance`, `created_at`, `updated_at`)
SELECT 
    u.id,
    0,
    NOW(),
    NOW()
FROM `users` u
LEFT JOIN `wallets` w ON u.id = w.user_id
WHERE w.user_id IS NULL;

-- 3. إنشاء حسابات المحاسبة المطلوبة إذا لم تكن موجودة
-- حساب الخرج (out@account.com)
INSERT IGNORE INTO `users` (`name`, `email`, `password`, `type_id`, `created_at`, `updated_at`)
SELECT 
    'حساب الخرج',
    'out@account.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    (SELECT id FROM user_type WHERE name = 'account' LIMIT 1),
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'out@account.com');

-- حساب الدخل (in@account.com)
INSERT IGNORE INTO `users` (`name`, `email`, `password`, `type_id`, `created_at`, `updated_at`)
SELECT 
    'حساب الدخل',
    'in@account.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    (SELECT id FROM user_type WHERE name = 'account' LIMIT 1),
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'in@account.com');

-- حساب التحويلات (transfers@account.com)
INSERT IGNORE INTO `users` (`name`, `email`, `password`, `type_id`, `created_at`, `updated_at`)
SELECT 
    'حساب التحويلات',
    'transfers@account.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    (SELECT id FROM user_type WHERE name = 'account' LIMIT 1),
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'transfers@account.com');

-- حساب الدين (debt@account.com) - تم إزالته لتبسيط النظام
-- سيتم استخدام out@account.com لجميع المعاملات الخارجية

-- 4. إنشاء wallets للحسابات المحاسبية إذا لم تكن موجودة
INSERT INTO `wallets` (`user_id`, `balance`, `created_at`, `updated_at`)
SELECT 
    u.id,
    0,
    NOW(),
    NOW()
FROM `users` u
WHERE u.email IN ('out@account.com', 'in@account.com', 'transfers@account.com')
AND NOT EXISTS (
    SELECT 1 FROM `wallets` w WHERE w.user_id = u.id
);

-- 5. تفعيل القاسة للمديرين (اختياري)
UPDATE `users` SET `show_wallet` = 1 WHERE `email` = 'admin@admin.com';
UPDATE `users` SET `show_wallet` = 1 WHERE `type_id` = (SELECT id FROM user_type WHERE name = 'admin' LIMIT 1);

-- ========================================
-- التحقق من النتائج
-- ========================================

-- عرض إحصائيات المستخدمين والـ wallets
SELECT 
    COUNT(*) as total_users,
    SUM(show_wallet) as users_with_wallet_permission,
    (SELECT COUNT(*) FROM wallets) as total_wallets,
    (SELECT COUNT(*) FROM users WHERE email LIKE '%@account.com') as account_users
FROM users;

-- عرض الحسابات المحاسبية
SELECT 
    u.id,
    u.name,
    u.email,
    w.id as wallet_id,
    w.balance
FROM users u
LEFT JOIN wallets w ON u.id = w.user_id
WHERE u.email IN ('out@account.com', 'in@account.com', 'transfers@account.com');

-- التحقق من وجود الحقل الجديد
DESCRIBE users;
