<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IomDetail extends Model
{
    use HasFactory;

    protected $table = 'iom_details';
    protected $primaryKey = 'form_id'; // Since it's a 1-to-1 relationship using form_id
    public $incrementing = false; // Because form_id is not auto-incrementing here, it comes from form_requests
    public $timestamps = false; // As we decided not to have timestamps in the migration for this table

    protected $fillable = [
        'form_id',
        'date_needed',
        'priority',
        'purpose',
        'body',
    ];

    protected $casts = [
        'date_needed' => 'date',
    ];

    public function formRequest(): BelongsTo
    {
        return $this->belongsTo(FormRequest::class, 'form_id', 'form_id');
    }
}
