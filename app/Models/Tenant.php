<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes
     */
    protected $fillable = [
        'name',
        'domain',
        'plan',
        'settings',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Route key for model binding.
     * This tells Laravel to use the 'domain' column
     * instead of the default 'id' when resolving route parameters.
     */
    public function getRouteKeyName(): string
    {
        return 'domain';
    }

    /**
     * Relationships
     */

    // A tenant has many users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // A tenant has many leads
    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
