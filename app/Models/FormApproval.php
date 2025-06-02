<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormApproval extends Model
{
    use HasFactory;

    protected $table = 'form_approvals';
    protected $primaryKey = 'approval_id';

    protected $fillable = [
        'form_id',
        'approver_id',
        'action',
        'comments',
        'action_date',
        'signature_name',
        'signature_data',
    ];

    protected $casts = [
        'action_date' => 'datetime',
    ];

    public function formRequest(): BelongsTo
    {
        return $this->belongsTo(FormRequest::class, 'form_id', 'form_id');
    }

    public function approver(): BelongsTo
    {
        // Assuming your User model is App\Models\User and its primary key is accnt_id
        return $this->belongsTo(User::class, 'approver_id', 'accnt_id');
    }
}
