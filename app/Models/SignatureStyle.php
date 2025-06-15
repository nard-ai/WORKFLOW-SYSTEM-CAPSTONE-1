<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignatureStyle extends Model
{
    use HasFactory;

    protected $table = 'signature_styles';

    protected $fillable = [
        'name',
        'font_family',
        'preview_image'
    ];

    /**
     * Get the user that owns the signature style.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'accnt_id');
    }
}