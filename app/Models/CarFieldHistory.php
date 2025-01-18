<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CarFieldHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'field',
        'old_value',
        'updated_by',
        'description',
        'new_value',
        'user_id',
    ];

    // Relation to Car
    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    // Relation to User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
