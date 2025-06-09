<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\SignatureStyle; // Add this line

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
     * Get the signature style associated with the user.
     */
    public function signatureStyle(): BelongsTo
    {
        return $this->belongsTo(SignatureStyle::class, 'signature_style_id', 'id');
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
        // Non-approvers can't approve anything
        if ($this->accessRole !== 'Approver') {
            return false;
        }

        $permissions = $this->approverPermissions; // Fetches the related ApproverPermission record

        // VPAA: Can always 'Note' (act on 'Pending') if they are an Approver.
        // For other statuses, depends on their specific ApproverPermission record.
        if ($this->position === 'VPAA') {
            if ($status === 'Pending') {
                return true; // VPAA (as Approver) can always act on 'Pending' requests assigned to them.
            }
            // For other statuses, VPAA needs explicit permissions from ApproverPermission table
            if ($permissions) {
                return match ($status) {
                    // VPAA might handle 'In Progress' if, for example, a request is routed back to them,
                    // or if they have a dual role like acting HR Head (though not the current scenario).
                    // The seeder sets can_approve_in_progress to true for VPAA for flexibility.
                    'In Progress' => $permissions->can_approve_in_progress,
                    default => false,
                };
            }
            return false; // No permission record and status is not 'Pending', so cannot act on other statuses.
        }

        // Department Heads: Check permissions first, then fall back to general role-based ability.
        if ($this->position === 'Head') {
            if ($permissions) { // If specific permissions exist, use them
                return match ($status) {
                    'Pending' => $permissions->can_approve_pending,
                    'In Progress', 'Pending Target Department Approval' => $permissions->can_approve_in_progress,
                    default => false,
                };
            }
            // Default for Heads if no specific permission entry: can handle typical Head tasks.
            return match ($status) {
                'Pending', // e.g., Noting their own staff's request
                'In Progress', // e.g., Approving a request targeted to their department
                'Pending Target Department Approval' // Similar to 'In Progress' for Heads
                => true,
                default => false,
            };
        }

        // For other approvers (e.g., Staff with Approver role, not Head or VPAA):
        // Strictly based on their ApproverPermission record.
        if (!$permissions) {
            return false;
        }
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
