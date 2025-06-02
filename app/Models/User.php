<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tb_account';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'accnt_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'Emp_No',
        'department_id',
        'position',
        'accessRole',
        'status',
        // Ensure created_at and updated_at are not in fillable if relying on Eloquent defaults
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        // 'remember_token', // tb_account doesn't have remember_token by default
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 'email_verified_at' => 'datetime', // tb_account doesn't have email_verified_at
            'password' => 'hashed',
        ];
    }

    /**
     * Find the user by their username.
     *
     * @param  string  $username
     * @return \App\Models\User|null
     */
    public function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Get the department associated with the user.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function employeeInfo(): BelongsTo
    {
        return $this->belongsTo(EmployeeInfo::class, 'Emp_No', 'Emp_No');
    }

    // Define relationships if needed, for example:
    // public function employee()
    // {
    //     return $this->belongsTo(EmployeeInfo::class, 'Emp_No', 'Emp_No');
    // }
}
