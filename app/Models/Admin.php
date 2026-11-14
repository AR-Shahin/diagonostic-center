<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, HasRoles,Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'branch_id',
        'max_discount_percent',
        'designation',
        'qualification',
        'signature',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** Has the Super Admin role and full access is switched on (config/diagnostic.php → super_admin). */
    public function isSuperAdmin(): bool
    {
        return config('diagnostic.super_admin.full_access') && $this->hasRole(config('diagnostic.super_admin.role'), 'admin');
    }

    /** Every permission name this person may use. A Super Admin gets the full list, including permissions added later. */
    public function permissionNames(): array
    {
        if ($this->isSuperAdmin()) {
            return app(PermissionRegistrar::class)->getPermissions(['guard_name' => 'admin'])->pluck('name')->all();
        }

        return $this->getAllPermissions()->pluck('name')->all();
    }

    /** Highest discount % this person can give or approve without anyone else. */
    public function discountLimit(): float
    {
        return $this->isSuperAdmin() ? 100.0 : (float) $this->max_discount_percent;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new AdminResetPasswordNotification($token));
    }
}
