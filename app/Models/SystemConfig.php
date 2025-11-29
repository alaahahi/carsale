<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use App\Helpers\TenantDataHelper;

class SystemConfig extends Model
{
    use HasFactory;
   // use Searchable;
    protected $table = 'system_config';
    protected $fillable = [
        'id',
        'first_title_ar',
        'first_title_kr',
        'second_title_ar',
        'second_title_kr',
        'third_title_ar',
        'third_title_kr',
        'external_merchant_ids',
    ];
    
    /**
     * Boot method to clear cache on model events
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($systemConfig) {
            TenantDataHelper::clearCacheOnUpdate();
        });

        static::deleted(function ($systemConfig) {
            TenantDataHelper::clearCacheOnUpdate();
        });
    }
}
