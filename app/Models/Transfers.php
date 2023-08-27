<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfers extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'transfers';
    protected $fillable = [
        'id',
        'user_id',
        'amount',
        'currency',
        'note',
        'created_at',
        'updated_at'
    ];

    public function morphed()
    {
        return $this->morphTo();
    }

    protected $dates = ['deleted_at']; // Define the deleted_at column as a date

  }