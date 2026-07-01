<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $table = 'supplier_payments';

    protected $fillable = [
        'supplier_id',
        'car_id',
        'amount',
        'paid_at',
        'note',
        'created_by',
    ];

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

