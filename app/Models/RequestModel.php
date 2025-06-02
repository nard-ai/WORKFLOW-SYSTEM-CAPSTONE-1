<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tb_requests';
    protected $primaryKey = 'request_id';

    protected $fillable = [
        'requester_accnt_id',
        'request_type',
        'iom_ref_no',
        'status',
        'details',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'details' => 'array', // Automatically cast the JSON details field to an array and back
    ];

    /**
     * Get the user who made the request.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_accnt_id', 'accnt_id');
    }
}
