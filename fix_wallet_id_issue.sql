-- ========================================
-- SQL Script لحل مشكلة wallet_id في المعاملات
-- تشغيل فوري في phpMyAdmin
-- ========================================

-- 1. التحقق من المعاملات التي لا تحتوي على wallet_id
SELECT 
    'transactions_without_wallet' as issue_type,
    COUNT(*) as count
FROM transactions 
WHERE wallet_id IS NULL OR wallet_id = 0;

-- 2. إصلاح المعاملات التي لا تحتوي على wallet_id
-- للمعاملات من نوع user_in و user_out
UPDATE transactions t
JOIN users u ON t.morphed_id = u.id AND t.morphed_type = 'App\\Models\\User'
JOIN wallets w ON u.id = w.user_id
SET t.wallet_id = w.id
WHERE (t.wallet_id IS NULL OR t.wallet_id = 0) 
AND t.morphed_type = 'App\\Models\\User'
AND t.type IN ('user_in', 'user_out');

-- 3. إصلاح المعاملات من نوع investment
UPDATE transactions t
JOIN investments i ON t.morphed_id = i.id AND t.morphed_type = 'App\\Models\\Investment'
JOIN wallets w ON i.user_id = w.user_id
SET t.wallet_id = w.id
WHERE (t.wallet_id IS NULL OR t.wallet_id = 0) 
AND t.type IN ('investment', 'investment_withdrawal');

-- 4. إصلاح المعاملات من نوع out (شراء سيارات)
UPDATE transactions t
JOIN wallets w ON w.user_id = (
    SELECT u.id FROM users u WHERE u.email = 'out@account.com' LIMIT 1
)
SET t.wallet_id = w.id
WHERE (t.wallet_id IS NULL OR t.wallet_id = 0) 
AND t.type = 'out'
AND t.description LIKE '%شراء سيارة%';

-- 5. إصلاح المعاملات من نوع in (دفعات سيارات)
UPDATE transactions t
JOIN wallets w ON w.user_id = (
    SELECT u.id FROM users u WHERE u.email = 'in@account.com' LIMIT 1
)
SET t.wallet_id = w.id
WHERE (t.wallet_id IS NULL OR t.wallet_id = 0) 
AND t.type = 'in'
AND t.morphed_type = 'App\\Models\\Car';

-- 6. حذف المعاملات التي لا يمكن إصلاحها (بدون wallet_id صحيح)
DELETE FROM transactions 
WHERE wallet_id IS NULL OR wallet_id = 0;

-- 7. التحقق من النتيجة
SELECT 
    'fixed_transactions' as result_type,
    COUNT(*) as count
FROM transactions 
WHERE wallet_id IS NOT NULL AND wallet_id > 0;

-- 8. عرض بعض المعاملات للتحقق
SELECT 
    t.id,
    t.type,
    t.amount,
    t.description,
    t.wallet_id,
    u.name as user_name,
    u.email
FROM transactions t
LEFT JOIN wallets w ON t.wallet_id = w.id
LEFT JOIN users u ON w.user_id = u.id
ORDER BY t.created_at DESC
LIMIT 10;
