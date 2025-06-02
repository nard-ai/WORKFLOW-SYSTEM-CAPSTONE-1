<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FormRequest extends Model
{
    use HasFactory;

    protected $table = 'form_requests';
    protected $primaryKey = 'form_id';

    protected $fillable = [
        'form_type',
        'title',
        'from_department_id',
        'to_department_id',
        'requested_by',
        'current_approver_id',
        'status',
        'date_submitted',
    ];

    // If your date_submitted is a DATETIME and you want Carbon instances:
    protected $casts = [
        'date_submitted' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by', 'accnt_id');
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id', 'department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id', 'department_id');
    }

    public function currentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_approver_id', 'accnt_id');
    }

    public function iomDetails(): HasOne
    {
        return $this->hasOne(IomDetail::class, 'form_id', 'form_id');
    }

    public function leaveDetails(): HasOne
    {
        return $this->hasOne(LeaveDetail::class, 'form_id', 'form_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(FormApproval::class, 'form_id', 'form_id');
    }
}
