<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    /**
     * Get the approver permissions for the user.
     */
    public function approverPermissions(): HasOne
    {
        return $this->hasOne(ApproverPermission::class, 'accnt_id', 'accnt_id');
    }

    /**
     * Check if the user can approve requests with a specific status.
     */
    public function canApproveStatus(string $status): bool
    {
        // Department heads can approve all statuses
        if ($this->position === 'Head') {
            return true;
        }

        // Non-approvers can't approve anything
        if ($this->accessRole !== 'Approver') {
            return false;
        }

        // Get the user's permissions
        $permissions = $this->approverPermissions;
        if (!$permissions) {
            return false;
        }

        // Check permissions based on status
        return match ($status) {
            'Pending' => $permissions->can_approve_pending,
            'In Progress', 'Pending Target Department Approval' => $permissions->can_approve_in_progress,
            default => false,
        };
    }

    // Define relationships if needed, for example:
    // public function employee()
    // {
    //     return $this->belongsTo(EmployeeInfo::class, 'Emp_No', 'Emp_No');
    // }
}
