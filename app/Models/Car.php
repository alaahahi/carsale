<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Car extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'car';
    protected $fillable = [
        'id',
        'no',
        'name_id',
        'company_id',
        'color_id',
        'model_id',
        'image',
        'pin',
        'price',
        'phone_number',
        'invoice_number',
        'paid_amount',
        'paid_amount_pay',
        'user_id',
        'user_purchase_id',
        'user_accepted',
        'user_rejected',
        'created_at',
        'updated_at',
        'purchase_price',
        'purchase_data',
        'pay_data',
        'pay_price',
        'note',
        'note_pay',
        'client_id',
        'results',
        'erbil_exp',
        'erbil_shipping',
        'dubai_exp',
        'dubai_shipping'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    protected $dates = ['deleted_at']; // Define the deleted_at column as a date
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function carmodel()
    {
        return $this->belongsTo(CarModel::class,'model_id');
    }
    public function name()
    {
        return $this->belongsTo(Name::class);
    }
    public function color()
    {
        return $this->belongsTo(Color::class);
    }
    public function Client()
    {
        return $this->belongsTo(User::class);
    }
        
    public function transactions()
    {
        return $this->morphMany(Transactions::class, 'morphed');
    }

    protected $appends = ['image_url'];

    /**
     * Get Added Image Attribute URL.
     *
     * @return string|null
     */
    public function getImageUrlAttribute(): array
    {

        $images = json_decode($this->image);

        if (!is_array($images)) {
            return [];
        }
    
        $imageUrls = [];
    
        foreach ($images as $image) {
            $imageUrl = url('') . "/storage/car/" . $image;
            if (Str::contains($imageUrl, '/car/car/')) {
                $imageUrl = str_replace('/car/car/', '/car/', $imageUrl);
            }
            
            $imageUrls[] = $imageUrl;
        }
    
        return $imageUrls;
    }

  }