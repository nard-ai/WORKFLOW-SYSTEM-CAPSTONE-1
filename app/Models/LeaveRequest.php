<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $table = 'tb_leave_requests';
    protected $primaryKey = 'leave_request_id';

    protected $fillable = [
        'requester_accnt_id',
        'leave_type',
        'date_of_leave',
        'description',
        'status',
    ];

    /**
     * Get the user who made the request.
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_accnt_id', 'accnt_id');
    }
}
