<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        // Persisted subscription metadata
        'plan',
        'billing_cycle',
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
        ];
    }

    /**
     * Relationships
     * A user belongs to a tenant (nullable in schema).
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /** Roles relationship */
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps()->withPivot('tenant_id');
    }

    /** Simple admin accessor based on ADMIN_EMAILS allowlist. */
    public function getIsAdminAttribute(): bool
    {
        $allowlist = collect(explode(',', (string) env('ADMIN_EMAILS', '')))
            ->map(fn ($e) => strtolower(trim($e)))
            ->filter();
        return $allowlist->contains(strtolower((string) $this->email));
    }

    /** Check role by slug, optionally within a tenant context. */
    public function hasRole(string $slug, ?int $tenantId = null): bool
    {
        $query = $this->roles()->where('slug', $slug);
        if ($tenantId !== null) {
            $query->wherePivot('tenant_id', $tenantId);
        }
        return $query->exists();
    }
}
