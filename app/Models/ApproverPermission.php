<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApproverPermission extends Model
{
    protected $fillable = [
        'accnt_id',
        'can_approve_pending',
        'can_approve_in_progress',
    ];

    protected $casts = [
        'can_approve_pending' => 'boolean',
        'can_approve_in_progress' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accnt_id', 'accnt_id');
    }
} 