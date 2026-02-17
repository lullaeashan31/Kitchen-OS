<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'staff_code',
        'target_latitude',
        'target_longitude',
        'target_location_name',
        'phone',
        'email',
        'password',
        'role',
        'shift',
        'monthly_salary',
        'variable_enabled',
        'max_variable_amount',
        'weekly_off_day',
        'onboarding_status',
        'profile_photo_path',
        'is_password_changed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_password_changed' => 'boolean',
        ];
    }

    // Role Helper Methods
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::Staff;
    }

    public function canApproveRecipes(): bool
    {
        return $this->isAdmin();
    }

    public function canManageProduction(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    // Relationships
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasPermissionTo($permission)
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->permissions()->where('slug', $permission)->exists();
    }

    // Accessor for profile photo URL
    protected function profilePhotoUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => $this->profile_photo_path
            ? asset('storage/' . $this->profile_photo_path)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF'
        );
    }

    // Existing Relationships...
    public function recipes()
    {
        return $this->hasMany(Recipe::class, 'created_by');
    }

    public function approvedRecipes()
    {
        return $this->hasMany(Recipe::class, 'approved_by');
    }

    public function productionDays()
    {
        return $this->hasMany(ProductionDay::class, 'created_by');
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function driveFiles()
    {
        return $this->hasMany(DriveFile::class, 'uploaded_by');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    public function shiftAssignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    public function employeeProfile()
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function onboardingTokens()
    {
        return $this->hasMany(OnboardingToken::class);
    }

    public function hrPolicyLogs()
    {
        return $this->hasMany(HrPolicyLog::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function performanceReviews()
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function payrollRecords()
    {
        return $this->hasMany(PayrollRecord::class);
    }
}
