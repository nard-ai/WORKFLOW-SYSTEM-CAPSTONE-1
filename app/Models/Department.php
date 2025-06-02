<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'tb_department';
    protected $primaryKey = 'department_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dept_name',
        'category',
        'dept_code',
    ];

    // Define relationship back to User if needed (e.g., one department has many users)
    // public function users()
    // {
    //     return $this->hasMany(User::class, 'department_id', 'department_id');
    // }
}
