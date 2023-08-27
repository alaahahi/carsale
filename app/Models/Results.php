<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Results extends Model
{
    use HasFactory;
    protected $table = 'results';
    protected $fillable = [
        'id',
        'husband_b',
        'husband_hb',
        'husband_mcv',
        'husband_mch',
        'husband_hemoglobin_a',
        'husband_hemoglobin_f',
        'husband_hemoglobin_a2',
        'husband_hbs',
        'husband_hcv',
        'husband_hiv',
        'husband_tb',
        'husband_syphilis',
        'husband_tpha',
        'wife_b',
        'wife_hb',
        'wife_mcv',
        'wife_mch',
        'wife_hemoglobin_a',
        'wife_hemoglobin_f',
        'wife_hemoglobin_a2',
        'wife_hbs',
        'wife_hcv',
        'wife_hiv',
        'wife_tb',
        'wife_syphilis',
        'wife_tpha',
        'created_at',
        'updated_at',
        'user_id',
        'profile_id'
    ];
  }