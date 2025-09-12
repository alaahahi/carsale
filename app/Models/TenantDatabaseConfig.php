<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantDatabaseConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'subdomain',
        'driver',
        'host',
        'port',
        'database_name',
        'username',
        'password',
        'charset',
        'collation',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'port' => 'integer',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * العلاقة مع المستأجر
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * الحصول على معلومات الاتصال
     */
    public function getConnectionInfo(): array
    {
        return [
            'driver' => $this->driver,
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database_name,
            'username' => $this->username,
            'password' => $this->password,
            'charset' => $this->charset,
            'collation' => $this->collation,
        ];
    }

    /**
     * التحقق من صحة الاتصال
     */
    public function testConnection(): bool
    {
        try {
            $connectionInfo = $this->getConnectionInfo();
            $connectionName = 'test_connection_' . $this->id;
            
            \Log::info('Setting up test connection', [
                'config_id' => $this->id,
                'connection_name' => $connectionName,
                'host' => $this->host,
                'database' => $this->database_name,
                'driver' => $this->driver
            ]);
            
            config([
                "database.connections.{$connectionName}" => $connectionInfo
            ]);
            
            $connection = \DB::connection($connectionName);
            $pdo = $connection->getPdo();
            
            \Log::info('Test connection successful', [
                'config_id' => $this->id,
                'connection_name' => $connectionName
            ]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Test connection failed', [
                'config_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * الحصول على معلومات الاتصال مع إخفاء كلمة المرور
     */
    public function getSafeConnectionInfo(): array
    {
        $info = $this->getConnectionInfo();
        $info['password'] = '****';
        return $info;
    }

    /**
     * البحث عن إعدادات قاعدة البيانات بالـ subdomain
     */
    public static function findBySubdomain(string $subdomain): ?self
    {
        return self::where('subdomain', $subdomain)
                   ->where('is_active', true)
                   ->first();
    }

    /**
     * الحصول على جميع الإعدادات النشطة
     */
    public static function getActiveConfigs()
    {
        return self::where('is_active', true)
                   ->with('tenant')
                   ->get();
    }
}