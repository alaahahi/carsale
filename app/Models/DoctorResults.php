<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorResults extends Model
{
    use HasFactory;
    protected $table = 'doctor_reslts';
    protected $fillable = [
        'id',
        'husband_talasyma',
        'husband_faqar',
        'husband_himofilya',
        'husband_al',
        'husband_dam',
        'husband_note',
        'husband_results',
        'wife_talasyma',
        'wife_faqar',
        'wife_himofilya',
        'wife_al',
        'wife_dam',
        'wife_note',
        'wife_results',
        'created_at',
        'updated_at',
        'user_id',
        'profile_id'
    ];
  }