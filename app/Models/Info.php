<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Info extends Model
{
    use HasFactory;
   // use Searchable;
    protected $table = 'info';
    protected $fillable = [
        'id',
        'phone',
        'fname',
        'lname',
        'sex',
        'link',
        'p1',
        'username',
        'fullname',
        'work',
        'study',
        'email',
        'p2',
        'p3',
        'p4',
        'date1',
        'date2',
        'p5',
        'p6',
        'p7'
    ];
}
