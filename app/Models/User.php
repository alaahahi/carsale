<?php

namespace App\Models;
use App\Models\Massage;
use App\Models\UserType;
use App\Models\Tenant;
use Illuminate\Support\Facades\Crypt;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type_id',
        'public_key',
        'publickey_receiver',
        'parent_id',
        'is_band',
        'phone',
        'device',
        'tenant_id',
        'show_wallet',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function morphed()
    {
        return $this->morphTo();
    }
    public function massage()
    {
        return $this->hasMany(Massage::class,'sender_id');
    }
    public function userType()
    {
        return $this->belongsTo(UserType::class,'type_id');
    }
    public function getParentUser() {
        return $this->belongsTo(self::class, 'parent_id');
    }
    
    public function getChildUser(){
        return $this->hasMany(self::class, 'parent_id');
    }
    public function getTokenAttribute()
    {
        return Crypt::encryptString($this->id);
    }
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    
    public function cars()
    {
        return $this->hasMany(Car::class, 'client_id');
    }
    
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    public function activeInvestments()
    {
        return $this->hasMany(Investment::class)->where('status', 'active');
    }
    
    /**
     * إنشاء wallet للمستخدم إذا لم يكن موجوداً
     */
    public function createWalletIfNotExists()
    {
        if (!$this->wallet) {
            return $this->wallet()->create([
                'balance' => 0
            ]);
        }
        return $this->wallet;
    }
    
    /**
     * الحصول على wallet المستخدم أو إنشاء واحد جديد
     */
    public function getWalletOrCreate()
    {
        return $this->wallet ?: $this->createWalletIfNotExists();
    }
}
