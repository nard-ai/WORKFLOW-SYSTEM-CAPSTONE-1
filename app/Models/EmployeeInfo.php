<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmployeeInfo extends Model
{
    use HasFactory;

    protected $table = 'tb_employeeinfo';
    protected $primaryKey = 'Emp_No';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'Emp_No',
        'LastName',
        'FirstName',
        'MiddleName',
        'Email',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'Emp_No', 'Emp_No');
    }
} 