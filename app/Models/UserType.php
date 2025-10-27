<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Helpers\TenantDataHelper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    use HasFactory;
    protected $table = 'user_type';
    protected $fillable = [
        'id',
        'name',
    ];
    
    public function users() {
        return $this->hasMany(User::class,'type_id');
    }

    /**
     * Boot method to clear cache on model events
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($userType) {
            TenantDataHelper::clearCacheOnUpdate();
        });

        static::deleted(function ($userType) {
            TenantDataHelper::clearCacheOnUpdate();
        });
    }
}