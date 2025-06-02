<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureStyle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'font_family',
        'preview_image'
    ];
} 